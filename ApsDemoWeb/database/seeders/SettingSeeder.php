<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        Setting::create([
            'name' => '出貨上旬日',
            'memo' => 'shipping_first',
            'setting_value' => '5',
        ]);

        Setting::create([
            'name' => '出貨中旬日',
            'memo' => 'shipping_middle',
            'setting_value' => '15',
        ]);

        Setting::create([
            'name' => '出貨下旬日',
            'memo' => 'shipping_last',
            'setting_value' => '25',
        ]);

        Setting::create([
            'name' => '工作日時數',
            'memo' => 'working_hours',
            'setting_value' => '8',
        ]);

        Setting::create([
            'name' => '生產週期',
            'memo' => 'production_cycle',
            'setting_value' => '25',
        ]);

        Setting::create([
            'name' => '生產上旬日',
            'memo' => 'production_first',
            'setting_value' => '1',
        ]);

        Setting::create([
            'name' => '生產中旬日',
            'memo' => 'production_middle',
            'setting_value' => '10',
        ]);

        Setting::create([
            'name' => '生產下旬日',
            'memo' => 'production_last',
            'setting_value' => '25',
        ]);

        Setting::create([
            'name' => '預設員工人數',
            'memo' => 'employee_numbers',
            'setting_value' => '95',
        ]);

        Setting::create([
            'name' => '年度生產計劃表 ISO編號',
            'memo' => 'iso_production_year',
            'setting_value' => 'B245…',
        ]);

        Setting::create([
            'name' => '年度出荷計劃表 ISO編號',
            'memo' => 'iso_shipping_year',
            'setting_value' => 'B311…',
        ]);

        Setting::create([
            'name' => '月度生產計劃表 ISO編號',
            'memo' => 'iso_production_month',
            'setting_value' => 'B254…',
        ]);

        Setting::create([
            'name' => '月度出荷計劃表 ISO編號',
            'memo' => 'iso_shipping_month',
            'setting_value' => 'B232…',
        ]);

        Setting::create([
            'name' => '實績表 ISO編號',
            'memo' => 'iso_performance',
            'setting_value' => '實績表 ISO...',
        ]);

        Setting::create([
            'name' => '生產預計日',
            'memo' => 'product_date',
            'setting_value' => '4',
        ]);

        Setting::create([
            'name' => '材料納期預定日',
            'memo' => 'material_date',
            'setting_value' => '12',
        ]);
    }
}
