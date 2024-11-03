<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        $permissions=[

            [
                'name' 			=> 'Retrieve Users',
                'guard_name' 	=> 'admin',
                'created_at' 	=> now(),
                'updated_at' 	=> now(),
            ],
            [
                'name' 			=> 'Create Users',
                'guard_name' 	=> 'admin',
                'created_at' 	=> now(),
                'updated_at' 	=> now(),
            ],
            [
                'name' 			=> 'Update Users',
                'guard_name' 	=> 'admin',
                'created_at' 	=> now(),
                'updated_at' 	=> now(),
            ],
            [
                'name' 			=> 'Delete Users',
                'guard_name' 	=> 'admin',
                'created_at' 	=> now(),
                'updated_at' 	=> now(),
            ],
            [
                'name' 			=> 'Retrieve Roles',
                'guard_name' 	=> 'admin',
                'created_at' 	=> now(),
                'updated_at' 	=> now(),
            ],
            [
                'name' 			=> 'Create Roles',
                'guard_name' 	=> 'admin',
                'created_at' 	=> now(),
                'updated_at' 	=> now(),
            ],
            [
                'name' 			=> 'Update Roles',
                'guard_name' 	=> 'admin',
                'created_at' 	=> now(),
                'updated_at' 	=> now(),
            ],
            [
                'name' 			=> 'Deleted Roles',
                'guard_name' 	=> 'admin',
                'created_at' 	=> now(),
                'updated_at' 	=> now(),
            ],
            [
                'name' 			=> 'Retrieve Permissions',
                'guard_name' 	=> 'admin',
                'created_at' 	=> now(),
                'updated_at' 	=> now(),
            ],
            [
                'name' 			=> 'Create Permissions',
                'guard_name' 	=> 'admin',
                'created_at' 	=> now(),
                'updated_at' 	=> now(),
            ],
            [
                'name' 			=> 'Update Permissions',
                'guard_name' 	=> 'admin',
                'created_at' 	=> now(),
                'updated_at' 	=> now(),
            ],
            [
                'name' 			=> 'Deleted Permissions',
                'guard_name' 	=> 'admin',
                'created_at' 	=> now(),
                'updated_at' 	=> now(),
            ]
        
        ];

        foreach($permissions as $key=>$permission){

            if( !Permission::where('name',$permission['name'])->exists()){
                Permission::insert($permission);
            }
        }

    }
}
