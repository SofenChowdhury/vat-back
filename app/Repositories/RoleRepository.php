<?php
namespace App\Repositories;

use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class RoleRepository implements BaseRepository{

     protected  $model;
     
     public function __construct(Role $model)
     {
        $this->model=$model;
     }

     /**
      * all resource get
      * @return Collection
      */
     public function getAll(){
          return $this->model::with('permissions')->get();
     }
     
     /**
     *  specified resource get .
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function getById(int $id){
          return $this->model::with('permissions')->find($id);
     }

     /**
     * resource create
     * @param $request
     * @return \Illuminate\Http\Response
     */

     public function create($request){
          $validator= Validator::make($request->all(),[
               'name' => 'required|string|max:255',
               'description' => ['max:200']
          ]);

          if($validator->fails()){
               return response()->json(['status'=>false ,'errors'=> $validator->errors()]);
          }

          $role =$this->model;
          $role->name = $request->name;
          $role->description = $request->description ? $request->description: NULL;
          $role->save();
          $role->syncPermissions($request->permissions);
          return response()->json(['status'=>true ,'data'=> $role]);
     }  

    /**
      * specified resource update
      *
      * @param int $id
      * @param $request
      * @return \Illuminate\Http\Response
      */

     public function update( int $id, $request){

          $validator= Validator::make($request->all(),[
               'name' => 'required|string|max:50|unique:roles,name,'.$id
          ]);

          if($validator->fails()){
               return response()->json(['status'=>false ,'errors'=> $validator->errors()]);
          }

          $role = $this->getById($id);          
          $role->name = $request->name;
          $role->description = $request->description? $request->description : NULL;
          $role->syncPermissions($request->permissions);
          $role->update();
          return response()->json(['status'=>true ,'data'=> $role]);
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
