<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Cache;


class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request)
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request)
    {

        $permissions = null; 
        if($request->user()){
             
             $permissions = auth()->user()->getAllPermissions();
            // $permissions = Cache::remember('permissions', 60, function() use($request) {
            //     $permissions = auth()->user();
            //     return $permissions->getAllPermissions();
            // });            
        }
        
        $toast = null;
        if ($request->session()->has('toaster')) {
            $toast =$request->session()->get('toaster');
        } 

        return array_merge(parent::share($request), [

            'auth' => [
                'user' => $request->user(),
                'permissions' => $permissions,
            ],
            'flash' => function () use ($request) {
                return [
                    'success' => $request->session()->get('success'),
                ];
            },
            'toaster' =>$toast,

            'ziggy' => function () {
                return (new Ziggy)->toArray();
            },
        ]);
    }
}
