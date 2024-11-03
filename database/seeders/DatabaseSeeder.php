<?php
namespace Database\Seeders;
use RoleSeeder;
use AdminSeeder;
use PermissionSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {


        // \App\Models\User::factory(10)->create();
        // $this->call([
        //     PermissionSeeder::class,
        //     RoleSeeder::class,
        //     AdminSeeder::class
        // ]);
        // $this->call('RoleSeeder');
        $this->call(RoleSeeder::class);
        // $this->call(AdminSeeder::class);
        // \App\Models\Slider::factory(10)->create();
        // \App\Models\Product::factory(50)->create();
    }
}