<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create([
            'name' => '系統管理員',
            'permission_code' => 'A',
        ]);

        Role::create([
            'name' => '員工',
            'permission_code' => 'W',
        ]);

        Role::create([
            'name' => '課長',
            'permission_code' => 'S',
        ]);

        Role::create([
            'name' => '經副理',
            'permission_code' => 'M',
        ]);

        Role::create([
            'name' => '執行董事',
            'permission_code' => 'D',
        ]);

        Role::create([
            'name' => '無權限',
            'permission_code' => 'N',
        ]);
    }
}
