<?php

namespace Database\Seeders;

use App\Models\Calendar;
use Illuminate\Database\Seeder;

class CalendarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Calendar::create([
            'name'=>'工作日',
            'is_holiday'=>'N',
        ]);

        Calendar::create([
            'name'=>'休假日',
            'is_holiday'=>'Y',
        ]);
    }
}
