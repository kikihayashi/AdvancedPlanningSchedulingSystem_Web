<?php

namespace App\Http\Controllers;

use App\Http\Traits\BaseTool;
use App\Http\Traits\ValidatorTool;
use App\Models\Calendar;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ScheduleController extends Controller
{
    use BaseTool, ValidatorTool;

    /**
     * 行事曆------------------------------------------------------------------------------
     */
    //讀取
    public function showSchedulePage()
    {
        $data['title'] = "行事曆";
        $data['calendar'] = Calendar::orderBy('id')->get();
        $data['schedule'] = Schedule::join('parameter_calendar', 'schedule.calendar_id', '=', DB::raw("CAST(parameter_calendar.id AS VARCHAR)"))
            ->selectRaw('schedule.* , parameter_calendar.name AS name, parameter_calendar.is_holiday AS is_holiday')
            ->get();

        //操作權限畫面用
        $data['permission'] = $this->getUserPermission();

        return view('system.basicMenu.schedule', [
            'selection' => 'system',
            'openMenu' => 'basicMenu',
            'visitedId' => 'schedule',
            'tableData' => $data]);
    }

    //改變行事曆種類
    public function changeScheduleStatus()
    {
        $statusArray = $_POST['status'];
        $calendar_id = $statusArray[0];
        $year = $statusArray[1];
        $month = $statusArray[2];
        $date = $statusArray[3];
        $day = $statusArray[4];

        //建立驗證器
        $columnArray['id'] = $id ?? 0;
        $columnArray['name'] = 'calendar_id';
        $columnArray['value'] = $calendar_id;
        $validator = $this->checkInputValid('schedule', $columnArray);
        //如果驗證失敗
        if ($validator->fails()) {
            return redirect()->back()
                ->with('errorMessage', $validator->errors()->all()[0]); //顯示錯誤訊息
        }

        //檢查此日期是否存在Schedule資料庫
        //如果Schedule資料庫裡沒有這個日期，代表要新增
        //如果Schedule資料庫裡有這個日期，代表要修改
        $schedule = Schedule::where('year', $year)
            ->where('month', $month)
            ->where('date', $date)
            ->where('day', $day)
            ->first() ?? new Schedule();

        $schedule->calendar_id = $calendar_id;
        $schedule->year = $year;
        $schedule->month = $month;
        $schedule->date = $date;
        $schedule->day = $day;
        $schedule->save();

        return redirect(route('ScheduleController.showSchedulePage'))
            ->with('message', '成功，已修改資料！'); //顯示成功訊息
    }
}
