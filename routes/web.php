<?php

use Illuminate\Support\Facades\Route;

use App\Http\Middleware\ValidateCompanySlug;
use App\Http\Middleware\SuperAdminMiddleware;

use App\Http\Controllers\AjaxController;
use App\Http\Controllers\TestController;

use App\Http\Controllers\Company\PageController as CompanyPageController;
use App\Http\Controllers\Company\BranchController as CompanyBranchController;
use App\Http\Controllers\Company\ClusterController as CompanyClusterController;
use App\Http\Controllers\Company\DepartmentController as CompanyDepartmentController;
use App\Http\Controllers\Company\SupplierController as CompanySupplierController;
use App\Http\Controllers\Company\CategoryController as CompanyCategoryController;
use App\Http\Controllers\Company\SubcategoryController as CompanySubcategoryController;
use App\Http\Controllers\Company\UnitOfMeasurementController as CompanyUnitOfMeasurementController;
use App\Http\Controllers\Company\PaymentTypeController as CompanyPaymentTypeController;
use App\Http\Controllers\Company\BankController as CompanyBankController;
use App\Http\Controllers\Company\DiscountTypeController as CompanyDiscountTypeController;
use App\Http\Controllers\Company\ItemTypeController as CompanyItemTypeController;
use App\Http\Controllers\Company\ProductController as CompanyProductController;
use App\Http\Controllers\Company\ReportController as CompanyReportController;
use App\Http\Controllers\Company\UserController as CompanyUserController;
use App\Http\Controllers\Company\PaymentTermController as CompanyPaymentTermController;
use App\Http\Controllers\Company\SupplierTermController as CompanySupplierTermController;
use App\Http\Controllers\Company\PurchaseRequestController as CompanyPurchaseRequestController;
use App\Http\Controllers\Company\PurchaseOrderController as CompanyPurchaseOrderController;
use App\Http\Controllers\Company\PurchaseDeliveryController as CompanyPurchaseDeliveryController;
use App\Http\Controllers\Company\StockTransferRequestController as CompanyStockTransferRequestController;
use App\Http\Controllers\Company\ProductDisposalReasonController as CompanyProductDisposalReasonController;
use App\Http\Controllers\Company\ProductDisposalController as CompanyProductDisposalController;
use App\Http\Controllers\Company\ProductPhysicalCountController as CompanyProductPhysicalCountController;
use App\Http\Controllers\Company\RoleController as CompanyRoleController;
use App\Http\Controllers\Company\ItemLocationController as CompanyItemLocationController;
use App\Http\Controllers\Company\ChangePriceReasonController as CompanyChangePriceReasonController;

use App\Http\Controllers\Admin\CompanyController as AdminCompanyController;
use App\Http\Controllers\Admin\ClusterController as AdminClusterController;
use App\Http\Controllers\Admin\BranchController as AdminBranchController;
use App\Http\Controllers\Admin\MachineController as AdminMachineController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\DeviceController as AdminDeviceController;

use App\Http\Controllers\Branch\PageController as BranchPageController;
use App\Http\Controllers\Branch\UserController as BranchUserController;
use App\Http\Controllers\Branch\TransactionController as BranchTransactionController;
use App\Http\Controllers\Branch\ReportController as BranchReportController;
use App\Http\Controllers\Branch\DeliveryLocationController as BranchDeliveryLocationController;
use App\Http\Controllers\Branch\PurchaseRequestController as BranchPurchaseRequestController;
use App\Http\Controllers\Branch\PurchaseOrderController as BranchPurchaseOrderController;
use App\Http\Controllers\Branch\PurchaseDeliveryController as BranchPurchaseDeliveryController;
use App\Http\Controllers\Branch\ProductController as BranchProductController;
use App\Http\Controllers\Branch\StockTransferRequestController as BranchStockTransferRequestController;
use App\Http\Controllers\Branch\StockTransferOrderController as BranchStockTransferOrderController;
use App\Http\Controllers\Branch\StockTransferDeliveryController as BranchStockTransferDeliveryController;
use App\Http\Controllers\Branch\ProductPhysicalCountController as BranchProductPhysicalCountController;
use App\Http\Controllers\Branch\ProductDisposalController as BranchProductDisposalController;
use App\Http\Controllers\Branch\ChargeAccountController as BranchChargeAccountController;

use Illuminate\Support\Facades\Response;


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

Route::get('/error', function () {
    abort(500);
});

Route::get('/download-product-import-template', function () {
    $filePath = storage_path('app/Files/product_import_template.xlsx'); // Adjust the path as necessary

    if (!file_exists($filePath)) {
        abort(404, "File not found");
    }

    return Response::download($filePath);
});

Route::get('/map-data', [TestController::class, 'mapData']);

require __DIR__ . '/auth.php';


Route::view('/swagger', 'swagger');

Route::view('/', 'comingSoon');

Route::middleware('auth')->group(function () {
    Route::prefix('ajax')->group(function () {
        Route::get('/get-provinces', [AjaxController::class, 'getProvinces']);
        Route::get('/get-cities', [AjaxController::class, 'getCities']);
        Route::get('/get-barangays', [AjaxController::class, 'getBarangays']);
        Route::get('/get-clusters', [AjaxController::class, 'getClusters']);
        Route::get('/get-department-categories', [AjaxController::class, 'getDepartmentCategories']);
        Route::get('/get-category-subcategories', [AjaxController::class, 'getCategorySubcategories']);
        Route::get('/get-products', [AjaxController::class, 'getProducts']);
        Route::get('/get-department-suppliers', [AjaxController::class, 'getDepartmentSuppliers']);
        Route::get('/get-product-uoms', [AjaxController::class, 'getProductUoms']);
        Route::get('/get-product-details', [AjaxController::class, 'getProductDetails']);
        Route::get('/get-uom-conversions', [AjaxController::class, 'getUomConversions']);
    });

    Route::prefix('admin')->group(function () {
        Route::middleware(['auth', SuperAdminMiddleware::class])->group(function () {
            Route::get('/', [AdminPageController::class, 'dashboard'])->name('admin.dashboard');
            Route::resource('companies', AdminCompanyController::class, ['as' => 'admin']);
            Route::resource('clients', AdminClientController::class, ['as' => 'admin']);
            Route::resource('clusters', AdminClusterController::class, ['as' => 'admin']);
            Route::resource('branches', AdminBranchController::class, ['as' => 'admin']);

            Route::prefix('branches/{branchId}')->group(function () {
                Route::resource('machines', AdminMachineController::class, ['as' => 'admin']);
                Route::resource('devices', AdminDeviceController::class, ['as' => 'admin']);
            });
        });
    });

    //company
    Route::middleware([ValidateCompanySlug::class])->prefix('{companySlug}')->group(function () {
        Route::get('/', [CompanyPageController::class, 'dashboard'])->name('company.dashboard');
        Route::resource('branches', CompanyBranchController::class, ['as' => 'company']);
        Route::resource('clusters', CompanyClusterController::class, ['as' => 'company']);
        Route::resource('departments', CompanyDepartmentController::class, ['as' => 'company']);
        Route::resource('suppliers', CompanySupplierController::class, ['as' => 'company']);
        Route::resource('categories', CompanyCategoryController::class, ['as' => 'company']);
        Route::resource('subcategories', CompanySubcategoryController::class, ['as' => 'company']);
        Route::resource('unit-of-measurements', CompanyUnitOfMeasurementController::class, ['as' => 'company']);
        Route::post('/unit-of-measurements/save-conversion', [CompanyUnitOfMeasurementController::class, 'saveConversion'])->name('company.unit-of-measurements.save-conversion');
        Route::resource('payment-types', CompanyPaymentTypeController::class, ['as' => 'company']);
        Route::resource('banks', CompanyBankController::class, ['as' => 'company']);
        Route::resource('discount-types', CompanyDiscountTypeController::class, ['as' => 'company']);
        Route::resource('item-types', CompanyItemTypeController::class, ['as' => 'company']);
        Route::resource('product-disposal-reasons', CompanyProductDisposalReasonController::class, ['as' => 'company']);

        Route::resource('products', CompanyProductController::class, ['as' => 'company']);
        Route::get('/branch/{branchId}/inventory', [CompanyProductController::class, 'inventory'])->name('company.branch-inventory.index');
        Route::get('/branch/{branchId}/inventory/{productId}', [CompanyProductController::class, 'inventoryProduct'])->name('company.branch-inventory.show');
        Route::get('/branch/{branchId}/inventory-download', [CompanyProductController::class, 'inventoryDownload'])->name('company.branch-inventory.download');

        Route::get('/import-product', [CompanyProductController::class, 'showForm']);
        Route::post('/import-product', [CompanyProductController::class, 'import'])->name('company.products.import');
        Route::resource('users', CompanyUserController::class, ['as' => 'company']);
        Route::resource('payment-terms', CompanyPaymentTermController::class, ['as' => 'company']);
        Route::resource('supplier-terms', CompanySupplierTermController::class, ['as' => 'company']);
        Route::resource('purchase-requests', CompanyPurchaseRequestController::class, ['as' => 'company']);
        Route::resource('purchase-orders', CompanyPurchaseOrderController::class, ['as' => 'company']);
        Route::resource('purchase-deliveries', CompanyPurchaseDeliveryController::class, ['as' => 'company']);
        Route::resource('stock-transfer-requests', CompanyStockTransferRequestController::class, ['as' => 'company']);
        Route::resource('product-disposals', CompanyProductDisposalController::class, ['as' => 'company']);
        Route::resource('product-physical-counts', CompanyProductPhysicalCountController::class, ['as' => 'company']);

        Route::get('/purchase-requests/{id}/print', [CompanyPurchaseRequestController::class, 'print'])->name('company.purchase-requests.print');
        Route::get('/purchase-orders/{id}/print', [CompanyPurchaseOrderController::class, 'print'])->name('company.purchase-orders.print');
        Route::get('/purchase-deliveries/{id}/print', [CompanyPurchaseDeliveryController::class, 'print'])->name('company.purchase-deliveries.print');

        Route::resource('roles', CompanyRoleController::class, ['as' => 'company']);
        Route::resource('item-locations', CompanyItemLocationController::class, ['as' => 'company']);
        Route::resource('change-price-reasons', CompanyChangePriceReasonController::class, ['as' => 'company']);

        Route::prefix('reports')->group(function () {
            Route::get('/transactions', [CompanyReportController::class, 'transactions'])->name('company.reports.transactions');
            Route::get('/transaction/{transactionId}', [CompanyReportController::class, 'viewTransaction'])->name('company.reports.view-transaction');
            Route::get('export-custom-users', [CompanyReportController::class, 'exportCustomUsers']);

            Route::match(['get', 'post'], 'sales-transaction-report', [CompanyReportController::class, 'salesTransactionReport'])->name('company.reports.sales-transaction-report');
            Route::match(['get', 'post'], 'sales-invoices-report', [CompanyReportController::class, 'salesInvoicesReport'])->name('company.reports.sales-invoices-report');
            Route::match(['get', 'post'], 'void-transactions-report', [CompanyReportController::class, 'voidTransactionsReport'])->name('company.reports.void-transactions-report');
            Route::match(['get', 'post'], 'vat-sales-report', [CompanyReportController::class, 'vatSalesReport'])->name('company.reports.vat-sales-report');
            Route::match(['get', 'post'], 'x-reading-report', [CompanyReportController::class, 'xReadingReport'])->name('company.reports.x-reading-report');
            Route::match(['get', 'post'], 'z-reading-report', [CompanyReportController::class, 'zReadingReport'])->name('company.reports.z-reading-report');
            Route::match(['get', 'post'], 'discounts-report', [CompanyReportController::class, 'discountsReport'])->name('company.reports.discounts-report');
            Route::match(['get', 'post'], 'item-sales', [CompanyReportController::class, 'itemSales'])->name('company.reports.item-sales-report');

            Route::get('stock-card', [CompanyReportController::class, 'stockCard'])->name('company.reports.stock-card');
            Route::match(['get', 'post'], 'audit-trail', [CompanyReportController::class, 'auditTrail'])->name('company.reports.audit-trail');
            Route::match(['get', 'post'], 'bir-sales-summary-report', [CompanyReportController::class, 'birSalesSummaryReport'])->name('company.reports.bir-sales-summary-report');
            Route::match(['get', 'post'], 'bir-senior-citizen-sales-report', [CompanyReportController::class, 'birSeniorCitizenSalesReport'])->name('company.reports.bir-senior-citizen-sales-report');
            Route::match(['get', 'post'], 'bir-pwd-sales-report', [CompanyReportController::class, 'birPwdSalesReport'])->name('company.reports.bir-pwd-sales-report');
            Route::match(['get', 'post'], 'bir-naac-sales-report', [CompanyReportController::class, 'birNaacSalesReport'])->name('company.reports.bir-naac-sales-report');
            Route::match(['get', 'post'], 'bir-solo-parent-sales-report', [CompanyReportController::class, 'birSoloParentSalesReport'])->name('company.reports.bir-solo-parent-sales-report');
        });

        //branch
        Route::middleware([ValidateCompanySlug::class])->prefix('{branchSlug}')->group(function () {
            Route::get('/', [BranchPageController::class, 'dashboard'])->name('branch.dashboard');
            Route::resource('users', BranchUserController::class, ['as' => 'branch']);
            Route::resource('delivery-locations', BranchDeliveryLocationController::class, ['as' => 'branch']);
            Route::resource('purchase-requests', BranchPurchaseRequestController::class, ['as' => 'branch']);
            Route::resource('purchase-orders', BranchPurchaseOrderController::class, ['as' => 'branch']);
            Route::resource('products', BranchProductController::class, ['as' => 'branch']);
            Route::get('/purchase-orders/{purchaseOrderId}/create-delivery', [BranchPurchaseDeliveryController::class, 'create'])->name('branch.purchase-delivery.create');
            Route::resource('purchase-deliveries', BranchPurchaseDeliveryController::class, ['as' => 'branch']);
            Route::resource('stock-transfer-requests', BranchStockTransferRequestController::class, ['as' => 'branch']);
            Route::resource('stock-transfer-orders', BranchStockTransferOrderController::class, ['as' => 'branch']);
            Route::resource('stock-transfer-deliveries', BranchStockTransferDeliveryController::class, ['as' => 'branch']);
            Route::resource('product-physical-counts', BranchProductPhysicalCountController::class, ['as' => 'branch']);
            Route::resource('product-disposals', BranchProductDisposalController::class, ['as' => 'branch']);

            Route::get('/purchase-requests/{id}/print', [BranchPurchaseRequestController::class, 'print'])->name('branch.purchase-requests.print');
            Route::get('/purchase-orders/{id}/print', [BranchPurchaseOrderController::class, 'print'])->name('branch.purchase-orders.print');
            Route::get('/purchase-deliveries/{id}/print', [BranchPurchaseDeliveryController::class, 'print'])->name('branch.purchase-deliveries.print');
            Route::get('/product-disposals/{id}/print', [BranchProductDisposalController::class, 'print'])->name('branch.product-disposals.print');

            Route::get('transactions', [BranchTransactionController::class, 'index', ['as' => 'branch']])->name('branch.transactions.index');

            Route::resource('charge-accounts', BranchChargeAccountController::class, ['as' => 'branch']);

            Route::prefix('reports')->group(function () {
                Route::get('/transactions', [BranchReportController::class, 'transactions'])->name('branch.reports.transactions');
                Route::get('/transaction/{transactionId}', [BranchReportController::class, 'viewTransaction'])->name('branch.reports.view-transaction');

                Route::match(['get', 'post'], 'sales-transaction-report', [BranchReportController::class, 'salesTransactionReport'])->name('branch.reports.sales-transaction-report');
                Route::match(['get', 'post'], 'sales-invoices-report', [BranchReportController::class, 'salesInvoicesReport'])->name('branch.reports.sales-invoices-report');
                Route::match(['get', 'post'], 'void-transactions-report', [BranchReportController::class, 'voidTransactionsReport'])->name('branch.reports.void-transactions-report');
                Route::match(['get', 'post'], 'vat-sales-report', [BranchReportController::class, 'vatSalesReport'])->name('branch.reports.vat-sales-report');
                Route::match(['get', 'post'], 'x-reading-report', [BranchReportController::class, 'xReadingReport'])->name('branch.reports.x-reading-report');
                Route::match(['get', 'post'], 'z-reading-report', [BranchReportController::class, 'zReadingReport'])->name('branch.reports.z-reading-report');
                Route::match(['get', 'post'], 'discounts-reports', [BranchReportController::class, 'discountsReport'])->name('branch.reports.discounts-report');
                Route::match(['get', 'post'], 'item-sales', [BranchReportController::class, 'itemSales'])->name('branch.reports.item-sales-report');

                Route::get('stock-card', [BranchReportController::class, 'stockCard'])->name('branch.reports.stock-card');
                Route::match(['get', 'post'], 'audit-trail', [BranchReportController::class, 'auditTrail'])->name('branch.reports.audit-trail');

                Route::get('backup', [BranchReportController::class, 'backup'])->name('branch.reports.backup');

                Route::get('account-receivables', [BranchReportController::class, 'accountReceivables'])->name('branch.reports.account-receivables');
                Route::get('account-receivables/{customerId}', [BranchReportController::class, 'accountReceivableDetails'])->name('branch.reports.account-receivable-details');
                
            });
        });
    });
});

Route::get('/company/{company}/dashboard/department-products', [CompanyPageController::class, 'getDepartmentProducts'])->name('company.dashboard.department-products');