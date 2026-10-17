
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashInflowController;
use App\Http\Controllers\CashOutflowController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OwnerDashboardController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReceivableController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockOpnameController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Autentikasi
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])
            ->name('dashboard');

        // Pembelian Petani
        Route::get('/purchases', [PurchaseController::class, 'index'])
            ->name('purchases.index');

        Route::post('/purchases', [PurchaseController::class, 'store'])
            ->name('purchases.store');

        Route::get('/purchases/{purchase}/json', [PurchaseController::class, 'showJson'])
            ->name('purchases.json');

        Route::put('/purchases/{purchase}', [PurchaseController::class, 'update'])
            ->name('purchases.update');

        Route::get('/purchases/{purchase}/print', [PurchaseController::class, 'print'])
            ->name('purchases.print');

        // Pengiriman PKS
        Route::get('/shipments', [ShipmentController::class, 'index'])
            ->name('shipments.index');

        Route::post('/shipments', [ShipmentController::class, 'store'])
            ->name('shipments.store');

        Route::put('/shipments/{shipment}', [ShipmentController::class, 'update'])
            ->name('shipments.update');

        Route::get('/shipments/{shipment}/print', [ShipmentController::class, 'print'])
            ->name('shipments.print');

        // Informasi stok
        Route::get('/stocks', [StockController::class, 'index'])
            ->name('stocks.index');

        // Stok opname
        Route::get('/stock-opnames', [StockOpnameController::class, 'index'])
            ->name('stock-opnames.index');

        Route::post('/stock-opnames', [StockOpnameController::class, 'store'])
            ->name('stock-opnames.store');

        Route::put('/stock-opnames/{stockOpname}', [StockOpnameController::class, 'update'])
            ->name('stock-opnames.update');

        // Piutang pelanggan / petani
        Route::get('/receivables', [ReceivableController::class, 'index'])
            ->name('receivables.index');

        Route::post('/receivables', [ReceivableController::class, 'store'])
            ->name('receivables.store');

        Route::get('/receivables/{receivable}/json', [ReceivableController::class, 'showJson'])
            ->name('receivables.json');

        Route::post('/receivables/{receivable}/loans', [ReceivableController::class, 'addLoan'])
            ->name('receivables.loans.store');

        Route::post('/receivables/{receivable}/payments', [ReceivableController::class, 'pay'])
            ->name('receivables.payments.store');

        // Kas masuk
        Route::get('/cash-inflows', [CashInflowController::class, 'index'])
            ->name('cash-inflows.index');

        Route::post('/cash-inflows', [CashInflowController::class, 'store'])
            ->name('cash-inflows.store');

        Route::put('/cash-inflows/{cashInflow}', [CashInflowController::class, 'update'])
            ->name('cash-inflows.update');

        // Kas keluar
        Route::get('/cash-outflows', [CashOutflowController::class, 'index'])
            ->name('cash-outflows.index');

        Route::post('/cash-outflows', [CashOutflowController::class, 'store'])
            ->name('cash-outflows.store');

        Route::put('/cash-outflows/{cashOutflow}', [CashOutflowController::class, 'update'])
            ->name('cash-outflows.update');

        // Laporan
        Route::get('/reports', [ReportController::class, 'index'])
            ->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])
            ->name('reports.export');

    });

// Owner
Route::middleware(['auth', 'role:owner'])
    ->prefix('owner')
    ->name('owner.')
    ->group(function () {

        Route::get('/dashboard', [OwnerDashboardController::class, 'index'])
            ->name('dashboard');
        Route::get('/cash', [OwnerDashboardController::class, 'cash'])
            ->name('cash');
        Route::get('/sales', [OwnerDashboardController::class, 'sales'])
            ->name('sales');
        Route::get('/expenses', [OwnerDashboardController::class, 'expenses'])
            ->name('expenses');
        Route::get('/reports', [OwnerDashboardController::class, 'reports'])
            ->name('reports');
        Route::get('/reports/export/{format}', [OwnerDashboardController::class, 'exportReport'])
            ->whereIn('format', ['pdf', 'csv'])
            ->name('reports.export');
        Route::post('/accounts', [OwnerDashboardController::class, 'storeAccount'])
            ->name('accounts.store');
        Route::post('/cash/balance', [OwnerDashboardController::class, 'addCashBalance'])
            ->name('cash.balance.store');
        Route::post('/transfers', [OwnerDashboardController::class, 'storeTransfer'])
            ->name('transfers.store');
        Route::post('/expenses', [OwnerDashboardController::class, 'storeExpense'])
            ->name('expenses.store');
        Route::post('/sales', [ShipmentController::class, 'ownerStore'])
            ->name('sales.store');
        Route::post('/sales/{shipment}/payment', [OwnerDashboardController::class, 'recordSalePayment'])
            ->name('sales.payments.store');
    });
