<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ShippingYearExport implements WithMultipleSheets
{
    use Exportable;

    protected $period_tw;

    public function __construct($period_tw)
    {
        $this->period_tw = $period_tw;
    }

    public function sheets(): array
    {
        $sheets[] = new ShippingYearSheet($this->period_tw, 'first');
        $sheets[] = new ShippingYearSheet($this->period_tw, 'last');
        return $sheets;
    }

}
