<?php
namespace App\Http\Controllers\Api\Admin;

use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\PermissionRequest;
use Illuminate\Support\Facades\Redirect;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;

class PermissionController extends Controller
{
    protected $permission;
    protected $role;

    public function __construct(PermissionRepository $permission, RoleRepository $role)
    {
         $this->permission = $permission;
         $this->role = $role;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index()
    {
        if (auth()->user()->can('show permission')) {
            return $this->permission->getAll();
        }else{
            return response()->json(['status'=>false ,'errors'=> '', 'message' => "You have no permission to show the permission"]);
        }       
    }

    function getAllPaginate() {        
        if (auth()->user()->can('show permission')) {
            return $this->permission->getAllPaginate();
        }else{
            return response()->json(['status'=>false ,'errors'=> '', 'message' => "You have no permission to show the permission"]);
        }       
    }

   /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function create(PermissionRequest $request)
    {
        if (auth()->user()->can('create permission')) {
            return $this->permission->create($request);
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
        $permission = $this->permission->getById($id);

         return view('permission.show', compact('permission'));
    }

   /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function edit($id)
    {
        $permission = $this->permission->getById($id);
        $roles=  $this->role->getAll();
        return Inertia::render('Permission/Create', compact('roles', 'permission'));
    }

   /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(PermissionRequest $request, $id)
    {
        $permission=$this->permission->update($id, $request);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        $this->permission->delete($id);
        return back();

    }
}
