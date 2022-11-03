<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    public $table = "parameter_setting"; //等於sql server Table名稱

    protected $cast = [
        'toppings' => 'array',
    ];
}
