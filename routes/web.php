<?php

use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\AdminAuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\MedicineController;
use App\Http\Controllers\Admin\AdminInventoryController;
use App\Http\Controllers\Admin\AdminPurchaseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MedicineCategoryController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/sales', [ReportController::class, 'generateSalesReport'])->name('reports.sales');
    Route::post('/reports/sales/download', [ReportController::class, 'downloadSalesReport'])->name('reports.sales.download');
    Route::post('/reports/payment', [ReportController::class, 'generatePaymentReport'])->name('reports.payment');
    Route::post('/reports/payment/download', [ReportController::class, 'downloadPaymentReport'])->name('reports.payment.download');
    Route::get('/purchase/receipt', [ReportController::class, 'viewReceipt'])->name('purchase.receipt');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth:admin'])->name('dashboard');

    Route::middleware('auth:admin')->group(function () {
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [AdminProfileController::class, 'destroy'])->name('profile.destroy');
        
        // Admin Reports Routes
        Route::get('/reports', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/sales', [App\Http\Controllers\Admin\ReportController::class, 'generateSalesReport'])->name('reports.sales');
        Route::post('/reports/payments', [App\Http\Controllers\Admin\ReportController::class, 'generatePaymentReport'])->name('reports.payments');
        Route::post('/reports/sales/download', [App\Http\Controllers\Admin\ReportController::class, 'downloadSalesReport'])->name('reports.sales.download');
        Route::post('/reports/payments/download', [App\Http\Controllers\Admin\ReportController::class, 'downloadPaymentReport'])->name('reports.payments.download');
        Route::get('/reports/sales/print', [App\Http\Controllers\Admin\ReportController::class, 'printSalesReport'])->name('reports.sales.print');
        Route::get('/reports/payments/print', [App\Http\Controllers\Admin\ReportController::class, 'printPaymentReport'])->name('reports.payments.print');
        
        // Medicine Routes
        Route::resource('medicines', MedicineController::class);
        
        // Category Routes
        Route::resource('categories', MedicineCategoryController::class)->except(['show']);
        
        // Inventory Routes
        Route::get('/inventory', [AdminInventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory', [AdminInventoryController::class, 'store'])->name('inventory.store');
        Route::put('/inventory/{medicine}', [AdminInventoryController::class, 'update'])->name('inventory.update');
        Route::delete('/inventory/{medicine}', [AdminInventoryController::class, 'destroy'])->name('inventory.destroy');
        Route::put('/inventory/{medicine}/toggle-status', [AdminInventoryController::class, 'toggleStatus'])->name('inventory.toggle-status');
        Route::get('/inventory/report', [AdminInventoryController::class, 'getInventoryReport'])->name('inventory.report');
        Route::post('/inventory/report/download', [AdminInventoryController::class, 'downloadReport'])->name('inventory.report.download');
        
        // Inventory Report Routes
        Route::get('/inventory/report', [AdminInventoryController::class, 'getInventoryReport'])->name('inventory.report');
        Route::post('/inventory/report/download', [AdminInventoryController::class, 'downloadReport'])->name('inventory.report.download');
        
        // Users Routes
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        
        // Purchase Routes
        Route::get('/purchase/index', [AdminPurchaseController::class, 'index'])->name('purchase.index');
        Route::get('/purchase/{id}', [AdminPurchaseController::class, 'show'])->name('purchase.show');
        Route::post('/purchase/{id}/mark-ready', [AdminPurchaseController::class, 'markAsReady'])->name('purchase.mark-ready');
        Route::post('/purchase/{id}/confirm', [AdminPurchaseController::class, 'confirm'])->name('purchase.confirm');
        Route::post('/purchase/{id}/ready', [AdminPurchaseController::class, 'markAsReady'])->name('purchase.ready');
        Route::post('/purchase/{id}/complete', [AdminPurchaseController::class, 'markAsCompleted'])->name('purchase.complete');
        Route::post('/purchase/{id}/verify-pickup', [AdminPurchaseController::class, 'markAsPickedUp'])->name('purchase.verify-pickup');
        Route::post('/purchase/{id}/verify-payment', [AdminPurchaseController::class, 'verifyPayment'])->name('purchase.verify-payment');
        Route::get('/purchase/{purchase}/report', [AdminPurchaseController::class, 'generatePurchaseReport'])->name('purchase.report');
    });
});

Route::post('/admin/logout', [AdminAuthenticatedSessionController::class, 'destroy'])->name('admin.logout');

//Medicine
/*Route::get('/admin.medicines', function () {
    return Inertia::render('Admin/Medicines/Index');
})->name('admin.medicines');*/
//Route::get('/admin/medicines/index', [MedicineController::class, 'index'])->name('admin.medicines.index');
//Route::post('/admin/medicines/', [MedicineController::class, 'store'])->name('admin.medicines.store');
//Route::get('/admin/medicines/create', [MedicineController::class, 'create'])->name('admin.medicines.create');    
//Route::put('/admin/medicines/{medicine}', [MedicineController::class, 'update'])->name('admin.medicines.update');
//Route::get('/admin/medicines/{medicine}/edit', [MedicineController::class, 'edit'])->name('admin.medicines.edit');
//Route::delete('/admin/medicines/{medicine}', [MedicineController::class, 'destroy'])->name('admin.medicines.destroy');
    
//Inventory
//Route::get('/admin/admininventory/index', [AdminInventoryController::class, 'index'])->name('admin.admininventory.index');
Route::get('/dashboard', [InventoryController::class, 'dashboard'])->name('dashboard');
//Message 

//UsersProfiles
//Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');


Route::get('/medicines', [InventoryController::class, 'index'])->name('medicines.index');
Route::post('/medicines/', [InventoryController::class, 'store'])->name('medicines.store');


//Cart
Route::middleware(['auth'])->group(function () {
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::delete('/cart/{id}', [CartController::class, 'destroy'])->name('cart.destroy');
});
    
//Purcahse
Route::get('/purchase/index', [PurchaseController::class, 'index'])->name('purchase.index');
Route::post('/purchase/order', [PurchaseController::class, 'store'])->name('purchase.store');
Route::delete('/purchase/cancel/{id}', [PurchaseController::class, 'cancel'])->name('purchase.cancel');

//Purchase ADMIN
Route::get('admin/purchase/index', [AdminPurchaseController::class, 'index'])->name('admin.purchase.index');
Route::get('/admin/purchase/report', [AdminPurchaseController::class, 'generateReport'])->name('admin.purchase.report');
Route::get('/admin/purchase/{id}', [AdminPurchaseController::class, 'show'])->name('admin.purchase.show');

//ADMIN DASHBOARD
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/dashboard/search', [DashboardController::class, 'search'])->name('admin.dashboard.search');
});

// User routes
Route::middleware(['auth'])->group(function () {
    Route::post('/purchase/{id}/verify-pickup', [PurchaseController::class, 'verifyPickup'])
        ->name('purchase.verify-pickup');
    Route::post('/purchase/{id}/upload-payment', [PurchaseController::class, 'uploadPaymentProof'])
        ->name('purchase.upload-payment');
    Route::get('/purchase/{purchase}/report', [PurchaseController::class, 'generatePurchaseReport'])
        ->name('purchase.report');
});

// User chat routes
Route::middleware(['auth'])->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat', [ChatController::class, 'store'])->name('chat.store');
    Route::delete('/chat/{id}', [ChatController::class, 'destroy'])->name('chat.destroy');
    Route::get('/chat/unread-count', [ChatController::class, 'getUnreadCount'])->name('chat.unread-count');
});

// Admin chat routes
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin/chat', [App\Http\Controllers\Admin\ChatController::class, 'index'])->name('admin.chat.index');
    Route::get('/admin/chat/{user}', [App\Http\Controllers\Admin\ChatController::class, 'show'])->name('admin.chat.show');
    Route::delete('/admin/chat/{user}/delete/{chatId}', [App\Http\Controllers\Admin\ChatController::class, 'destroy'])
    ->name('admin.chat.delete');
    Route::post('/admin/chat/{user}', [App\Http\Controllers\Admin\ChatController::class, 'store'])->name('admin.chat.store');
});

// Add this temporary route to test email
Route::get('/test-mail/{user_id}', function ($user_id) {
    try {
        $user = \App\Models\User::find($user_id);
        if (!$user) {
            return 'User not found';
        }

        // Create a test purchase
        $purchase = \App\Models\Purchase::where('user_id', $user_id)->first();
        if (!$purchase) {
            return 'No purchase found for this user';
        }

        // Send notification
        $purchase->sendNotification('confirmed');
        
        return 'Email sent successfully to ' . $user->email;
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

require __DIR__.'/auth.php';
