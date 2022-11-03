<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ParameterSettingRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */

    public $id = 0;

    public function __construct($input_id)
    {
        //
        $this->id = $input_id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //此為ISO編號編號以外的參數
        if ($this->id < 11 || $this->id > 15) {
            //判斷是否為正數
            return (bool) preg_match('/^\d+(\.(\d))?$/', $value);
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '錯誤，此欄位只許輸入正數！';
    }
}
