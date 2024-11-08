<?php
namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class UserRepository implements BaseRepository{

     protected  $model;
     
     public function __construct(User $model)
     {
        $this->model=$model;
     }

     /**
      * all resource get
      * @return Collection
      */
     public function getAll($company_id = NULL){
          if (!empty($company_id)) {
               return $this->model::where('company_id', $company_id)
               ->get();
          }else{
               return $this->model::all();
          } 
         
     }

     public function getPageTen($company_id = NULL){
          if (!empty($company_id)) {
               return $this->model::where('company_id', $company_id)
               ->paginate(10);
          }else{
               return $this->model::paginate(10);
          }         
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
     * @param $request
     * @return \Illuminate\Http\Response
     */

     public function create($request){   

          $admin = $this->model;          
          $admin->name = $request->name;
          $admin->email = $request->email;
          $admin->phone = $request->phone;
          $admin->password = Hash::make($request->password);
          $admin->save();
          return $admin;
     }  

    /**
      * specified resource update
      *
      * @param int $id
      * @param $request
      * @return \Illuminate\Http\Response
      */

     public function update( int $id, $request){

          $admin= $this->getById($id);

          $admin->name=$request->name;
          $admin->update();
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
