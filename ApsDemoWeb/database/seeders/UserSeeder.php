<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //初始建立系統管理員
        User::create([
            'account' => 'admin',
            'role_id' => Role::where('permission_code', 'A')->first()->id,
            'password' => Hash::make('123456'),
            'name' => '系統管理員',
        ]);
    }
}
