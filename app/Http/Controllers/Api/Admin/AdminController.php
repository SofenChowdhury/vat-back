<?php

namespace App\Http\Controllers\Api\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminRequest;
use App\Models\Admin;
use App\Models\User;
use App\Repositories\RoleRepository;
use Illuminate\Support\Facades\Auth;
use App\Repositories\AdminRepository;
use App\Repositories\CompanyRepository;
use Illuminate\Support\Facades\Redirect;
use Spatie\Permission\Traits\HasRoles;

class AdminController extends Controller
{
    protected $admin;
    protected $role;
    protected $company;
    public function __construct(AdminRepository $admin, RoleRepository $role, CompanyRepository $company )
    {
        $this->admin    = $admin;
        $this->role     = $role;
        $this->company  = $company;
    }
    
    public function index()
    {
        $user = auth()->user();
        if ($user->can('show user')) {
            $admins = $this->admin->getAll();
            return response()->json([
                'status' => true,
                'data' => $admins
            ]);    
        }else{
            return response()->json(['status' => false, 'errors' => '', 'message' => "You have no permission to show this user"]);
        }            
    }

    public function show($id)
    {
        $user = auth()->user();
        if ($user->can('show user')) {
            return $this->admin->getById($id);
        }else{
            return response()->json(['status' => false, 'errors' => '', 'message' => "You have no permission to show this user"]);
        }
    }

    public function store(AdminRequest $request)
    {
        if (auth()->user()->can('create user')) {
            $admin = $this->admin->create($request);
            return response()->json([
                'status' => true,
                'data' => $admin,
                'errors' => '', 
                'message' => "A new user has been successfully created",
            ]);
        }else{
            return response()->json(['status' => false, 'errors' => '', 'message' => "You have no permission to create new user"]);
        }
       
    }


    public function update(Request $request)
    {        
        if (auth()->user()->can('edit user')) {
            return $this->admin->update($request->id, $request);
        }else{
            return response()->json(['status' => false, 'errors' => '', 'message' => "You have no permission to update user"]);
        }
    }

    public function changePassword()
    {
        return Inertia::render('Admin/ChangePassword');
    }

    public function StoreChangePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed|min:8'
        ]);
        $user = auth()->user();
        $this->admin->changePassword($user->id, $request);
        return true;
    }

    public function storeUserProfile(Request $request)
    {        
        $this->admin->updateProfile($request);
    }

    public function guard()
    {
        return Auth::guard('api');
    }

    public function searchByPhoneNameEmail($query = NULL)
    {
        $admins = $this->admin->searchByPhoneNameEmail($query);
        if (!empty($admins)) {
            return response()->json([
                'status' => true,
                'errors' => [],
                'data' => $admins,
                'message' => "Admins successfully loaded."
            ]);
        }else{
            return response()->json([
                'status' => false,
                'errors' => [],
                'data' => $admins,
                'message' => "Admins not found."
            ]);
        }
    }
}