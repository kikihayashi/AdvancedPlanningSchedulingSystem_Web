<?php
namespace App\Http\Traits;

use App\Rules\EquipmentRule;
use App\Rules\ProjectLotNoRule;
use App\Rules\ProjectLotTotalRule;
use App\Rules\ParameterSettingRule;
use App\Rules\PeriodRule;
use App\Rules\ScheduleRule;
use Illuminate\Support\Facades\Http;
use Validator;

trait ValidatorTool
{
    //驗證輸入值是否正確(Table名稱、欄位資料)
    protected function checkInputValid($tableName, $columnArray)
    {
        //取得id、欄位名稱、欄位值
        $id = $columnArray['id'] ?? 0; //id是用來處理unique更新問題
        $columnName = $columnArray['name']; //欄位名稱
        $columnValue = $columnArray['value']; //欄位值

        //驗證格式設定
        $inputs = [
            $columnName => $columnValue,
        ];

        //設計共用的錯誤訊息，個別的錯誤訊息放在自定義的Rule裡
        $messages = [
            $columnName . '.required' => '錯誤，資料不可為空白！',
            $columnName . '.unique' => '錯誤，資料已存在！',
        ];

        switch ($tableName) {
            case 'users':
                $columnName = $columnArray['name'];
                $columnValue = $columnArray['value'];
                $columnName2 = $columnArray['name2'];
                $columnValue2 = $columnArray['value2'];
                $columnName3 = $columnArray['name3'];
                $columnValue3 = $columnArray['value3'];
                $columnName4 = $columnArray['name4'];
                $columnValue4 = $columnArray['value4'];

                $inputs = [
                    $columnName => $columnValue,
                    $columnName2 => $columnValue2,
                    $columnName3 => $columnValue3,
                    $columnName4 => $columnValue4,
                ];

                $rules = [
                    $columnName => ['required', 'string', 'max:255'],
                    $columnName2 => ['required', 'string', 'max:255', 'unique:' . $tableName . ',' . $columnName2 . ',' . $id],
                    $columnName3 => ['required', 'string', 'min:4', 'confirmed'],
                ];

                $messages = [
                    $columnName . '.required' => '錯誤，名稱不可為空白！',
                    $columnName2 . '.required' => '錯誤，帳號不可為空白！',
                    $columnName2 . '.unique' => '錯誤，此帳號名稱已被使用！',
                    $columnName3 . '.confirmed' => '錯誤，再次輸入的密碼不相同！',
                ];
                break;

            case 'roles':
                $columnName = $columnArray['name'];
                $columnValue = $columnArray['value'];
                $columnName2 = $columnArray['name2'];
                $columnValue2 = $columnArray['value2'];

                $inputs = [
                    $columnName => $columnValue,
                    $columnName2 => $columnValue2,
                ];

                $rules = [
                    $columnName => ['required', 'string', 'max:255', 'unique:' . $tableName . ',' . $columnName . ',' . $id],
                    $columnName2 => ['required', 'string', 'max:255'],
                ];

                $messages = [
                    $columnName . '.required' => '錯誤，名稱不可為空白！',
                    $columnName2 . '.required' => '錯誤，權限代號不可為空白！',
                    $columnName . '.unique' => '錯誤，此名稱已存在！',
                ];
                break;

            case 'permission':
                $columnName = $columnArray['name'];
                $columnValue = $columnArray['value'];

                $inputs = [
                    $columnName => $columnValue,
                ];

                $rules = [
                    $columnName => ['required', 'string', 'max:255', 'unique:' . $tableName . ',' . $columnName],
                ];

                $messages = [
                    $columnName . '.required' => '錯誤，名稱不可為空白！',
                    $columnName . '.unique' => '錯誤，此名稱已存在！',
                ];
                break;

            case 'parameter_setting':
                $rules = [
                    $columnName => ['required', 'min:1', new ParameterSettingRule($id)],
                    //舊方法：$columnName => 'required|min:1|regex:/^\d+(\.(\d))?$/'
                    //還要加if判斷式，確認是否為ISO編號編號以外的參數。
                ];
                break;
            case 'parameter_calendar':
            case 'parameter_transport':
                $rules = [
                    $columnName => 'required|min:1|unique:' . $tableName . ',' . $columnName . ',' . $id,
                ];
                break;

            case 'equipment':
                $rules = [
                    $columnName => ['required', 'min:1', new EquipmentRule],
                ];
                break;

            case 'period':
                $rules = [
                    $columnName => ['required', 'min:1', 'unique:' . $tableName . ',' . $columnName . ',' . $id, new PeriodRule],
                ];
                break;

            case 'schedule':
                $rules = [
                    $columnName => ['required', 'min:1', new ScheduleRule],
                ];
                break;

            case 'management':
                $columnName2 = $columnArray['name2'];
                $columnValue2 = $columnArray['value2'];
                $columnName3 = $columnArray['name3'];
                $columnValue3 = $columnArray['value3'];
                $columnName4 = $columnArray['name4'];
                $columnValue4 = $columnArray['value4'];
                $columnName5 = $columnArray['name5'];
                $columnValue5 = $columnArray['value5'];

                $inputs = [
                    $columnName => $columnValue,
                    $columnName2 => $columnValue2,
                    $columnName5 => $columnValue5,
                ];

                $rules = [
                    $columnName => ['unique:' . $tableName . ',' .
                        $columnName . ',' . $id . ',id,' .
                        $columnName2 . ',' . $columnValue2 . ',' .
                        $columnName3 . ',' . $columnValue3 . ',' .
                        $columnName4 . ',' . $columnValue4],
                    $columnName2 => ['required', new ProjectLotNoRule],
                    $columnName5 => ['required', new ProjectLotTotalRule],
                ];

                $messages = [
                    $columnName . '.unique' => '錯誤！機種【' . $columnValue . '】對應製番【#' . $columnValue2 . '】已存在。',
                ];
                break;

            case 'production_year':
                $columnName2 = $columnArray['name2'];
                $columnValue2 = $columnArray['value2'];
                $columnName3 = $columnArray['name3'];
                $columnValue3 = $columnArray['value3'];
                $columnName4 = $columnArray['name4'];
                $columnValue4 = $columnArray['value4'];
                $columnName5 = $columnArray['name5'];
                $columnValue5 = $columnArray['value5'];

                $inputs = [
                    $columnName => $columnValue,
                    $columnName2 => $columnValue2,
                    $columnName5 => $columnValue5,
                ];

                $rules = [
                    $columnName => 'unique:' . $tableName . ',' .
                    $columnName . ',' . $id . ',id,' .
                    $columnName2 . ',' . $columnValue2 . ',' .
                    $columnName3 . ',' . $columnValue3 . ',' .
                    $columnName4 . ',' . $columnValue4,
                    $columnName2 => ['required', new ProjectLotNoRule],
                    $columnName5 => ['required', new ProjectLotTotalRule],
                ];
                //column1、column2...組合不可重複，用法可參考以下：
                //https://stackoverflow.com/questions/50349775/laravel-unique-validation-on-multiple-columns

                $messages = [
                    $columnName . '.unique' => '錯誤！機種【' . $columnValue . '】對應製番【#' . $columnValue2 . '】已存在。',
                ];
                break;

            case 'shipping_year':
                $columnName2 = $columnArray['name2'];
                $columnValue2 = $columnArray['value2'];
                $columnName3 = $columnArray['name3'];
                $columnValue3 = $columnArray['value3'];
                $columnName4 = $columnArray['name4'];
                $columnValue4 = $columnArray['value4'];
                $columnName5 = $columnArray['name5'];
                $columnValue5 = $columnArray['value5'];
                $columnName6 = $columnArray['name6'];
                $columnValue6 = $columnArray['value6'];
                $columnName7 = $columnArray['name7'];
                $columnValue7 = $columnArray['value7'];

                $inputs = [
                    $columnName => $columnValue,
                    $columnName2 => $columnValue2,
                    $columnName7 => $columnValue7,
                ];

                $rules = [
                    $columnName => 'unique:' . $tableName . ',' .
                    $columnName . ',' . $id . ',id,' .
                    $columnName2 . ',' . $columnValue2 . ',' .
                    $columnName3 . ',' . $columnValue3 . ',' .
                    $columnName4 . ',' . $columnValue4 . ',' .
                    $columnName5 . ',' . $columnValue5,
                    $columnName2 => ['required', new ProjectLotNoRule],
                    $columnName7 => ['required', new ProjectLotTotalRule],
                ];

                $messages = [
                    $columnName . '.unique' => '錯誤！機種【' . $columnValue . '】#' . $columnValue2 . ' ' . $columnValue6 . ' 已存在。',
                ];
                break;

            case 'production_month':
                $columnName2 = $columnArray['name2'];
                $columnValue2 = $columnArray['value2'];
                $columnName3 = $columnArray['name3'];
                $columnValue3 = $columnArray['value3'];
                $columnName4 = $columnArray['name4'];
                $columnValue4 = $columnArray['value4'];
                $columnName5 = $columnArray['name5'];
                $columnValue5 = $columnArray['value5'];
                $columnName6 = $columnArray['name6'];
                $columnValue6 = $columnArray['value6'];
                $columnName7 = $columnArray['name7'];
                $columnValue7 = $columnArray['value7'];

                $inputs = [
                    $columnName => $columnValue,
                    $columnName2 => $columnValue2,
                    $columnName6 => $columnValue6,
                    $columnName7 => $columnValue7,
                ];

                $rules = [
                    $columnName => 'unique:' . $tableName . ',' .
                    $columnName . ',' . $id . ',id,' .
                    $columnName2 . ',' . $columnValue2 . ',' .
                    $columnName3 . ',' . $columnValue3 . ',' .
                    $columnName4 . ',' . $columnValue4 . ',' .
                    $columnName5 . ',' . $columnValue5,
                    $columnName2 => ['required', new ProjectLotNoRule],
                    $columnName6 => ['required', new ProjectLotTotalRule],
                    $columnName7 => ['required', new ProjectLotTotalRule],
                ];

                $messages = [
                    $columnName . '.unique' => '錯誤！機種【' . $columnValue . '】對應製番【#' . $columnValue2 . '】已存在。',
                ];
                break;

            case 'shipping_month':
                $columnName2 = $columnArray['name2'];
                $columnValue2 = $columnArray['value2'];
                $columnName3 = $columnArray['name3'];
                $columnValue3 = $columnArray['value3'];
                $columnName4 = $columnArray['name4'];
                $columnValue4 = $columnArray['value4'];
                $columnName5 = $columnArray['name5'];
                $columnValue5 = $columnArray['value5'];
                $columnName6 = $columnArray['name6'];
                $columnValue6 = $columnArray['value6'];
                $columnName7 = $columnArray['name7'];
                $columnValue7 = $columnArray['value7'];
                $columnName8 = $columnArray['name8'];
                $columnValue8 = $columnArray['value8'];
                $columnName9 = $columnArray['name9'];
                $columnValue9 = $columnArray['value9'];

                $inputs = [
                    $columnName => $columnValue,
                    $columnName2 => $columnValue2,
                    $columnName9 => $columnValue9,
                ];

                $rules = [
                    $columnName => 'unique:' . $tableName . ',' .
                    $columnName . ',' . $id . ',id,' .
                    $columnName2 . ',' . $columnValue2 . ',' .
                    $columnName3 . ',' . $columnValue3 . ',' .
                    $columnName4 . ',' . $columnValue4 . ',' .
                    $columnName5 . ',' . $columnValue5 . ',' .
                    $columnName6 . ',' . $columnValue6,
                    $columnName7 . ',' . $columnValue7,
                    $columnName2 => ['required', new ProjectLotNoRule],
                    $columnName9 => ['required', new ProjectLotTotalRule],
                ];

                $messages = [
                    $columnName . '.unique' => '錯誤！機種【' . $columnValue . '】Lot No：' . $columnValue2 . ' 已存在於 ' . $columnValue8,
                ];
                break;

            case 'file':
                $columnName2 = $columnArray['name2'];
                $columnValue2 = $columnArray['value2'];

                $inputs = [
                    $columnName => $columnValue,
                    $columnName2 => $columnValue2,
                ];
                $rules = [
                    $columnName => 'required',
                    $columnName2 => 'required|in:xlsx,xls',
                ];
                $messages = [
                    $columnName2 . '.in' => '錯誤，檔案只能是Excel格式！',
                ];
                break;
        }
        //回傳驗證器
        return Validator::make($inputs, $rules, $messages);
    }
}
