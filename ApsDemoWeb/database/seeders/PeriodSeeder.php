<?php

namespace Database\Seeders;

use App\Models\Period;
use Illuminate\Database\Seeder;

class PeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $year = intval(date("Y"));
        Period::create([
            'period_tw' => $year - 1969,
            'years' => $year,
            'period_jp' => $year - 1969 + 105,
            'start_date' => 1,
        ]);

    }
}
