<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\User;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as PDFFacade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Reports/Index');
    }

    private function getExecutiveSummary($startDate, $endDate)
    {
        // Get current period data
        $currentPeriodData = Purchase::where('payment_status', 'verified')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(quantity * mprice) as total_revenue'),
                DB::raw('COUNT(DISTINCT user_id) as total_customers'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(quantity) as total_units')
            )
            ->first();

        // Calculate previous period dates
        $periodLength = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $previousEndDate = Carbon::parse($startDate)->subDay();
        $previousStartDate = $previousEndDate->copy()->subDays($periodLength - 1);
        
        $previousPeriodData = Purchase::where('payment_status', 'verified')
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->select(
                DB::raw('SUM(quantity * mprice) as total_revenue'),
                DB::raw('COUNT(DISTINCT user_id) as total_customers'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(quantity) as total_units')
            )
            ->first();

        // Calculate growth rates
        $revenueGrowth = $previousPeriodData->total_revenue > 0 
            ? (($currentPeriodData->total_revenue - $previousPeriodData->total_revenue) / $previousPeriodData->total_revenue) * 100 
            : 0;
        
        $customerGrowth = $previousPeriodData->total_customers > 0 
            ? (($currentPeriodData->total_customers - $previousPeriodData->total_customers) / $previousPeriodData->total_customers) * 100 
            : 0;

        return [
            'current_period' => [
                'total_revenue' => number_format($currentPeriodData->total_revenue, 2, '.', ''),
                'total_customers' => $currentPeriodData->total_customers,
                'total_orders' => $currentPeriodData->total_orders,
                'total_units' => $currentPeriodData->total_units,
                'average_order_value' => $currentPeriodData->total_orders > 0 
                    ? number_format($currentPeriodData->total_revenue / $currentPeriodData->total_orders, 2, '.', '')
                    : '0.00'
            ],
            'growth_rates' => [
                'revenue_growth' => number_format($revenueGrowth, 1),
                'customer_growth' => number_format($customerGrowth, 1)
            ]
        ];
    }

    private function getProductAnalysis($startDate, $endDate)
    {
        // Calculate previous period dates
        $periodLength = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $previousEndDate = Carbon::parse($startDate)->subDay();
        $previousStartDate = $previousEndDate->copy()->subDays($periodLength - 1);

        $products = DB::table('purchases')
            ->join('medicines', 'medicines.id', '=', 'purchases.medicine_id')
            ->where('purchases.payment_status', 'verified')
            ->whereBetween('purchases.created_at', [$startDate, $endDate])
            ->select(
                'medicines.name',
                'medicines.id',
                DB::raw('SUM(purchases.quantity) as total_quantity'),
                DB::raw('SUM(purchases.quantity * purchases.mprice) as total_revenue'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('AVG(purchases.quantity * purchases.mprice) as avg_order_value')
            )
            ->groupBy('medicines.id', 'medicines.name')
            ->orderByDesc('total_revenue')
            ->get();

        return $products->map(function ($product) use ($previousStartDate, $previousEndDate) {
            // Get previous period data for this product
            $previousData = DB::table('purchases')
                ->where('medicine_id', $product->id)
                ->where('payment_status', 'verified')
                ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
                ->select(
                    DB::raw('COALESCE(SUM(quantity), 0) as total_quantity'),
                    DB::raw('COALESCE(SUM(quantity * mprice), 0) as total_revenue')
                )
                ->first();

            // Calculate growth rate
            $currentRevenue = floatval($product->total_revenue);
            $previousRevenue = floatval($previousData->total_revenue);
            
            $growth = $previousRevenue > 0 
                ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 
                : 0;

            return [
                'name' => $product->name,
                'total_quantity' => $product->total_quantity,
                'total_revenue' => number_format($currentRevenue, 2, '.', ''),
                'order_count' => $product->order_count,
                'avg_order_value' => number_format($product->avg_order_value, 2, '.', ''),
                'growth_rate' => number_format($growth, 1),
            ];
        });
    }

    private function getCustomerAnalysis($startDate, $endDate)
    {
        try {
            // Get all customers who made purchases in the period
            $customers = DB::table('purchases')
                ->join('users', 'users.id', '=', 'purchases.user_id')
                ->where('purchases.payment_status', 'verified')
                ->whereBetween('purchases.created_at', [$startDate, $endDate])
                ->select(
                    'users.id',
                    'users.name',
                    DB::raw('COUNT(*) as order_count'),
                    DB::raw('SUM(purchases.quantity * purchases.mprice) as total_spent'),
                    DB::raw('MIN(purchases.created_at) as first_purchase'),
                    DB::raw('MAX(purchases.created_at) as last_purchase')
                )
                ->groupBy('users.id', 'users.name')
                ->get();

            // Categorize customers
            $newCustomers = $customers->filter(function ($customer) use ($startDate, $endDate) {
                $firstPurchase = Carbon::parse($customer->first_purchase);
                return $firstPurchase->between($startDate, $endDate);
            });

            $returningCustomers = $customers->filter(function ($customer) use ($startDate) {
                $firstPurchase = Carbon::parse($customer->first_purchase);
                return $firstPurchase->lt($startDate);
            });

            return [
                'new_customers' => [
                    'count' => $newCustomers->count(),
                    'total_revenue' => number_format($newCustomers->sum('total_spent'), 2, '.', ''),
                    'average_order_value' => $newCustomers->count() > 0 
                        ? number_format($newCustomers->sum('total_spent') / $newCustomers->count(), 2, '.', '')
                        : '0.00'
                ],
                'returning_customers' => [
                    'count' => $returningCustomers->count(),
                    'total_revenue' => number_format($returningCustomers->sum('total_spent'), 2, '.', ''),
                    'average_order_value' => $returningCustomers->count() > 0 
                        ? number_format($returningCustomers->sum('total_spent') / $returningCustomers->count(), 2, '.', '')
                        : '0.00'
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error in getCustomerAnalysis: ' . $e->getMessage());
            throw $e;
        }
    }

    public function generateSalesReport(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date'
            ]);

            // Parse dates with explicit format
            $startDate = Carbon::parse($validated['start_date'])->startOfDay();
            $endDate = Carbon::parse($validated['end_date'])->endOfDay();

            // Get comprehensive report data
            $executiveSummary = $this->getExecutiveSummary($startDate, $endDate);
            $productAnalysis = $this->getProductAnalysis($startDate, $endDate);
            $customerAnalysis = $this->getCustomerAnalysis($startDate, $endDate);

            // Get daily trends with proper date formatting
            $dailyTrends = Purchase::where('payment_status', 'verified')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(quantity * mprice) as total_sales'),
                    DB::raw('SUM(quantity) as total_quantity'),
                    DB::raw('COUNT(DISTINCT user_id) as unique_customers')
                )
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date', 'asc')
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => Carbon::parse($item->date)->format('M d, Y'),
                        'total_sales' => number_format($item->total_sales, 2, '.', ''),
                        'total_orders' => $item->total_orders,
                        'total_quantity' => $item->total_quantity,
                        'unique_customers' => $item->unique_customers
                    ];
                });

            // Get payment status breakdown
            $paymentStatus = Purchase::whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    'payment_status',
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(quantity * mprice) as total_amount')
                )
                ->groupBy('payment_status')
                ->get()
                ->map(function ($item) {
                    return [
                        'status' => $item->payment_status,
                        'count' => $item->count,
                        'amount' => number_format($item->total_amount, 2, '.', '')
                    ];
                });

            // Get sales summary
            $salesSummary = Purchase::where('payment_status', 'verified')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('SUM(quantity * mprice) as total_sales'),
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('COUNT(DISTINCT medicine_id) as total_products')
                )
                ->first();

            $summary = [
                'total_sales' => number_format($salesSummary->total_sales ?? 0, 2, '.', ''),
                'total_orders' => $salesSummary->total_orders ?? 0,
                'average_order_value' => $salesSummary->total_orders > 0 
                    ? number_format(($salesSummary->total_sales / $salesSummary->total_orders), 2, '.', '')
                    : '0.00',
                'total_products' => $salesSummary->total_products ?? 0
            ];

            return response()->json([
                'report_data' => [
                    'executive_summary' => $executiveSummary,
                    'product_analysis' => $productAnalysis,
                    'customer_analysis' => $customerAnalysis,
                    'daily_trends' => $dailyTrends,
                    'payment_status' => $paymentStatus,
                    'sales_summary' => $summary
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating sales report: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function generatePaymentReport(Request $request)
    {
        try {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

            $paymentData = Purchase::whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    'payment_status',
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(quantity * mprice) as total_amount')
                )
                ->groupBy('payment_status')
                ->get()
                ->map(function ($item) {
                    return [
                        'payment_status' => $item->payment_status,
                        'count' => $item->count,
                        'total_amount' => number_format($item->total_amount, 2, '.', '')
                    ];
                });

            return response()->json([
                'payment_data' => $paymentData
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating payment report: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function downloadSalesReport(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'format' => 'required|in:pdf,xlsx'
            ]);

            $startDate = Carbon::parse($validated['start_date'])->startOfDay();
            $endDate = Carbon::parse($validated['end_date'])->endOfDay();
            $format = $validated['format'];

            // Get sales data
            $salesData = Purchase::with(['user', 'medicine'])
                ->where('payment_status', 'verified')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get()
                ->map(function ($purchase) {
                    return [
                        'id' => $purchase->id,
                        'user' => $purchase->user->name ?? 'N/A',
                        'medicine_name' => $purchase->medicine->name ?? 'N/A',
                        'quantity' => $purchase->quantity,
                        'price' => $purchase->mprice,
                        'total_amount' => $purchase->quantity * $purchase->mprice,
                        'payment_status' => $purchase->payment_status,
                        'created_at' => $purchase->created_at->format('Y-m-d H:i:s'),
                    ];
                });

            // Calculate summary
            $summary = [
                'total_sales' => $salesData->sum('total_amount'),
                'total_orders' => $salesData->count(),
                'average_order_value' => $salesData->count() > 0 
                    ? $salesData->sum('total_amount') / $salesData->count()
                    : 0,
                'total_items' => $salesData->sum('quantity')
            ];

            if ($format === 'pdf') {
                $pdf = PDFFacade::loadView('reports.sales', [
                    'sales_data' => $salesData,
                    'summary' => $summary,
                    'start_date' => $startDate->format('M d, Y'),
                    'end_date' => $endDate->format('M d, Y')
                ]);

                return $pdf->download('sales_report_' . now()->format('Y-m-d') . '.pdf');
            } else {
                // Create new spreadsheet
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                // Set title
                $sheet->setCellValue('A1', 'Sales Report');
                $sheet->setCellValue('A2', 'Period: ' . $startDate->format('M d, Y') . ' to ' . $endDate->format('M d, Y'));
                $sheet->mergeCells('A1:H1');
                $sheet->mergeCells('A2:H2');

                // Summary section
                $sheet->setCellValue('A4', 'Summary Statistics');
                $sheet->mergeCells('A4:H4');
                $sheet->setCellValue('A5', 'Total Sales:');
                $sheet->setCellValue('B5', '₱' . number_format($summary['total_sales'], 2));
                $sheet->setCellValue('C5', 'Total Orders:');
                $sheet->setCellValue('D5', $summary['total_orders']);
                $sheet->setCellValue('E5', 'Average Order Value:');
                $sheet->setCellValue('F5', '₱' . number_format($summary['average_order_value'], 2));
                $sheet->setCellValue('G5', 'Total Items:');
                $sheet->setCellValue('H5', $summary['total_items']);

                // Headers
                $headers = ['ID', 'Customer', 'Medicine', 'Quantity', 'Unit Price', 'Total Amount', 'Status', 'Date'];
                foreach ($headers as $col => $header) {
                    $sheet->setCellValueByColumnAndRow($col + 1, 8, $header);
                }

                // Data
                $row = 9;
                foreach ($salesData as $sale) {
                    $sheet->setCellValueByColumnAndRow(1, $row, $sale['id']);
                    $sheet->setCellValueByColumnAndRow(2, $row, $sale['user']);
                    $sheet->setCellValueByColumnAndRow(3, $row, $sale['medicine_name']);
                    $sheet->setCellValueByColumnAndRow(4, $row, $sale['quantity']);
                    $sheet->setCellValueByColumnAndRow(5, $row, '₱' . number_format($sale['price'], 2));
                    $sheet->setCellValueByColumnAndRow(6, $row, '₱' . number_format($sale['total_amount'], 2));
                    $sheet->setCellValueByColumnAndRow(7, $row, $sale['payment_status']);
                    $sheet->setCellValueByColumnAndRow(8, $row, $sale['created_at']);
                    $row++;
                }

                // Auto-size columns
                foreach (range('A', 'H') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Styling
                $styleArray = [
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ];
                $sheet->getStyle('A1:H2')->applyFromArray($styleArray);
                $sheet->getStyle('A4')->applyFromArray($styleArray);
                $sheet->getStyle('A8:H8')->applyFromArray($styleArray);

                // Save to temporary file
                $writer = new Xlsx($spreadsheet);
                $fileName = 'sales_report_' . now()->format('Y-m-d') . '.xlsx';
                $tempPath = storage_path('app/temp');
                
                if (!file_exists($tempPath)) {
                    mkdir($tempPath, 0755, true);
                }
                
                $filePath = $tempPath . '/' . $fileName;
                $writer->save($filePath);

                return response()->download($filePath, $fileName, [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
                ])->deleteFileAfterSend(true);
            }
        } catch (\Exception $e) {
            Log::error('Error generating sales report: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Return error response in JSON format
            return response()->json([
                'error' => 'Error generating report: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadPaymentReport(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'format' => 'required|in:pdf,xlsx'
            ]);

            $startDate = Carbon::parse($validated['start_date'])->startOfDay();
            $endDate = Carbon::parse($validated['end_date'])->endOfDay();
            $format = $validated['format'];

            // Get payment data
            $paymentData = Purchase::whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    'payment_status',
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(quantity * mprice) as total_amount'),
                    DB::raw('AVG(quantity * mprice) as average_amount')
                )
                ->groupBy('payment_status')
                ->get()
                ->map(function ($item) {
                    return [
                        'status' => $item->payment_status,
                        'count' => $item->count,
                        'total_amount' => $item->total_amount,
                        'average_amount' => $item->average_amount,
                    ];
                });

            // Get daily trends
            $paymentTrends = Purchase::whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    'payment_status',
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(quantity * mprice) as total_amount')
                )
                ->groupBy(DB::raw('DATE(created_at)'), 'payment_status')
                ->orderBy('date')
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => Carbon::parse($item->date)->format('M d, Y'),
                        'status' => $item->payment_status,
                        'count' => $item->count,
                        'total_amount' => $item->total_amount
                    ];
                });

            // Calculate summary
            $summary = [
                'total_transactions' => $paymentData->sum('count'),
                'total_amount' => $paymentData->sum('total_amount'),
                'average_transaction' => $paymentData->sum('count') > 0 
                    ? $paymentData->sum('total_amount') / $paymentData->sum('count')
                    : 0
            ];

            if ($format === 'pdf') {
                $pdf = PDFFacade::loadView('reports.payments', [
                    'payment_data' => $paymentData,
                    'payment_trends' => $paymentTrends,
                    'summary' => $summary,
                    'start_date' => $startDate->format('M d, Y'),
                    'end_date' => $endDate->format('M d, Y')
                ]);

                return $pdf->download('payment_report_' . now()->format('Y-m-d') . '.pdf');
            } else {
                // Create new spreadsheet
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                // Set title
                $sheet->setCellValue('A1', 'Payment Report');
                $sheet->setCellValue('A2', 'Period: ' . $startDate->format('M d, Y') . ' to ' . $endDate->format('M d, Y'));
                $sheet->mergeCells('A1:D1');
                $sheet->mergeCells('A2:D2');

                // Summary section
                $sheet->setCellValue('A4', 'Summary Statistics');
                $sheet->mergeCells('A4:D4');
                $sheet->setCellValue('A5', 'Total Transactions:');
                $sheet->setCellValue('B5', number_format($summary['total_transactions']));
                $sheet->setCellValue('C5', 'Total Amount:');
                $sheet->setCellValue('D5', number_format($summary['total_amount'], 2));
                $sheet->setCellValue('A6', 'Average Transaction:');
                $sheet->setCellValue('B6', number_format($summary['average_transaction'], 2));

                // Payment Status Breakdown
                $sheet->setCellValue('A8', 'Payment Status Breakdown');
                $sheet->mergeCells('A8:D8');
                
                $headers = ['Status', 'Count', 'Total Amount', 'Average Amount'];
                foreach ($headers as $col => $header) {
                    $sheet->setCellValueByColumnAndRow($col + 1, 9, $header);
                }

                $row = 10;
                foreach ($paymentData as $data) {
                    $sheet->setCellValueByColumnAndRow(1, $row, $data['status']);
                    $sheet->setCellValueByColumnAndRow(2, $row, number_format($data['count']));
                    $sheet->setCellValueByColumnAndRow(3, $row, number_format($data['total_amount'], 2));
                    $sheet->setCellValueByColumnAndRow(4, $row, number_format($data['average_amount'], 2));
                    $row++;
                }

                // Daily Payment Trends
                $row += 2;
                $sheet->setCellValue('A' . $row, 'Daily Payment Trends');
                $sheet->mergeCells("A{$row}:D{$row}");
                $row++;

                $trendHeaders = ['Date', 'Status', 'Count', 'Total Amount'];
                foreach ($trendHeaders as $col => $header) {
                    $sheet->setCellValueByColumnAndRow($col + 1, $row, $header);
                }
                $row++;

                foreach ($paymentTrends as $trend) {
                    $sheet->setCellValueByColumnAndRow(1, $row, $trend['date']);
                    $sheet->setCellValueByColumnAndRow(2, $row, $trend['status']);
                    $sheet->setCellValueByColumnAndRow(3, $row, number_format($trend['count']));
                    $sheet->setCellValueByColumnAndRow(4, $row, number_format($trend['total_amount'], 2));
                    $row++;
                }

                // Auto-size columns
                foreach (range('A', 'D') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Styling
                $styleArray = [
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ];
                $sheet->getStyle('A1:D2')->applyFromArray($styleArray);
                $sheet->getStyle('A4')->applyFromArray($styleArray);
                $sheet->getStyle('A8')->applyFromArray($styleArray);
                $sheet->getStyle('A9:D9')->applyFromArray($styleArray);

                // Save to temporary file
                $writer = new Xlsx($spreadsheet);
                $fileName = 'payment_report_' . now()->format('Y-m-d') . '.xlsx';
                $tempPath = storage_path('app/temp');
                
                if (!file_exists($tempPath)) {
                    mkdir($tempPath, 0755, true);
                }
                
                $filePath = $tempPath . '/' . $fileName;
                $writer->save($filePath);

                return response()->download($filePath, $fileName, [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ])->deleteFileAfterSend(true);
            }
        } catch (\Exception $e) {
            Log::error('Error generating payment report: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Error generating report: ' . $e->getMessage()], 500);
        }
    }

    public function printSalesReport(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date'
            ]);

            $startDate = Carbon::parse($validated['start_date'])->startOfDay();
            $endDate = Carbon::parse($validated['end_date'])->endOfDay();

            // Get sales data
            $salesData = Purchase::with(['user', 'medicine'])
                ->where('payment_status', 'verified')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get()
                ->map(function ($purchase) {
                    return [
                        'id' => $purchase->id,
                        'user' => $purchase->user->name ?? 'N/A',
                        'medicine_name' => $purchase->medicine->name ?? 'N/A',
                        'quantity' => $purchase->quantity,
                        'price' => $purchase->mprice,
                        'total_amount' => $purchase->quantity * $purchase->mprice,
                        'payment_status' => $purchase->payment_status,
                        'created_at' => $purchase->created_at->format('Y-m-d H:i:s'),
                    ];
                });

            // Calculate summary
            $summary = [
                'total_sales' => $salesData->sum('total_amount'),
                'total_orders' => $salesData->count(),
                'average_order_value' => $salesData->count() > 0 
                    ? $salesData->sum('total_amount') / $salesData->count()
                    : 0,
                'total_items' => $salesData->sum('quantity')
            ];

            // Return the print view
            return view('reports.sales-print', [
                'sales_data' => $salesData,
                'summary' => $summary,
                'start_date' => $startDate->format('M d, Y'),
                'end_date' => $endDate->format('M d, Y')
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating sales print report: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Error generating report: ' . $e->getMessage()], 500);
        }
    }

    public function printPaymentReport(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date'
            ]);

            $startDate = Carbon::parse($validated['start_date'])->startOfDay();
            $endDate = Carbon::parse($validated['end_date'])->endOfDay();

            // Get payment data
            $paymentData = Purchase::whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    'payment_status',
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(quantity * mprice) as total_amount'),
                    DB::raw('AVG(quantity * mprice) as average_amount')
                )
                ->groupBy('payment_status')
                ->get()
                ->map(function ($item) {
                    return [
                        'status' => $item->payment_status,
                        'count' => $item->count,
                        'total_amount' => $item->total_amount,
                        'average_amount' => $item->average_amount,
                    ];
                });

            // Get daily trends
            $paymentTrends = Purchase::whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    'payment_status',
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(quantity * mprice) as total_amount')
                )
                ->groupBy(DB::raw('DATE(created_at)'), 'payment_status')
                ->orderBy('date')
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => Carbon::parse($item->date)->format('M d, Y'),
                        'status' => $item->payment_status,
                        'count' => $item->count,
                        'total_amount' => $item->total_amount
                    ];
                });

            // Calculate summary
            $summary = [
                'total_transactions' => $paymentData->sum('count'),
                'total_amount' => $paymentData->sum('total_amount'),
                'average_transaction' => $paymentData->sum('count') > 0 
                    ? $paymentData->sum('total_amount') / $paymentData->sum('count')
                    : 0
            ];

            // Return the print view
            return view('reports.payments-print', [
                'payment_data' => $paymentData,
                'payment_trends' => $paymentTrends,
                'summary' => $summary,
                'start_date' => $startDate->format('M d, Y'),
                'end_date' => $endDate->format('M d, Y')
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating payment print report: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Error generating report: ' . $e->getMessage()], 500);
        }
    }
}
