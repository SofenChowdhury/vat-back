<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\Admin\BomController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Admin\SalesController;
use App\Http\Controllers\Api\Admin\BranchController;
use App\Http\Controllers\Api\Admin\HsCodeController;
use App\Http\Controllers\Api\Admin\MushokController;
use App\Http\Controllers\Api\Admin\TicketController;
use App\Http\Controllers\Api\Admin\VendorController;
use App\Http\Controllers\Api\Admin\CommentController;
use App\Http\Controllers\Api\Admin\CompanyController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\CustomerController;
use App\Http\Controllers\Api\Admin\PurchaseController;
use App\Http\Controllers\Api\Admin\TransferController;
use App\Http\Controllers\Api\Admin\OpenStockController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Admin\VatPaymentController;
use App\Http\Controllers\Api\Admin\MeasurementController;
use App\Http\Controllers\Api\Admin\ActivityLogsController;
use App\Http\Controllers\Api\Admin\MushokReturnController;
use App\Http\Controllers\Api\Admin\FinishedGoodsController;
use App\Http\Controllers\Api\Admin\VatAdjustmentController;
use App\Http\Controllers\Api\Admin\ValueAdditionHeadController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// Route::get('admin-list',[AdminController::class,'index']);
Route::group(['middleware' => ['api','throttle:60,1'],'prefix' => 'v1/auth'], function ($router) {
    Route::get('/yes', function () {
        return "OK";
    });
    Route::post('login',  [AuthController::class ,'login']);
    Route::post('logout', [AuthController::class ,'logout']);
    Route::post('refresh',[AuthController::class ,'refresh']);
    Route::post('register', [AuthController::class ,'store']);
    // Route::post('/send-otp', [AuthController::class ,'sendingOtp']);
    Route::post('verify-token', [AuthController::class ,'verifyToken']);
    Route::get('profile', [AuthController::class ,'me']);
    Route::post('profile/update-photo', [UserController::class,'userChangePhoto']);
    Route::post('change-password', [AuthController::class,'changePassword']);
    // Route::post('change-password', function(){
    //     return "Ahsan Ullah";
    // });
    // Route::post('profile/update', [UserController::class,'userProfileUpdate']);

});

Route::group(['prefix' => 'v1','middleware' => ['api', 'jwt.verify', 'throttle:60,1']], function () {
    Route::get('admin-list', [AdminController::class, 'index']);
    Route::get('admins/{id}', [AdminController::class, 'show']);
    Route::post('admins/update', [AdminController::class, 'update']);
    Route::post('admin/store' ,[AdminController::class, 'store']);
    Route::get('admin/{query}' ,[AdminController::class, 'searchByPhoneNameEmail']);
});

Route::group(['prefix' => 'v1','middleware' => ['api', 'jwt.verify', 'throttle:60,1']], function () {
    Route::get('permissions',[PermissionController::class,'index']);
    Route::get('permissions/paginate',[PermissionController::class,'getAllPaginate']);
    Route::post('permissions/create' , [PermissionController::class,'create']);
});

Route::group(['prefix' => 'v1', 'middleware' => ['api', 'jwt.verify', 'throttle:60,1']], function () {
    Route::get('roles',[RoleController::class,'index']);
    Route::post('roles/create' , [RoleController::class,'create']);
    Route::get('roles/{id}' , [RoleController::class,'show']);
    Route::post('roles/update' , [RoleController::class,'update']);
});

Route::group(['prefix' => 'v1', 'middleware' => ['api', 'jwt.verify', 'throttle:60,1']], function () {

    Route::get('companies',[CompanyController::class,'index']);
    Route::post('companies/create' , [CompanyController::class,'create']);
    Route::get('companies/{id}' , [CompanyController::class,'show']);
    Route::post('companies/update' , [CompanyController::class,'update']);
});

Route::group(['prefix' => 'v1', 'middleware' => ['api', 'jwt.verify', 'throttle:60,1']], function () {

    Route::get('vendors',[VendorController::class,'index']);
    Route::get('vendors/search',[VendorController::class,'search']);
    Route::post('vendors/create' , [VendorController::class,'create']);
    Route::get('vendors/{id}' , [VendorController::class,'show']);
    Route::post('vendors/update' , [VendorController::class,'update']);
    Route::post('vendors/destroy' , [VendorController::class,'destroy']);
});

Route::group(['prefix' => 'v1', 'middleware' => ['api', 'jwt.verify', 'throttle:60,1']], function () {
    Route::get('categories', [CategoryController::class,'index']);
    Route::post('categories/create', [CategoryController::class,'create']);
    Route::get('categories/{id}', [CategoryController::class,'show']);
    Route::post('categories/update', [CategoryController::class,'update']);
    Route::post('categories/destroy', [CategoryController::class,'destroy']);
});

Route::group(['prefix' => 'v1', 'middleware' => ['api', 'jwt.verify', 'throttle:60,1']], function () {

    Route::controller(ProductController::class)->group(function(){
        // Route::get('products','index');
        Route::get('products','listWithSearch');
        Route::get('products/goods','finishedGoods');
        Route::get('products/download','download');
        Route::get('products/raw','rawMaterials');
        Route::get('products/accessories','accessories');
        Route::get('products/services','services');
        Route::get('products/service/search/{query}','serviceSearch');
        Route::get('products/stock','stock');
        Route::post('products/stock-merge','stockMerge');
        Route::get('products/stock/download','stockDownload');
        Route::post('products/create' , 'create');
        Route::get('products/{id}' ,'show');
        Route::get('products/sku/{sku}' ,'showSku');
        Route::get('product-search', 'search');
        Route::post('product-multi-search', 'multiSearch');
        Route::post('products/update', 'update');
        Route::post('products/bulk-upload', 'bulkUpload');
        // Reporting
        Route::post('products/stock-summery', 'stockSummery');
        Route::post('products/stock-report', 'stockReport');
        Route::post('products/stock-report-download', 'stockReportDownload');
       
    });

    Route::controller(CustomerController::class)->group(function(){
        Route::get('customers','index');
        Route::post('customers/create' , 'create');
        Route::get('customers/{id}' ,'show');
        Route::get('customers/search/{query}' ,'searchByPhoneNameEmail');
        Route::post('customers/update' , 'update');
        Route::post('customers/delete' , 'update');
        Route::post('customer/bulk-upload', 'bulkUpload');
    });

    Route::controller(PurchaseController::class)->group(function(){
        Route::get('purchases','index');
        Route::get('purchases/return','returnList');
        Route::get('purchases/return-download','returnDownload');
        Route::post('purchases/return-entry-bulk','returnEntryManualBulk');
        Route::post('purchases/return-entry','returnEntryManual');
        Route::get('purchases/search', 'search');
        Route::get('purchases/mushok_six_one', 'mushok_six_one');
        Route::get('purchases/mushok_six_two', 'mushok_six_two');
        Route::get('purchase-sub-form', 'purchaseSubForm');
        Route::get('purchases-return-sub-form', 'returnSubForm');
        Route::post('purchases/create' , 'create');
        Route::post('contractual/create' , 'contractualCreate');
        Route::post('purchases/create-bulk', 'createBulk');
        Route::post('purchases/return' , 'return');
        Route::post('purchases/return-update' , 'returnUpdate');
        Route::get('purchases/{id}' ,'show');
        Route::get('purchases-download' ,'download');
        Route::get('purchases/return/{id}' ,'returnShow');
        Route::post('purchases/update' , 'update');
        Route::post('purchases/update-item' , 'updateItem');
        Route::post('purchases/add-item' , 'addItem');
        Route::post('purchases/remove-item' , 'removeItem');
        Route::post('purchases/delete' , 'destroy');
        // Reports
        Route::post('purchases/report/comparison','comparisonReport');
        Route::post('purchases/vendor-statement','vendorStatement');
        Route::post('purchases/product-statement','productPurchaseStatement');
    });
    
    Route::controller(BranchController::class)->group(function(){
        Route::get('branches', 'index');
        Route::get('branch-list', 'list');
        Route::post('branches/create', 'create');
        Route::get('branches/{id}' ,'show');
        Route::post('branches/update' , 'update');
    });

    Route::controller(MeasurementController::class)->group(function(){
        Route::get('units', 'index');
        Route::post('units/create', 'create');
        Route::get('units/{id}' ,'show');
        Route::post('units/update' , 'update');
        Route::post('units/delete' , 'delete');
    });

    Route::controller(SalesController::class)->group(function(){
        Route::get('sales', 'index');
        Route::get('sales/search', 'search');
        Route::get('sales/download', 'download');
        Route::get('sales/return', 'returnList');
        Route::get('sales/return-sub-form', 'returnSubForm');
        Route::post('sales/return' , 'return');
        Route::get('sales/credit-note-download' , 'returnDownload');
        Route::post('sales/manualCreditNote' , 'manualCreditNote');
        Route::post('sales/create' , 'create');
        Route::post('contractual-delivery/create' , 'contractualDelivery');
        Route::post('sales/branchBulkUpload', 'branchBulkUpload');
        Route::post('sales/bulkUpload', 'regularBulkUpload');
        Route::post('sales/roll-back', 'rollBack');
        Route::post('sales/draft' , 'draftSales');
        Route::post('sales/draft-update' , 'draftUpdate');
        Route::get('sales/{id}' ,'show');
        Route::get('sales-sms/{id}' ,'smsTest');
        Route::get('sales/return/{id}' ,'returnShow');
        Route::post('sales/update' , 'update');
        Route::post('sales/item-update' , 'updateItem');
        Route::post('sales/remove-items' , 'itemRemove');
        Route::post('sales/add-items' , 'addSalesItem');
        Route::post('sales/delete' , 'destroy');
        Route::post('sales/delete-bulk' , 'deleteSalesBulk');
        Route::get('sales-print' , 'print');
        // Mushok
        Route::get('sales-sub-form', 'salesSubForm');
        // Reporting
        
        Route::post('sales/report/comparison','comparisonReport');
        Route::post('sales/customer-statement','customerStatement');
        Route::post('sales/product-statement','productSalesStatement');
    });

    Route::controller(BomController::class)->group(function(){
        Route::get('boms', 'index');
        Route::post('boms/create' , 'create');
        Route::get('boms/{id}' ,'show');
        Route::post('boms/bulk-upload' ,'bulkUpload');
        Route::post('boms/bulk-bom-create' ,'bulkUploadCreate');
        Route::post('boms/update' , 'update');
        Route::post('boms/delete' , 'destroy');
        Route::post('boms/history' , 'reportHistory');
    });

    Route::controller(FinishedGoodsController::class)->group(function(){
        Route::get('goods', 'search');
        Route::get('goods-download', 'goodsDownload');
        Route::post('goods/create' , 'create');
        Route::get('goods/{id}' ,'show');
        Route::post('goods/update' , 'update');
        Route::post('goods/remove-item' , 'removeItem');
        Route::post('goods/add-item' , 'addItem');
        Route::post('goods/delete' , 'destroy');

        // Reports
        // Route::post('goods/date-wise-report', 'dateWiseReport');
        Route::post('goods/date-wise-report', 'dateWiseReport');
    });

    Route::controller(ValueAdditionHeadController::class)->group(function(){
        Route::get('value-additions', 'index');
        Route::post('value-additions/create' , 'create');
        Route::get('value-additions/{id}' ,'show');
        Route::post('value-additions/update' , 'update');
        Route::post('value-additions/delete' , 'destroy');
    });

    Route::controller(MushokController::class)->group(function(){
        Route::get('mushok/six-one', 'sixOne');
        Route::get('mushok/six-two', 'sixTwo');
        Route::get('mushok/six-two-one', 'sixTwoOne');
        Route::get('mushok/six-ten', 'sixTen');
        Route::get('mushok/nine-one', 'nineOne');
        // Route::get('mushok/sales-sub-form', 'salesSubForm');
        Route::get('mushok/purchase-sub-form', 'purchaseSubForm');
        Route::get('mushok/vdsList', 'vdsList');
        Route::post('mushok/deposit', 'vatDeposit');
        Route::post('mushok/upload', 'upload');
        Route::get('mushok/collect-vds', 'collectVds');
        Route::get('mushok/report', 'report');
        Route::get('mushok/six-two-correction', 'correction');
        // Report
        Route::get('mushok/six-two-one-summery', 'sixTwoOneSummery');
    });

    Route::controller(MushokReturnController::class)->group(function(){
        Route::post('mushok/return-submit', 'returnSubmit');
    });

    Route::group(['middleware' => ['api','throttle:60,1'],'prefix' => 'v1/auth'], function ($router) {
    Route::post('login',  [AuthController::class ,'login']);
    Route::post('logout', [AuthController::class ,'logout']);
    Route::post('refresh',[AuthController::class ,'refresh']);
    // Route::post('register', [AuthController::class ,'store']);
    // Route::post('/send-otp', [AuthController::class ,'sendingOtp']);
    Route::post('verify-token', [AuthController::class ,'verifyToken']);
    Route::get('profile', [AuthController::class ,'me']);
    Route::post('profile/update-photo', [UserController::class,'userChangePhoto']);
    Route::post('change-password', [AuthController::class,'changePassword']);
    // Route::post('change-password', function(){
    //     return "Ahsan Ullah";
    // });
    // Route::post('profile/update', [UserController::class,'userProfileUpdate']);

});

    Route::controller(VatPaymentController::class)->group(function(){
        Route::get('vat-payments', 'index');
        Route::post('vat-payments/payment', 'vatDeposit');
        Route::post('vat-payments/update', 'update');
        Route::get('vat-payments/{id}', 'show');
        Route::get('vat-payment-sub-form', 'vatPaymentSubForm');
    });

    Route::controller(VatAdjustmentController::class)->group(function(){
        Route::get('vat-adjustment/challan', 'challanSearch');
        Route::get('vat-adjustment', 'index');
        Route::post('vat-adjustment/payment', 'payment');
        Route::post('vat-adjustment/update', 'update');
        Route::post('vat-adjustment/upload', 'upload');
        Route::get('vat-adjustment/download', 'download');
        Route::get('vat-adjustment/{id}', 'show');
        Route::get('vds-adjustment-sub-form', 'vdsAdjustmentList');
    });

    Route::controller(HsCodeController::class)->group(function(){
        Route::get('hs-codes', 'index');
        Route::get('hs-codes/search', 'advanceSearch');
        Route::get('hs-codes-search', 'search');
        Route::get('hs-codes/{id}', 'getById');
        Route::get('hs-codes-search/{code}', 'getByCode');
        Route::get('hs-codes-download', 'download');
    });

    Route::controller(TransferController::class)->group(function(){
        Route::get('transfers','index');
        Route::get('transfers/search', 'search');
        Route::post('transfers/create' , 'create');
        Route::get('transfers/{id}' ,'show');
        Route::get('transfer-download' ,'download');
        Route::post('transfers/update' , 'update');
        Route::post('transfers/upload' , 'bulkUpload');
        Route::post('transfers/delete' , 'destroy');
    });

    Route::controller(OpenStockController::class)->group(function(){
        Route::get('stock','index');
        Route::get('stock/search', 'search');
        Route::post('stock/create' , 'create');
        Route::get('stock/{id}' ,'show');
        Route::post('stock/update' , 'update');
        Route::post('stock/upload' , 'upload');
        Route::get('stock-clear' , 'clearData');
    });
    
    Route::controller(TicketController::class)->group(function(){
        Route::get('tickets' , 'index');
        Route::post('tickets/create' , 'store');
        Route::post('tickets/update' , 'update');
        Route::post('tickets/delete' , 'delete');
        Route::get('tickets/{id}' , 'show');
        Route::post('tickets/assign' , 'assign');
    });
    
    Route::controller(CommentController::class)->group(function(){
        Route::post('comments/create' , 'store');
    });

    Route::controller(ActivityLogsController::class)->group(function(){
        Route::post('activities' , 'search');
        Route::get('activities/{id}' , 'show');
    });
});
Route::group(['prefix' => 'v1/public/', 'middleware' => ['api', 'throttle:60,1']], function () {
    Route::get('check-challan/{challan}', [SalesController::class, 'checkMushok']);
    Route::get('check-transfer/{challan}', [TransferController::class, 'checkTransfer']);
    Route::get('credit-note/{challan}', [SalesController::class, 'creditNote']);
    Route::get('debit-note/{challan}', [PurchaseController::class, 'debitNote']);
});