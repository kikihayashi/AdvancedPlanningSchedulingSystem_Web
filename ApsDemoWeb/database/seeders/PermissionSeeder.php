<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::create([
            'code' => 'A',
            'remark' => '系統管理員(Administrator)',
            'worker_operation' => 'Y',
            'supervisor_operation' => 'Y',
            'manager_operation' => 'Y',
            'director_operation' => 'Y',
            'project_crud' => 'Y',
            'identity_crud' => 'Y',
            'basic_crud' => 'Y',
            'maintain_crud' => 'Y',
            'period_delete' => 'Y',
        ]);

        Permission::create([
            'code' => 'W',
            'remark' => '員工(Worker)',
            'worker_operation' => 'Y',
            'supervisor_operation' => 'N',
            'manager_operation' => 'N',
            'director_operation' => 'N',
            'project_crud' => 'Y',
            'identity_crud' => 'N',
            'basic_crud' => 'Y',
            'maintain_crud' => 'Y',
            'period_delete' => 'N',
        ]);

        Permission::create([
            'code' => 'S',
            'remark' => '課長(Supervisor)',
            'worker_operation' => 'N',
            'supervisor_operation' => 'Y',
            'manager_operation' => 'N',
            'director_operation' => 'N',
            'project_crud' => 'N',
            'identity_crud' => 'N',
            'basic_crud' => 'Y',
            'maintain_crud' => 'Y',
            'period_delete' => 'N',
        ]);

        Permission::create([
            'code' => 'M',
            'remark' => '經副理(Manager)',
            'worker_operation' => 'N',
            'supervisor_operation' => 'N',
            'manager_operation' => 'Y',
            'director_operation' => 'N',
            'project_crud' => 'N',
            'identity_crud' => 'N',
            'basic_crud' => 'Y',
            'maintain_crud' => 'Y',
            'period_delete' => 'Y',
        ]);

        Permission::create([
            'code' => 'D',
            'remark' => '執行董事(Director)',
            'worker_operation' => 'N',
            'supervisor_operation' => 'N',
            'manager_operation' => 'N',
            'director_operation' => 'Y',
            'project_crud' => 'N',
            'identity_crud' => 'N',
            'basic_crud' => 'Y',
            'maintain_crud' => 'Y',
            'period_delete' => 'N',
        ]);

        Permission::create([
            'code' => 'N',
            'remark' => '無權限(None)',
            'worker_operation' => 'N',
            'supervisor_operation' => 'N',
            'manager_operation' => 'N',
            'director_operation' => 'N',
            'project_crud' => 'N',
            'identity_crud' => 'N',
            'basic_crud' => 'N',
            'maintain_crud' => 'N',
            'period_delete' => 'N',
        ]);
    }
}
