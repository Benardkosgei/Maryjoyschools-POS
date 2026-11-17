<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class StudentPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeder.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            [
                'name' => 'Students_view',
                'label' => 'View Students',
                'description' => 'View student records and information',
            ],
            [
                'name' => 'Students_add',
                'label' => 'Add Students',
                'description' => 'Create new student records',
            ],
            [
                'name' => 'Students_edit',
                'label' => 'Edit Students',
                'description' => 'Update student information',
            ],
            [
                'name' => 'Students_delete',
                'label' => 'Delete Students',
                'description' => 'Remove student records',
            ],
            [
                'name' => 'Students_account_management',
                'label' => 'Manage Student Accounts',
                'description' => 'Manage student account balances and credit limits',
            ],
            [
                'name' => 'Students_reports',
                'label' => 'View Student Reports',
                'description' => 'Access student statistics and reports',
            ],
            [
                'name' => 'Students_export',
                'label' => 'Export Student Data',
                'description' => 'Export student information to CSV/Excel',
            ],
            [
                'name' => 'Students_import',
                'label' => 'Import Student Data',
                'description' => 'Import student information from CSV/Excel',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }
}