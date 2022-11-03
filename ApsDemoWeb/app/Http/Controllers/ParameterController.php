<?php

namespace App\Http\Controllers;

use App\Http\Traits\BaseTool;
use App\Http\Traits\ValidatorTool;
use App\Models\Calendar;
use App\Models\Transport;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class ParameterController extends Controller
{
    use BaseTool, ValidatorTool;

    /**
     * 參數設定頁面：日曆類型、運送類型、參數設定-----------------------------------------------------
     */
    //讀取
    public function showParameterPage()
    {
        //一次獲取3個小分頁的資料
        $data['title'] = "參數設定";
        $data['calendar'] = Calendar::orderBy('id')->get();
        $data['transport'] = Transport::orderBy('id')->get();
        $data['setting'] = Setting::orderBy('id')->get();
        //操作權限畫面用
        $data['permission'] = $this->getUserPermission();

        return view('system.basicMenu.parameter',
            ['selection' => 'system',
                'openMenu' => 'basicMenu',
                'visitedId' => 'parameter',
                'tableData' => $data]);
    }

    /**
     * 日曆類型分頁
     */
    //建立&更新
    public function writeCalendarType($id = 0)
    {
        //取得表單資料
        $calendarArray = $_POST['calendar'];
        $name = $calendarArray[0];
        $is_holiday = (isset($calendarArray[1])) ? 'Y' : 'N';

        // dd($calendarArray);

        //建立驗證器
        $columnArray['id'] = $id;
        $columnArray['name'] = 'name';
        $columnArray['value'] = $name;
        $validator = $this->checkInputValid('parameter_calendar', $columnArray);
        //如果驗證失敗
        if ($validator->fails()) {
            return redirect()->back()
                ->with('errorMessage', $validator->errors()->all()[0]) //顯示錯誤訊息
                ->with('selectPage', 'calendar'); //用來判斷使用者目前是點選哪個分頁
        }

        $calendar = Calendar::where('id', $id)->first() ?? new Calendar();
        $calendar->name = $name;
        $calendar->is_holiday = $is_holiday;
        $calendar->save();

        $message = ($id == 0) ? '成功，已新增資料！' : '成功，已修改資料！';

        return redirect(route('ParameterController.showParameterPage'))
            ->with('message', $message) //顯示成功訊息
            ->with('selectPage', 'calendar'); //用來判斷使用者目前是點選哪個分頁
    }

    /**
     * 運送類型分頁
     */
    //建立&更新
    public function writeTransportType($id = 0)
    {
        $transportArray = $_POST['transport'];
        $name = $transportArray[0];
        $abbreviation = $transportArray[1];
        $is_remark = (isset($transportArray[2])) ? 'Y' : 'N';

        //建立驗證器
        $columnArray['id'] = $id;
        $columnArray['name'] = 'name';
        $columnArray['value'] = $name;
        $validator = $this->checkInputValid('parameter_transport', $columnArray);
        //如果驗證失敗
        if ($validator->fails()) {
            return redirect()->back()
                ->with('errorMessage', $validator->errors()->all()[0]) //顯示錯誤訊息
                ->with('selectPage', 'transport'); //用來判斷使用者目前是點選哪個分頁
        }

        $transport = Transport::where('id', $id)->first() ?? new Transport();
        $transport->name = $name;
        $transport->abbreviation = $abbreviation;
        $transport->is_remark = $is_remark;
        $transport->save();

        $message = ($id == 0) ? '成功，已新增資料！' : '成功，已修改資料！';

        return redirect(route('ParameterController.showParameterPage'))
            ->with('message', $message) //顯示成功訊息
            ->with('selectPage', 'transport'); //用來判斷使用者目前是點選哪個分頁
    }

    /**
     * 參數設定分頁
     */
    //更新
    public function updateParameterSetting($id)
    {
        //取得表單資料
        $settingArray = $_POST['setting'];
        $systemType = $_POST['systemType'];
        $setting_value = $settingArray[0];

        //建立驗證器
        $columnArray['id'] = $id;
        $columnArray['name'] = 'setting_value';
        $columnArray['value'] = $setting_value;
        $validator = $this->checkInputValid('parameter_setting', $columnArray);
        //如果驗證失敗
        if ($validator->fails()) {
            return redirect()->back()
                ->with('errorMessage', $validator->errors()->all()[0]) //顯示錯誤訊息
                ->with('selectPage', 'setting'); //用來判斷使用者目前是點選哪個分頁
        }

        $setting = Setting::findOrFail($id);
        $setting->setting_value = $setting_value;
        $setting->save();

        switch ($systemType) {
            case 'basic':
                return redirect(route('ParameterController.showParameterPage'))
                    ->with('message', '成功，已修改資料！') //顯示成功訊息
                    ->with('selectPage', 'setting'); //用來判斷使用者目前是點選哪個分頁
            case 'maintain':
                return redirect(route('ContentController.showContentPage'))
                    ->with('message', '成功，已修改資料！'); //顯示成功訊息
        }
    }
}
