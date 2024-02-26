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
use App\Http\Controllers\Company\ChargeAccountController as CompanyChargeAccountController;
use App\Http\Controllers\Company\BankController as CompanyBankController;
use App\Http\Controllers\Company\DiscountTypeController as CompanyDiscountTypeController;
use App\Http\Controllers\Company\ItemTypeController as CompanyItemTypeController;
use App\Http\Controllers\Company\ProductController as CompanyProductController;
use App\Http\Controllers\Company\ReportController as CompanyReportController;
use App\Http\Controllers\Company\UserController as CompanyUserController;

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
        Route::resource('charge-accounts', CompanyChargeAccountController::class, ['as' => 'company']);
        Route::resource('banks', CompanyBankController::class, ['as' => 'company']);
        Route::resource('discount-types', CompanyDiscountTypeController::class, ['as' => 'company']);
        Route::resource('item-types', CompanyItemTypeController::class, ['as' => 'company']);
        Route::resource('products', CompanyProductController::class, ['as' => 'company']);
        Route::resource('users', CompanyUserController::class, ['as' => 'company']);

        Route::prefix('reports')->group(function () {
            Route::get('/transactions', [CompanyReportController::class, 'transactions'])->name('company.reports.transactions');
            Route::get('/transaction/{transactionId}', [CompanyReportController::class, 'viewTransaction'])->name('company.reports.view-transaction');
        });

        //branch
        Route::middleware([ValidateCompanySlug::class])->prefix('{branchSlug}')->group(function () {
            Route::get('/', [BranchPageController::class, 'dashboard'])->name('branch.dashboard');
            Route::resource('users', BranchUserController::class, ['as' => 'branch']);
            Route::get('transactions', [BranchTransactionController::class, 'index', ['as' => 'branch']])->name('branch.transactions.index');

            Route::prefix('reports')->group(function () {
                Route::get('/transactions', [BranchReportController::class, 'transactions'])->name('branch.reports.transactions');
                Route::get('/transaction/{transactionId}', [BranchReportController::class, 'viewTransaction'])->name('branch.reports.view-transaction');
            });
        });
    });
});