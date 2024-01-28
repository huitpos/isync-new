<?php

use Illuminate\Support\Facades\Route;

use App\Http\Middleware\ValidateCompanySlug;
use App\Http\Middleware\SuperAdminMiddleware;

use App\Http\Controllers\AjaxController;
use App\Http\Controllers\DashboardController;

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

use App\Http\Controllers\Admin\CompanyController as AdminCompanyController;
use App\Http\Controllers\Admin\ClusterController as AdminClusterController;
use App\Http\Controllers\Admin\BranchController as AdminBranchController;
use App\Http\Controllers\Admin\MachineController as AdminMachineController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\ClientController as AdminClientController;

use App\Http\Controllers\Branch\PageController as BranchPageController;
use App\Http\Controllers\Branch\UserController as BranchUserController;
use App\Http\Controllers\Branch\TransactionController as BranchTransactionController;



use App\Http\Controllers\Apps\RoleManagementController;
use App\Http\Controllers\Apps\UserManagementController;
use App\Http\Controllers\Apps\PermissionManagementController;

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

// Route::middleware(['auth'])->group(function () {

//     Route::get('/', [DashboardController::class, 'index']);

//     Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// });


Route::name('user-management.')->group(function () {
    Route::resource('/user-management/users', UserManagementController::class);
    Route::resource('/user-management/roles', RoleManagementController::class);
    Route::resource('/user-management/permissions', PermissionManagementController::class);
});

Route::get('/error', function () {
    abort(500);
});

require __DIR__ . '/auth.php';


Route::view('/swagger', 'swagger');

Route::view('/', 'comingSoon');
// Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::prefix('ajax')->group(function () {
    Route::get('/get-provinces', [AjaxController::class, 'getProvinces']);
    Route::get('/get-cities', [AjaxController::class, 'getCities']);
    Route::get('/get-barangays', [AjaxController::class, 'getBarangays']);
});

Route::prefix('admin')->group(function () {
    Route::middleware(['auth', SuperAdminMiddleware::class])->group(function () {
        Route::get('/', [AdminPageController::class, 'dashboard'])->name('admin.dashboard');
        Route::resource('companies', AdminCompanyController::class, ['as' => 'admin']);
        Route::resource('clients', AdminClientController::class, ['as' => 'admin']);
        Route::resource('clusters', AdminClusterController::class, ['as' => 'admin']);
        Route::resource('branches', AdminBranchController::class, ['as' => 'admin']);

        Route::prefix('branches/{branchid}')->group(function () {
            Route::resource('machines', AdminMachineController::class, ['as' => 'admin']);
        });
    });
});

Route::view('/test', 'content.pages.pages-account-settings-account');

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
    Route::resource('payment-types', CompanyPaymentTypeController::class, ['as' => 'company']);
    Route::resource('charge-accounts', CompanyChargeAccountController::class, ['as' => 'company']);
    Route::resource('banks', CompanyBankController::class, ['as' => 'company']);
    Route::resource('discount-types', CompanyDiscountTypeController::class, ['as' => 'company']);
    Route::resource('item-types', CompanyItemTypeController::class, ['as' => 'company']);
    Route::resource('products', CompanyProductController::class, ['as' => 'company']);

    //branch
    Route::middleware([ValidateCompanySlug::class])->prefix('{branchSlug}')->group(function () {
        Route::get('/', [BranchPageController::class, 'dashboard'])->name('branch.dashboard');
        Route::resource('users', BranchUserController::class, ['as' => 'branch']);
        Route::get('transactions', [BranchTransactionController::class, 'index', ['as' => 'branch']])->name('branch.transactions.index');
    });
});
