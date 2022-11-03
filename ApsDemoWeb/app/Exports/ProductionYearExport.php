<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductionYearExport implements WithMultipleSheets
{
    use Exportable;

    protected $period_tw;

    public function __construct($period_tw)
    {
        $this->period_tw = $period_tw;
    }

    public function sheets(): array
    {
        $sheets[] = new ProductionYearSheet($this->period_tw, 'first', 1);
        $sheets[] = new ProductionYearSheet($this->period_tw, 'first', 2);
        $sheets[] = new ProductionYearSheet($this->period_tw, 'first', 3);
        $sheets[] = new ProductionYearSheet($this->period_tw, 'last', 1);
        $sheets[] = new ProductionYearSheet($this->period_tw, 'last', 2);
        $sheets[] = new ProductionYearSheet($this->period_tw, 'last', 3);
        return $sheets;
    }
}
