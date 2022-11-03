<?php

use Illuminate\Support\Facades\Route;

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

Auth::routes();

//註冊頁面
Route::get('/register', function () {return view('auth.register', ['type' => 'HOME']);})->name('registerUser');

//新增使用者
Route::post('/control/identityMenu/createUser/{type}', 'UserController@createUser')->name('UserController.createUser');

//必須要先登入才能使用
Route::group(['middleware' => 'auth'], function () {

    /**
     * ----------------------------------------------首頁-------------------------------------------------------------------------------------------------------------------------------
     */
    Route::get('/', function () {
        $data['title'] = '首頁';
        return view('home', [
            'selection' => 'home',
            'tableData' => $data]);
    })->name('home');

    /**
     * -----------------------------------------系統管理員管理-------------------------------------------------------------------------------------------------------------------------------
     */

    /**
     * 【身分識別管理頁面】-------------------------------------------------------------------------------------------------------------------------------
     */

    /**
     * 使用者
     */
    //讀取
    Route::get('/control/identityMenu/user', 'UserController@showUserPage')->name('UserController.showUserPage');
    //修改
    Route::put('/control/identityMenu/updateUser/{id}/{type}', 'UserController@updateUser')->name('UserController.updateUser');
    //刪除
    Route::delete('/control/identityMenu/deleteUser/{id}', 'UserController@deleteUser')->name('UserController.deleteUser');
    //編輯使用者頁面
    Route::get('/control/identityMenu/editUserPage/{id}', 'UserController@editUserPage')->name('UserController.editUserPage');
    //新增使用者頁面(=註冊頁面)
    Route::get('/control/identityMenu/addUserPage', 'UserController@addUserPage')->name('UserController.addUserPage');

    /**
     * 角色
     */
    //讀取
    Route::get('/control/identityMenu/role', 'RoleController@showRolePage')->name('RoleController.showRolePage');
    //新增&修改
    Route::post('/control/identityMenu/writeRole/{id?}', 'RoleController@writeRole')->name('RoleController.writeRole');
    //刪除
    Route::delete('/control/identityMenu/deleteRole/{id}', 'RoleController@deleteRole')->name('RoleController.deleteRole');

    /**
     * 權限
     */
    //讀取
    Route::get('/control/identityMenu/permission', 'PermissionController@showPermissionPage')->name('PermissionController.showPermissionPage');
    //新增
    Route::post('/control/identityMenu/createPermission', 'PermissionController@createPermission')->name('PermissionController.createPermission');
    //修改
    Route::put('/control/identityMenu/updatePermission/{id?}', 'PermissionController@updatePermission')->name('PermissionController.updatePermission');
    //刪除
    Route::delete('/control/identityMenu/deletePermission/{code}', 'PermissionController@deletePermission')->name('PermissionController.deletePermission');

    /**
     * --------------------------------------大計劃管理系統----------------------------------------------------------------------------------------------
     */

    /**
     * 【基本資料維護頁面】-------------------------------------------------------------------------------------------------------------------------------
     */

    /**
     * 參數設定(日曆類型、運送類型、參數設定)
     */
    //讀取
    Route::get('/system/basicMenu/parameter/', 'ParameterController@showParameterPage')->name('ParameterController.showParameterPage');
    //日曆類型-新增&修改
    Route::post('/system/basicMenu/writeCalendarType/{id?}', 'ParameterController@writeCalendarType')->name('ParameterController.writeCalendarType');
    //運輸類型-新增&修改
    Route::post('/system/basicMenu/writeTransportType/{id?}', 'ParameterController@writeTransportType')->name('ParameterController.writeTransportType');
    //參數設定-修改
    Route::put('/system/basicMenu/updateParameterSetting/{id}', 'ParameterController@updateParameterSetting')->name('ParameterController.updateParameterSetting');

    /**
     * 機種清單
     */
    //讀取
    Route::get('/system/basicMenu/equipment/', 'EquipmentController@showEquipmentPage')->name('EquipmentController.showEquipmentPage');
    //修改
    Route::post('/system/basicMenu/updateEquipment', 'EquipmentController@updateEquipment')->name('EquipmentController.updateEquipment');

    /**
     * 期別與仕切維護
     */
    //讀取
    Route::get('/system/basicMenu/period/', 'PeriodController@showPeriodPage')->name('PeriodController.showPeriodPage');
    //新增&修改
    Route::post('/system/basicMenu/writePeriod/{id?}', 'PeriodController@writePeriod')->name('PeriodController.writePeriod');
    //刪除
    Route::delete('/system/basicMenu/deletePeriod/{id}', 'PeriodController@deletePeriod')->name('PeriodController.deletePeriod');

    //仕切表-讀取
    Route::get('/system/basicMenu/period/{period_tw}/partition', 'PeriodController@showPartitionPage')->name('PeriodController.showPartitionPage');
    //Ajax提取資料
    Route::post('/system/basicMenu/fetchPartition', 'PeriodController@ajaxFetchPartition')->name('PeriodController.ajaxFetchPartition');
    //匯率-讀取
    Route::get('/system/basicMenu/period/{period_tw}/exchange', 'PeriodController@showExchangePage')->name('PeriodController.showExchangePage');

    /**
     * 行事曆
     */
    //讀取
    Route::get('/system/basicMenu/schedule/', 'ScheduleController@showSchedulePage')->name('ScheduleController.showSchedulePage');
    //改變行事曆種類(工作日 or 休假日)
    Route::post('/system/basicMenu/changeScheduleStatus/', 'ScheduleController@changeScheduleStatus')->name('ScheduleController.changeScheduleStatus');

    /**
     * 【大計劃資料維護頁面】-------------------------------------------------------------------------------------------------------------------------------
     */

    /**
     * 共用Method
     */
    //審核計畫表
    Route::post('/system/projectMenu/reviewProject/{projectType}/{period_tw}/{month}/{operation}', 'ProjectController@reviewProject')->name('ProjectController.reviewProject');
    //產生計畫表
    Route::post('/system/projectMenu/createProject/{projectType}/{period_tw}/{month}/{version}', 'ProjectController@createProject')->name('ProjectController.createProject');
    //匯出Excel
    Route::post('/system/projectMenu/exportProject/{projectType}/{period_tw}/{month}', 'ProjectController@exportProject')->name('ProjectController.exportProject');

    /**
     * 大計劃規劃管理
     */
    //讀取
    Route::get('/system/projectMenu/management/{period_tw?}/{selectTab?}', 'ManagementController@showManagementPage')->name('ManagementController.showManagementPage');
    //新增
    Route::post('/system/projectMenu/createManagement/', 'ManagementController@createManagement')->name('ManagementController.createManagement');
    //修改頁面
    Route::get('/system/projectMenu/editManagement/{id}', 'ManagementController@editManagementPage')->name('ManagementController.editManagementPage');
    //修改
    Route::put('/system/projectMenu/updateManagement/{id}/{type}', 'ManagementController@updateManagement')->name('ManagementController.updateManagement');
    //刪除
    Route::delete('/system/projectMenu/deleteManagement/{id}', 'ManagementController@deleteManagement')->name('ManagementController.deleteManagement');

    /**
     * 年度生產計劃
     */
    //讀取
    Route::get('/system/projectMenu/productionYear/{period_tw?}/{selectTab?}', 'ProductionYearController@showProductionYearPage')->name('ProductionYearController.showProductionYearPage');
    //新增
    Route::post('/system/projectMenu/createProductionYear/', 'ProductionYearController@createProductionYear')->name('ProductionYearController.createProductionYear');
    //修改頁面
    Route::get('/system/projectMenu/editProductionYear/{id}', 'ProductionYearController@editProductionYearPage')->name('ProductionYearController.editProductionYearPage');
    //修改
    Route::put('/system/projectMenu/updateProductionYear/{id}', 'ProductionYearController@updateProductionYear')->name('ProductionYearController.updateProductionYear');
    //刪除
    Route::delete('/system/projectMenu/deleteProductionYear/{id}', 'ProductionYearController@deleteProductionYear')->name('ProductionYearController.deleteProductionYear');
    //轉入SAP
    Route::post('/system/projectMenu/uploadProductionYear/{period_tw}', 'ProductionYearController@uploadProductionYear')->name('ProductionYearController.uploadProductionYear');

    /**
     * 年度出荷計劃
     */
    //讀取
    Route::get('/system/projectMenu/shippingYear/{period_tw?}/{selectTab?}', 'ShippingYearController@showShippingYearPage')->name('ShippingYearController.showShippingYearPage');
    //新增
    Route::post('/system/projectMenu/createShippingYear/', 'ShippingYearController@createShippingYear')->name('ShippingYearController.createShippingYear');
    //修改頁面
    Route::get('/system/projectMenu/editShippingYear/{itemCode}/{period_tw}/{version}', 'ShippingYearController@editShippingYearPage')->name('ShippingYearController.editShippingYearPage');
    //修改
    Route::put('/system/projectMenu/updateShippingYear/{type}', 'ShippingYearController@updateShippingYear')->name('ShippingYearController.updateShippingYear');
    //刪除
    Route::delete('/system/projectMenu/deleteShippingYear/{type}', 'ShippingYearController@deleteShippingYear')->name('ShippingYearController.deleteShippingYear');

    /**
     * 月度生產計劃
     */
    //讀取
    Route::get('/system/projectMenu/productionMonth/{period_tw?}/{month?}/{selectTab?}', 'ProductionMonthController@showProductionMonthPage')->name('ProductionMonthController.showProductionMonthPage');
    //新增
    Route::post('/system/projectMenu/createProductionMonth/', 'ProductionMonthController@createProductionMonth')->name('ProductionMonthController.createProductionMonth');
    //修改頁面
    Route::get('/system/projectMenu/editProductionMonth/{id}', 'ProductionMonthController@editProductionMonthPage')->name('ProductionMonthController.editProductionMonthPage');
    //修改
    Route::put('/system/projectMenu/updateProductionMonth/{id}', 'ProductionMonthController@updateProductionMonth')->name('ProductionMonthController.updateProductionMonth');
    //刪除
    Route::delete('/system/projectMenu/deleteProductionMonth/{id}', 'ProductionMonthController@deleteProductionMonth')->name('ProductionMonthController.deleteProductionMonth');
    //轉入SAP
    Route::post('/system/projectMenu/uploadProductionMonth/{period_tw}/{month}', 'ProductionMonthController@uploadProductionMonth')->name('ProductionMonthController.uploadProductionMonth');

    /**
     * 月度出荷計劃
     */
    //讀取
    Route::get('/system/projectMenu/shippingMonth/{period_tw?}/{month?}/{selectTab?}', 'ShippingMonthController@showShippingMonthPage')->name('ShippingMonthController.showShippingMonthPage');
    //新增
    Route::post('/system/projectMenu/createShippingMonth/{type}', 'ShippingMonthController@createShippingMonth')->name('ShippingMonthController.createShippingMonth');
    //修改頁面
    Route::get('/system/projectMenu/editShippingMonth/{itemCode}/{period_tw}/{month}/{version}', 'ShippingMonthController@editShippingMonthPage')->name('ShippingMonthController.editShippingMonthPage');
    //修改
    Route::put('/system/projectMenu/updateShippingMonth/', 'ShippingMonthController@updateShippingMonth')->name('ShippingMonthController.updateShippingMonth');
    //刪除
    Route::delete('/system/projectMenu/deleteShippingMonth/{type}', 'ShippingMonthController@deleteShippingMonth')->name('ShippingMonthController.deleteShippingMonth');

    /**
     * 【資料維護頁面】-------------------------------------------------------------------------------------------------------------------------------
     */

    /**
     * 檔案管理
     */
    //讀取
    Route::get('/system/maintainMenu/file', 'FileController@showFilePage')->name('FileController.showFilePage');
    //上傳
    Route::post('/system/maintainMenu/uploadFile', 'FileController@uploadFile')->name('FileController.uploadFile');
    //下載
    Route::post('/system/maintainMenu/downloadFile/{fileFullName}', 'FileController@downloadFile')->name('FileController.downloadFile');
    //刪除
    Route::delete('/system/maintainMenu/deleteFile/{fileFullName}', 'FileController@deleteFile')->name('FileController.deleteFile');

    /**
     * 變更紀錄管理
     */
    //讀取
    Route::get('/system/maintainMenu/record', 'RecordController@showRecordPage')->name('RecordController.showRecordPage');
    //Ajax提取特定計畫的所有版本
    Route::post('/system/maintainMenu/ajaxFetchVersion', 'RecordController@ajaxFetchVersion')->name('RecordController.ajaxFetchVersion');
    //Ajax提取特定計畫的所有版本
    Route::post('/system/maintainMenu/ajaxFetchRecord', 'RecordController@ajaxFetchRecord')->name('RecordController.ajaxFetchRecord');

    /**
     * 報表內容管理
     */
    //讀取
    Route::get('/system/maintainMenu/content', 'ContentController@showContentPage')->name('ContentController.showContentPage');
    //新增&修改
    Route::post('/system/maintainMenu/writeContent/', 'ContentController@writeContent')->name('ContentController.writeContent');

});

// Route::get('/system/basicMenu/equipment/fetch_data', 'EquipmentController@fetch_data')->name('EquipmentController.fetch_data');
// Route::post('/system/basicMenu/equipment/fetch_data', 'EquipmentController@fetch_data')->name('EquipmentController.fetch_data');
// Route::get('/test', function () {
//     return response()->streamDownload(function () {
//         echo file_get_contents('http://127.0.0.1:8088/api/items/uploadToSap');
//     }, 'example.xlsx');
// });
