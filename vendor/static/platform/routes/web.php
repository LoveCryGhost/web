<?php

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

use App\Handlers\ShopeeHandler;
use App\Models\CrawlerItem;
use App\Models\CrawlerTask;
use App\Repositories\Member\MemberCoreRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

//MultiAuth 用户身份验证相关的路由
Auth::routes(['verify' => true]);
Route::get('/admin/horizon', function () {
    return redirect()->route('horizon.index');
});
Route::prefix('')->group(function() {
    Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
    Route::post('login', 'Auth\LoginController@login');
    Route::post('logout', 'Auth\LoginController@logout')->name('logout');

    // 用户注册相关路由
    Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
    Route::post('register', 'Auth\RegisterController@register');

    // 密码重置相关路由
    Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
    Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');

    // Email 认证相关路由
    Route::get('email/verify', 'Auth\VerificationController@show')->name('verification.notice');
    Route::get('email/verify/{id}/{hash}', 'Auth\VerificationController@verify')->name('verification.verify');
    Route::post('email/resend', 'Auth\VerificationController@resend')->name('verification.resend');

    //Admin
    Route::prefix('admin')->group(function() {
        Route::get('/login', 'Auth\AdminLoginController@showLoginForm')->name('admin.login');
        Route::post('/login', 'Auth\AdminLoginController@login')->name('admin.login.submit');
        Route::post('/logout', 'Auth\AdminLoginController@logout')->name('admin.logout');
        //Password Reset Route
        Route::post('/password/email', 'Auth\AdminForgotPasswordController@sendResetLinkEmail')->name('admin.password.email');
        Route::get('/password/reset', 'Auth\AdminForgotPasswordController@showRequestForm')->name('admin.password.request');
        Route::post('/password/reset', 'Auth\AdminResetPasswordController@reset')->name('admin.password.update');
        Route::get('/password/reset/{token}', 'Auth\AdminResetPasswordController@showResetForm')->name('admin.password.reset');

    });

    //Member
    Route::prefix('member')->group(function() {
        Route::get('/login', 'Auth\MemberLoginController@showLoginForm')->name('member.login');
        Route::post('/login', 'Auth\MemberLoginController@login')->name('member.login.submit');
        Route::post('/logout', 'Auth\MemberLoginController@logout')->name('member.logout');

        //Password Reset Route
        Route::post('/password/email', 'Auth\MemberForgotPasswordController@sendResetLinkEmail')->name('member.password.email');
        Route::get('/password/reset', 'Auth\MemberForgotPasswordController@showRequestForm')->name('member.password.request');
        Route::post('/password/reset', 'Auth\MemberResetPasswordController@reset')->name('member.password.update');
        Route::get('/password/reset/{token}', 'Auth\MemberResetPasswordController@showResetForm')->name('member.password.reset');
    });

    //Staff
    Route::prefix('staff')->group(function() {
        Route::get('/login', 'Auth\StaffLoginController@showLoginForm')->name('staff.login');
        Route::post('/login', 'Auth\StaffLoginController@login')->name('staff.login.submit');
        Route::post('/logout', 'Auth\StaffLoginController@logout')->name('staff.logout');

        //Password Reset Route
        Route::post('/password/email', 'Auth\StaffForgotPasswordController@sendResetLinkEmail')->name('staff.password.email');
        Route::get('/password/reset', 'Auth\StaffForgotPasswordController@showRequestForm')->name('staff.password.request');
        Route::post('/password/reset', 'Auth\StaffResetPasswordController@reset')->name('staff.password.update');
        Route::get('/password/reset/{token}', 'Auth\StaffResetPasswordController@showResetForm')->name('staff.password.reset');
    });
});



//User
Route::middleware('auth')->prefix('')->namespace('User')->name('')->group(function(){
    Route::resource('users', 'UsersController', ['only' => ['show', 'update', 'edit']]);
});

//Admin
Route::prefix('')->namespace('Admin')->group(function(){
    Route::prefix('admin')->name('admin.')->group(function(){

        //Guard-Switcher-User
        Route::post('tool/guard_switcher_user', 'AdminToolsController@guard_switcher_user')->name('tool.guard_switcher_user');

        //AdminUser
        Route::put('user_update_password/{user}', 'AdminUsersController@update_password')->name('user.update_password');
        Route::resource('user', 'AdminUsersController');

        //AdminMember
        Route::put('member_update_password/{member}', 'AdminMembersController@update_password')->name('member.update_password');
        Route::resource('member', 'AdminMembersController');

        //AdminMember
        Route::put('staff_update_password/{staff}', 'AdminStaffsController@update_password')->name('staff.update_password');
        Route::resource('staff', 'AdminStaffsController');

    });
    Route::resource('admin', 'AdminsController');
});

//Member
Route::prefix('member')->namespace('Member')->group(function(){
    Route::put('member_update_password/{member}', 'MembersController@update_password')->name('member.update_password');
    Route::resource('member', 'MembersController');
    Route::prefix('')->name('member.')->group(function(){

        //SupplierGroup
        Route::resource('supplier', 'SuppliersController');
        Route::resource('supplierGroup', 'SupplierGroupsController');
        Route::resource('supplier-contact', 'Supplier_ContactsController');


        //Type
        Route::resource('type', 'TypesController');
        Route::resource('type-attribute', 'Types_AttributesController');

        //Attribute
        Route::resource('attribute', 'AttributesController');

        //Product
        Route::resource('product', 'ProductsController');
        Route::resource('product-sku', 'Product_SKUsController');
        Route::resource('product-sku-supplier', 'Product_SKU_SuppliersController');

        //Crawler
        Route::resource('crawlertask', 'CrawlerTasksController');
        Route::post('crawlertask_refresh', 'CrawlerTasksController@refresh')->name('crawler.refresh');
        Route::resource('crawleritem', 'CrawlerItemsController');
        Route::post('crawleritem_toggle', 'CrawlerItemsController@toggle')->name('crawleritem.toggle');
        Route::post('crawleritem_save_cralwertask_info', 'CrawlerItemsController@save_cralwertask_info')->name('crawleritem.save_cralwertask_info');

        Route::resource('crawleritemsku', 'CrawlerItemSKUsController');
    });

});

//Staff
Route::prefix('')->namespace('Staff')->group(function(){
    Route::put('staff_update_password/{staff}', 'StaffsController@update_password')->name('staff.update_password');
    Route::prefix('staff')->name('staff.')->group(function(){
        Route::get('dashboard', 'StaffDashboardsController@dashboard')->name('staff.dashboard');

        Route::get('staff_list', 'StaffsController@list')->name('staff.staff_list');;
        Route::resource('staff', 'StaffsController');
        Route::resource('staff-department', 'Staff_DepartmentsController');
    });
});


Route::get('/', function () {
    return view('theme.cryptoadmin.user.welcome');
});

Route::get('/test', function (){

//    $statement = "select `ci_id` from `crawler_items` where (`itemid`, `shopid`) in (('7315080132', '121790231'), ('1815811714', '2478934'))";
//    dd(DB::select($statement));

    //$url = $this->url;
    $url = "https://shopee.tw/api/v2/search_items/?&by=relevancy&limit=5&newest=0&locations=-1&fe_categoryids=&page_type=search&version=2";
    $this->shopeeHandler = new ShopeeHandler();
    $ClientResponse = $this->shopeeHandler->ClientHeader_Shopee($url);
    $this->crawlerTask = CrawlerTask::find(1);
    $json = json_decode($ClientResponse->getBody(), true);

    $member_id = Auth::guard('member')->check()?  Auth::guard('member')->user()->id: '1';
    foreach ($json['items'] as $item){
        $row_items[] = [
            'itemid' => $item['itemid'],
            'shopid' => $item['shopid'],
            //'name' => $item['name'], 不完整
            'images' => $item['image'],
            'sold' => $item['sold']!==null? $item['sold']: 0,
            'historical_sold' => $item['historical_sold'],
            'domain_name' => $this->crawlerTask->domain_name,
            'local' => $this->crawlerTask->local,
            'member_id' => $member_id,
        ];

        $row_shops[] =  [
            'shopid' => $item['shopid'],
            'shop_location' => "",
            'local' => $this->crawlerTask->local,
            'member_id' => $member_id
        ];

        $value_arr[] = [ $item['itemid'],  $item['shopid'], $this->crawlerTask->local];
    };

    //批量儲存Item
    $crawlerItem = new CrawlerItem();
    $TF = (new MemberCoreRepository())->massUpdate($crawlerItem, $row_items);
    dd(12);
    //CrawlerTasks sync Items
    //$crawlerItem_ids = CrawlerItem::whereNull('created_at')->pluck('ci_id');
    $crawlerItem_ids = CrawlerItem::whereInMultiple(['itemid','shopid','local'], $value_arr)
        ->pluck('ci_id');

    $this->crawlerTask->crawlerItems()->syncwithoutdetaching($crawlerItem_ids);
});
include('route_test.php');








