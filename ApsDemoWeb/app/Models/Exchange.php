<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exchange extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv_MES';

    public $table = "MDProdPrice"; //等於sql server Table名稱

    protected $cast = [
        'toppings' => 'array',
    ];
}
