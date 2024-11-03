<?php

namespace App\Providers;

use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // $tz = 'Asia/Dhaka';
        // $timestamp = time();
        // $dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
        // $dt->setTimestamp($timestamp); //adjust the object to correct timestamp
        
        // // Add in boot function
        // DB::listen(function($query) use ($dt) {
        //     File::append(
        //         storage_path('/logs/query.log'),
        //         '[' . $dt->format('Y-m-d, H:i:s') . ']' . PHP_EOL . $query->sql . ' [' . implode(', ', $query->bindings) . ']' . PHP_EOL . PHP_EOL
        //     );
        // });
    }
}
