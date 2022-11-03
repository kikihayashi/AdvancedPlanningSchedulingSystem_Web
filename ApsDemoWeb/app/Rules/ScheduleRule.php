<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ScheduleRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        return (int) $value > 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        //如果使用者沒選日曆種類就送出，$value = 0
        return '錯誤，未選取日曆種類！';
    }
}
