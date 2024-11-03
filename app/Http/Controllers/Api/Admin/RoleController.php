<?php
namespace App\Http\Controllers\Api\Admin;

use Inertia\Inertia;
use App\Http\Requests\RoleRequest;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use Illuminate\Support\Facades\Redirect;

class RoleController extends Controller
{
    protected $role;
    protected $permission;

    public function __construct(RoleRepository $role, PermissionRepository $permission)
    {
         $this->role = $role;
         $this->permission = $permission;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index()
    {
        if (!auth()->user()->can('show role')) {
            return response()->json([
                'status' => false,
                'message' => "You have no permission to get the Roles"
            ]);
        }        
        $roles=  $this->role->getAll();
        return response()->json([
            'status' => true,
            'data' => $roles
        ]);
    }


   /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function create(RoleRequest $request)
    {        
        if (auth()->user()->can('create role')) {
            return $this->role->create($request);
        }else{
            return response()->json(['status'=>false ,'errors'=> '', 'message' => "You have no permission to create the role"]);
        }

    }

   /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */

     public function show($id)
    {
        if (auth()->user()->can('show role')) {
            $role = $this->role->getById($id);
            return response()->json(['status'=>true ,'data'=> $role]);
        }else{
            return response()->json(['status'=>false ,'errors'=> '', 'message' => "You have no permission to show the role"]);
        }
    }

   /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(RoleRequest $request)
    {
        if (auth()->user()->can('edit role')) {
            return $this->role->update($request->id, $request);
        }else{
            return response()->json(['status' => false, 'errors' => '', 'message' => "You have no permission to update user"]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        if (!auth()->user()->can('delete role')) {
            return Redirect::route('dashboard')->with('error', 'You have no permission to delete the role');
        }
        $this->role->delete($id);
        return Redirect::route('roles.index');

    }
}
