<?php
namespace App\Repositories;

use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class PermissionRepository implements BaseRepository{

     protected  $model;
     
     public function __construct(Permission $model)
     {
        $this->model=$model;
     }

     /**
      * all resource get
      * @return Collection
      */
     public function getAll(){
         $permissions = $this->model::with('roles')->get();
         return response()->json(['status'=> true,'data'=> $permissions]);
     }

     public function getAllPaginate(){
          $permissions = $this->model::with('roles')->paginate(20);
          return response()->json(['status'=> true,'data'=> $permissions]);
      }
     
     /**
     *  specified resource get .
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function getById(int $id){
          return $this->model::find($id);
     }   

     /**
     * resource create
     * @param  $request
     * @return \Illuminate\Http\Response
     */

     public function create($request){
          $validator= Validator::make($request->all(),[
               'name' => 'required|string|max:255',
               'description' => ['max:200']
          ]);
          if($validator->fails()){
               return response()->json(['status'=>false , 'errors' => $validator->errors()]);
          }
          $permission = $this->model;
          $permission->name = $request->name;
          $permission->description = $request->description ? $request->description: NULL;
          $permission->save();
          if (!empty($request->roles)) {
               $permission->syncRoles($request->roles);
          }
          return response()->json(['status'=>true ,'data'=> $permission]);
     }  

    /**
      * specified resource update
      *
      * @param int $id
      * @param  $request
      * @return \Illuminate\Http\Response
      */

     public function update( int $id, $request){

          $permission= $this->getById($id);

          $permission->name=$request->name;
          $permission->update();
          if (!empty($request['admin_roles'])) {
               $permission->syncRoles($request->admin_roles);
          }
     } 
        
     /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function delete($id){
       return $this->getById($id)->delete();
     }
}
