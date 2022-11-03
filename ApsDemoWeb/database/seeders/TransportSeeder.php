<?php

namespace Database\Seeders;

use App\Models\Transport;
use Illuminate\Database\Seeder;

class TransportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Transport::create([
            'name'=>'海運',
            'abbreviation'=>'S',
            'is_remark'=>'N',
        ]);
        
        Transport::create([
            'name'=>'空運',
            'abbreviation'=>'A',
            'is_remark'=>'N',
        ]);

        Transport::create([
            'name'=>'工程案',
            'abbreviation'=>'TRA',
            'is_remark'=>'N',
        ]);

        Transport::create([
            'name'=>'其他',
            'abbreviation'=>'',
            'is_remark'=>'Y',
        ]);
    }
}
