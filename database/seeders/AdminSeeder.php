<?php

use App\Models\Admin;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users=[
            [
                'name' 			=> 'Ahasan Ullah',
                'uuid' 	        => Str::uuid(),
                'email' 	    => 'admin@email.com',
                'phone' 	    => '01700718853',
                'status' 	    => 1,
                'password' 	    => Hash::make('123456789')
            ]
        ];    


        foreach($users as $key=>$user){

            if( !Admin::where('email',$user['email'])->where('phone',$user['phone'])->exists()){
                Admin::insert($user);
            }
        }

    }
}
