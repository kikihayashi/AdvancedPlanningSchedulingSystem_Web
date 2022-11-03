<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ProjectLotTotalRule implements Rule
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
        return strlen($value) <= 8;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '錯誤，台數已超過上限！(最多8位數)';
    }
}
