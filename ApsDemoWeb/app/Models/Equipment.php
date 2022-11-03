<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    public $table = "equipment"; //等於sql server Table名稱

    protected $cast = [
        'toppings' => 'array',
    ];
}
