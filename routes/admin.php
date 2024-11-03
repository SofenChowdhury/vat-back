<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\SeriesController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboadController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\SettingController;

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
    return "Ashan Ullah";
});



Route::group(['middleware' => ['auth', 'verified']], function()
{
    Route::get('users',[ UserController::class , 'index'])->name('users');
    Route::get('dashboard',[DashboadController::class,'index'])->name('dashboard');
    Route::resource('roles', RoleController::class);
    Route::resource('companies', CompanyController::class);
    Route::resource('permissions', PermissionController::class);
    Route::resource('admins', AdminController::class); 
    Route::get('user-profile', [AdminController::class ,'userProfile'])->name('user.profile'); 
    Route::post('user-profile', [AdminController::class ,'storeUserProfile'])->name('update.user.profile'); 
    Route::get('change-password', [AdminController::class ,'changePassword'])->name('change.password'); 
    Route::post('change-password', [AdminController::class ,'StoreChangePassword'])->name('store.change.password'); 
    Route::resource('shops', ShopController::class);
    Route::post('admins-update',[ AdminController::class ,'update'])->name('admins.update'); 
    Route::resource('categories', CategoryController::class); 
    Route::resource('sliders', SliderController::class); 
    Route::resource('series', SeriesController::class); 
    Route::post('slider-update', [SliderController::class, 'update'])->name('sliders.update'); 
    


    Route::resource('orders', OrderController::class);
    Route::get('orders-search', [ OrderController::class , 'search'])->name('order.search');
    Route::get('order-invoice/{id}',[ OrderController::class , 'emailTemplate'])->name('order.invoice');
    // Route::resource('orders', OrderController::class);
    Route::post('update-order-status', [OrderController::class, 'update'])->name('update.order');
    Route::get('order-payment/{id}', [OrderController::class, 'orderPayment'])->name('order.payment');
    Route::get('order-print/{id}', [OrderController::class, 'print'])->name('order.print');
    Route::get('order-download', [OrderController::class, 'download'])->name('order.download');
    Route::get('order-search', [OrderController::class, 'search'])->name('order.search');
    Route::resource('coupons', CouponController::class);
    Route::resource('customers', CustomerController::class); 
    Route::post('customer-update', [CustomerController::class, 'update'])->name('customer.update');
    Route::resource('notifications', NotificationController::class);
    

    Route::get('attribute/{product_id}', [AttributeController::class,'index'])->name('attribute.add'); 
    Route::post('attribute-add', [AttributeController::class,'store'])->name('attribute.store'); 
    Route::group(['middleware' => ['role:SuperAdmin']], function () {
        Route::get('settings', [SettingController::class,'index'])->name('setting.edit'); 
        Route::post('settings', [SettingController::class,'update'])->name('setting.update');
    });
    
    
    Route::group(['middleware' => ['permission:show payment|edit payment|create payment|delete payment']],function(){
        Route::resource('payments', PaymentController::class); 
        Route::post('payments/approve', [PaymentController::class,'approve'])->name('payment.approve');
    });

    Route::group(['middleware' => ['permission:show product|edit product|create product|delete product']],function(){
        Route::resource('products', ProductController::class);
        Route::post('product-update', [ProductController::class,'productUpdate'])->name('product.update');
        Route::get('product-download', [ProductController::class,'productDownload'])->name('products.download');
    

        Route::get('gallery-add/{id}',[ ProductController::class , 'galleryAdd'])->name('gallery.add');
        Route::post('gallery-store',[ ProductController::class , 'galleryStore'])->name('gallery.store');
        Route::post('gallery-update',[ ProductController::class , 'galleryUpdate'])->name('gallery.update');
        Route::get('gallery-delete/{id}',[ ProductController::class , 'galleryDelete'])->name('gallery.detele');

        ///variant product

        Route::post('update-variant',[ ProductController::class , 'variantUpdate'])->name('variant.update');
        Route::get('variant/{id}',[ ProductController::class , 'varitantForm'])->name('variant.add');
        Route::get('variant-delete/{id}',[ ProductController::class , 'varitantDelete'])->name('variant.delete');
        Route::get('products-bulk-update-form',[ ProductController::class , 'bulkUpdateForm'])->name('products.bulk.update.form');
        Route::post('products-bulk-update',[ ProductController::class , 'bulkUpdate'])->name('products.bulk.update');
       
    });

    

    // Route::post('attribute-update', [AttributeController::class,'update'])->name('attribute.update'); 
    // Route::resource('attributes', AttributeController::class); 
});




require __DIR__.'/auth.php';
