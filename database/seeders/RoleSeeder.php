<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles=[
            [
                'name' 			=> 'Super Admin',
                'guard_name' 	=> 'admin',
                'created_at' 	=> now(),
                'updated_at' 	=> now(),
            ],
            [
                'name' 			=> 'Admin',
                'guard_name' 	=> 'admin',
                'created_at' 	=> now(),
                'updated_at' 	=> now(),
            ],
            [
                'name' 			=> 'CompanyManager',
                'guard_name' 	=> 'admin',
                'created_at' 	=> now(),
                'updated_at' 	=> now(),
            ]
        ];
        


        foreach($roles as $key=>$role){

            if( !Role::where('name',$role['name'])->exists()){
                Role::insert($role);
            }
        }

    }
}
