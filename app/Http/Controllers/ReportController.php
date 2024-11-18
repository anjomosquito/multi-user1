<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Reports/Index');
    }

    public function generateSalesReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $salesReport = Purchase::with(['user', 'products'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->map(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'user' => $purchase->user->name,
                    'total_amount' => $purchase->total_amount,
                    'payment_status' => $purchase->payment_status,
                    'created_at' => $purchase->created_at->format('Y-m-d H:i:s'),
                    'products' => $purchase->products->map(function ($product) {
                        return [
                            'name' => $product->name,
                            'quantity' => $product->pivot->quantity,
                            'price' => $product->pivot->price,
                        ];
                    }),
                ];
            });

        $summary = [
            'total_sales' => $salesReport->sum('total_amount'),
            'total_orders' => $salesReport->count(),
            'average_order_value' => $salesReport->avg('total_amount'),
        ];

        return response()->json([
            'sales_data' => $salesReport,
            'summary' => $summary,
        ]);
    }

    public function generatePaymentReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $paymentReport = Purchase::select(
            'payment_status',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(total_amount) as total_amount')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('payment_status')
            ->get();

        return response()->json([
            'payment_data' => $paymentReport,
        ]);
    }

    public function downloadSalesReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $format = $request->input('format', 'pdf');

        $salesReport = Purchase::with(['user', 'products'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->map(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'user' => $purchase->user->name,
                    'total_amount' => $purchase->total_amount,
                    'payment_status' => $purchase->payment_status,
                    'created_at' => $purchase->created_at->format('Y-m-d H:i:s'),
                    'products' => $purchase->products->map(function ($product) {
                        return [
                            'name' => $product->name,
                            'quantity' => $product->pivot->quantity,
                            'price' => $product->pivot->price,
                        ];
                    }),
                ];
            });

        $summary = [
            'total_sales' => $salesReport->sum('total_amount'),
            'total_orders' => $salesReport->count(),
            'average_order_value' => $salesReport->avg('total_amount'),
        ];

        if ($format === 'pdf') {
            $pdf = PDF::loadView('reports.sales', [
                'sales_data' => $salesReport,
                'summary' => $summary,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            return $pdf->download('sales-report.pdf');
        } else {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Add headers
            $sheet->setCellValue('A1', 'Order ID');
            $sheet->setCellValue('B1', 'Customer');
            $sheet->setCellValue('C1', 'Total Amount');
            $sheet->setCellValue('D1', 'Payment Status');
            $sheet->setCellValue('E1', 'Date');

            // Add data
            $row = 2;
            foreach ($salesReport as $sale) {
                $sheet->setCellValue('A' . $row, $sale['id']);
                $sheet->setCellValue('B' . $row, $sale['user']);
                $sheet->setCellValue('C' . $row, $sale['total_amount']);
                $sheet->setCellValue('D' . $row, $sale['payment_status']);
                $sheet->setCellValue('E' . $row, $sale['created_at']);
                $row++;
            }

            // Add summary
            $row += 2;
            $sheet->setCellValue('A' . $row, 'Summary');
            $row++;
            $sheet->setCellValue('A' . $row, 'Total Sales:');
            $sheet->setCellValue('B' . $row, $summary['total_sales']);
            $row++;
            $sheet->setCellValue('A' . $row, 'Total Orders:');
            $sheet->setCellValue('B' . $row, $summary['total_orders']);
            $row++;
            $sheet->setCellValue('A' . $row, 'Average Order Value:');
            $sheet->setCellValue('B' . $row, $summary['average_order_value']);

            // Auto-size columns
            foreach (range('A', 'E') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            
            $fileName = 'sales-report.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), $fileName);
            $writer->save($tempFile);

            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
        }
    }

    public function downloadPaymentReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $format = $request->input('format', 'pdf');

        $paymentReport = Purchase::select(
            'payment_status',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(total_amount) as total_amount')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('payment_status')
            ->get();

        if ($format === 'pdf') {
            $pdf = PDF::loadView('reports.payments', [
                'payment_data' => $paymentReport,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            return $pdf->download('payment-report.pdf');
        } else {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Add headers
            $sheet->setCellValue('A1', 'Payment Status');
            $sheet->setCellValue('B1', 'Count');
            $sheet->setCellValue('C1', 'Total Amount');

            // Add data
            $row = 2;
            foreach ($paymentReport as $payment) {
                $sheet->setCellValue('A' . $row, $payment->payment_status);
                $sheet->setCellValue('B' . $row, $payment->count);
                $sheet->setCellValue('C' . $row, $payment->total_amount);
                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'C') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            
            $fileName = 'payment-report.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), $fileName);
            $writer->save($tempFile);

            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
        }
    }

    public function printSalesReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $purchaseId = $request->input('purchase_id');

        $query = Purchase::with(['user', 'medicine']);
        
        if ($purchaseId) {
            $query->where('id', $purchaseId);
        } else {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $salesData = $query->get()->map(function ($purchase) {
            return [
                'id' => $purchase->id,
                'transaction_number' => $purchase->id, // Using ID as transaction number if not available
                'user' => $purchase->user->name,
                'total_amount' => $purchase->quantity * $purchase->mprice,
                'payment_status' => $purchase->payment_status,
                'created_at' => $purchase->created_at->format('Y-m-d H:i:s'),
                'medicine' => [
                    'name' => $purchase->medicine->name,
                    'quantity' => $purchase->quantity,
                    'price' => $purchase->mprice,
                    'subtotal' => $purchase->quantity * $purchase->mprice
                ]
            ];
        });

        $summary = [
            'total_sales' => $salesData->sum('total_amount'),
            'total_orders' => $salesData->count(),
            'average_order_value' => $salesData->count() > 0 ? $salesData->sum('total_amount') / $salesData->count() : 0,
        ];

        $pdf = PDF::loadView('reports.sales', [
            'sales_data' => $salesData,
            'summary' => $summary,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'single_purchase' => !empty($purchaseId)
        ]);

        return $pdf->stream('sales-report.pdf');
    }

    public function viewReceipt(Request $request)
    {
        $purchaseId = $request->input('purchase_id');
        
        $purchase = Purchase::with(['user', 'medicine'])
            ->findOrFail($purchaseId);

        $receiptData = [
            'id' => $purchase->id,
            'user' => $purchase->user->name,
            'total_amount' => $purchase->quantity * $purchase->mprice,
            'payment_status' => $purchase->payment_status,
            'created_at' => $purchase->created_at->format('Y-m-d H:i:s'),
            'medicine' => [
                'name' => $purchase->medicine->name,
                'quantity' => $purchase->quantity,
                'price' => $purchase->mprice,
                'subtotal' => $purchase->quantity * $purchase->mprice
            ]
        ];

        $pdf = PDF::loadView('receipts.purchase', [
            'receipt' => $receiptData,
            'purchase_date' => $purchase->created_at->format('Y-m-d'),
            'receipt_no' => sprintf('RCP-%06d', $purchase->id)
        ]);

        return $pdf->stream('receipt.pdf');
    }
}
