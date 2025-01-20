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
        Route::get('/price-change-reasons/{branchId}', [MiscController::class, 'priceChangeReasons']);

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

        Route::post('/take-order-discounts', [MiscController::class, 'saveTakeOrderDiscounts']);
        Route::get('/take-order-discounts', [MiscController::class, 'getTakeOrderDiscounts']);

        Route::post('/discount-details', [MiscController::class, 'saveDiscountDetails']);
        Route::get('/discount-details', [MiscController::class, 'getDiscountDetails']);

        Route::post('/take-order-discount-details', [MiscController::class, 'saveTakeOrderDiscountDetails']);
        Route::get('/take-order-discount-details', [MiscController::class, 'getTakeOrderDiscountDetails']);

        Route::get('/branch-take-order-transactions', [MiscController::class, 'getTakeOrderTransactions']);
        Route::post('/branch-take-order-transactions', [MiscController::class, 'saveTakeOrderTransactions']);

        Route::get('/branch-take-order-orders', [MiscController::class, 'getTakeOrderOrders']);
        Route::post('/branch-take-order-orders', [MiscController::class, 'saveTakeOrderOrders']);

        Route::get('/payment-other-informations', [MiscController::class, 'getPaymentOtherInformations']);
        Route::post('/payment-other-informations', [MiscController::class, 'savePaymentOtherInformations']);

        Route::get('/discount-other-informations', [MiscController::class, 'getDiscountOtherInformations']);
        Route::post('/discount-other-informations', [MiscController::class, 'saveDiscountOtherInformations']);

        Route::get('/take-order-discount-other-informations', [MiscController::class, 'getTakeOrderDiscountOtherInformations']);
        Route::post('/take-order-discount-other-informations', [MiscController::class, 'saveTakeOrderDiscountOtherInformations']);

        //cutOffDepartments
        Route::get('/cut-off-departments', [MiscController::class, 'getCutOffDepartments']);
        Route::post('/cut-off-departments', [MiscController::class, 'saveCutOffDepartments']);

        //cutOffDiscounts
        Route::get('/cut-off-discounts', [MiscController::class, 'getCutOffDiscounts']);
        Route::post('/cut-off-discounts', [MiscController::class, 'saveCutOffDiscounts']);

        //cutOffPayments
        Route::get('/cut-off-payments', [MiscController::class, 'getCutOffPayments']);
        Route::post('/cut-off-payments', [MiscController::class, 'saveCutOffPayments']);

        //endOfDayDiscounts
        Route::get('/end-of-day-discounts', [MiscController::class, 'getEndOfDayDiscounts']);
        Route::post('/end-of-day-discounts', [MiscController::class, 'saveEndOfDayDiscounts']);

        //endOfDayPayments
        Route::get('/end-of-day-payments', [MiscController::class, 'getEndOfDayPayments']);
        Route::post('/end-of-day-payments', [MiscController::class, 'saveEndOfDayPayments']);

        Route::post('/end-of-day-departments', [MiscController::class, 'saveEndOfDayDepartments']);

        Route::post('/update-branch-transactions', [MiscController::class, 'bulkSaveTransactions']);

        Route::get('/cash-funds', [MiscController::class, 'getCashFunds']);
        Route::post('/cash-funds', [MiscController::class, 'saveCashFunds']);

        Route::get('/cash-fund-denominations', [MiscController::class, 'getCashFundDenominations']);
        Route::post('/cash-fund-denominations', [MiscController::class, 'saveCashFundDenominations']);

        Route::post('/audit-trails', [MiscController::class, 'saveAuditTrails']);
        Route::get('/audit-trails', [MiscController::class, 'getAuditTrails']);

        Route::get('/cut-off-products', [MiscController::class, 'getCutOffProducts']);
        Route::post('/cut-off-products', [MiscController::class, 'saveCutOffProducts']);

        Route::get('/payouts', [MiscController::class, 'getPayouts']);
        Route::post('/payouts', [MiscController::class, 'savePayouts']);

        Route::get('/official-receipt-informations', [MiscController::class, 'getOfficialReceiptInformations']);
        Route::post('/official-receipt-informations', [MiscController::class, 'saveOfficialReceiptInformations']);

        Route::get('/spot-audits', [MiscController::class, 'getSpotAudits']);
        Route::post('/spot-audits', [MiscController::class, 'saveSpotAudits']);

        Route::get('/spot-audit-denominations', [MiscController::class, 'getSpotAuditDenominations']);
        Route::post('/spot-audit-denominations', [MiscController::class, 'saveSpotAuditDenominations']);

        Route::get('/end-of-day-products', [MiscController::class, 'getEndOfDayProducts']);
        Route::post('/end-of-day-products', [MiscController::class, 'saveEndOfDayProducts']);

        Route::get('/branch-product-soh', [MiscController::class, 'getProductSoh']);
        Route::get('/unredeemed-ar-transactions', [MiscController::class, 'getUnredeemedArTransactions']);

        Route::post('/ar-transactions', [MiscController::class, 'updateArTransaction']);

        Route::get('/branch-tables/{branchId}', [MiscController::class, 'getBranchTables']);
        Route::get('/branch-table-locations/{branchId}', [MiscController::class, 'getBranchTableLocations']);
        Route::get('/branch-table-statuses/{branchId}', [MiscController::class, 'getBranchTableStatuses']);
    });

    Route::get('/logout', [AuthController::class, 'logout']);
});