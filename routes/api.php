<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompaniesController;
use App\Http\Controllers\Api\ClustersController;
use App\Http\Controllers\Api\BranchesController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\BarangayController;
use App\Http\Controllers\Api\MachineController;
use App\Http\Controllers\Api\MiscController;

use App\Http\Middleware\MachineValidationMiddleware;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Route::apiResource('users', UsersController::class);

    // Route::apiResource('clusters', ClustersController::class);
    // Route::apiResource('regions', RegionController::class);
    // Route::apiResource('region/{regionId}/provinces', ProvinceController::class);
    // Route::apiResource('provinces', ProvinceController::class);
    // Route::apiResource('province/{provinceId}/cities', CityController::class);
    // Route::apiResource('cities', CityController::class);
    // Route::apiResource('city/{cityId}/barangays', BarangayController::class);
    // Route::apiResource('barangays', BarangayController::class);

    //sync
    Route::post('/activate-machine', [MachineController::class, 'activate']);
    Route::get('/test-connection', [MiscController::class, 'testConnection']);

    Route::middleware([MachineValidationMiddleware::class])->group(function () {
        Route::apiResource('companies', CompaniesController::class);
        Route::apiResource('branches', BranchesController::class);
        Route::apiResource('machines', MachineController::class);

        Route::get('/cash-denominations', [MiscController::class, 'cashDenominations']);
        Route::get('/departments/{branchId}', [MiscController::class, 'departments']);
        Route::get('/categories/{branchId}', [MiscController::class, 'categories']);
        Route::get('/sub-categories/{branchId}', [MiscController::class, 'subCategories']);
        Route::get('/branch-users/{branchId}', [MiscController::class, 'branchUsers']);
        Route::get('/payment-types/{branchId}', [MiscController::class, 'paymentTypes']);
        Route::get('/discount-types/{branchId}', [MiscController::class, 'discountTypes']);
        Route::get('/charge-accounts/{branchId}', [MiscController::class, 'chargeAccounts']);
        Route::get('/branch-products/{branchId}', [MiscController::class, 'products']);

        Route::get('/branch-transactions', [MiscController::class, 'getTransactions']);
        Route::post('/branch-transactions', [MiscController::class, 'saveTransactions']);

        Route::get('/branch-orders', [MiscController::class, 'getOrders']);
        Route::post('/branch-orders', [MiscController::class, 'saveOrders']);

        Route::post('/branch-payments', [MiscController::class, 'savePayments']);
        Route::get('/branch-payments', [MiscController::class, 'getPayments']);

        Route::get('/safekeeping', [MiscController::class, 'getSafekeepings']);
        Route::post('/safekeeping', [MiscController::class, 'saveSafekeepings']);

        Route::get('/safekeeping-denominations', [MiscController::class, 'getSafekeepingDenominations']);
        Route::post('/safekeeping-denominations', [MiscController::class, 'saveSafekeepingsDenominations']);

        Route::post('/end-of-days', [MiscController::class, 'saveEndOfDays']);
        Route::get('/end-of-days', [MiscController::class, 'getEndOfDays']);

        Route::post('/cut-offs', [MiscController::class, 'saveCutOffs']);
        Route::get('/cut-offs', [MiscController::class, 'getCutOffs']);

        Route::post('/discounts', [MiscController::class, 'saveDiscounts']);
        Route::get('/discounts', [MiscController::class, 'getDiscounts']);

        Route::post('/discount-details', [MiscController::class, 'saveDiscountDetails']);
        Route::get('/discount-details', [MiscController::class, 'getDiscountDetails']);

        Route::get('/branch-take-order-transactions', [MiscController::class, 'getTakeOrderTransactions']);
        Route::post('/branch-take-order-transactions', [MiscController::class, 'saveTakeOrderTransactions']);

        Route::get('/branch-take-order-orders', [MiscController::class, 'getTakeOrderOrders']);
        Route::post('/branch-take-order-orders', [MiscController::class, 'saveTakeOrderOrders']);

        Route::apiResource('branches', BranchesController::class);
    });

    Route::get('/logout', [AuthController::class, 'logout']);
});