<?php
namespace App\Repositories;

use App\Models\Branch;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class BranchRepository implements BaseRepository{

     protected  $model;
     protected  $file;
     
     public function __construct(Branch $model)
     {
        $this->model = $model;
     }
     
     public function getAll($company_id = NULL){
          if (!empty($company_id)) {
               return $this->model::with(['company'])
               ->where('company_id', $company_id)
               ->orderBy("name", "asc")
               ->lazy();
          }else{
               return $this->model::with(['company'])
               ->lazy();
          }          
     }

     public function getPage($company_id = NULL, $request = []){
          if (!empty($company_id)) {
               $query = $this->model::with(['company']);
               if (!empty($request)) {
                    $query->where(function ($query) use ($request)
                    {
                         if ($request->name != "") {
                              $query->orWhere('name', 'LIKE', '%' . $request->name . '%');
                         }
                         if ($request->code != "") {
                              $query->orWhere('code', 'LIKE', '%' . $request->code . '%');               
                         }
                    });
               }               
               $branches = $query->where('company_id', $company_id)
               ->paginate(20);
               return $branches;
          }else{
               return $this->model::with(['company'])
               ->paginate(20);
          }
          
     }
     
     /**
     *  specified resource get .
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function getById(int $id){
          return $this->model::with('company')->find($id);
     } 

      /**
     *  specified resource get .
     *
     * @param  string  $company_code
     * @return \Illuminate\Http\Response
     */

     
     public function getByDistrictID($district_id = NULL){
          if (!empty($district_id)) {
               return $this->model::where('district_id', $district_id)->get();
          }else{
               return $this->getAll();
          }          
     }

       public function getBThanaID($thana_id = NULL){
          if (!empty($thana_id)) {
               return $this->model::where('thana_id', $thana_id)->get();
          }else{
               return $this->getAll();
          }          
     }



     /**
     *  specified resource get .
     *
     * @param  string  $company_code
     * @return \Illuminate\Http\Response
     */

     
     public function getByThanaID($thana_id = NULL){
          if (!empty($thana_id)) {
               return $this->model::where('thana_id', $thana_id)->get();
          }else{
               return $this->getAll();
          }          
     }

     /**
     *  specified resource get .
     *
     * @param  string  $company_code
     * @return \Illuminate\Http\Response
     */

     
     public function getByCompanyID($company_id = NULL){
          if (!empty($company_id)) {
               return $this->model::where('company_id', $company_id)->get();
          }else{
               return $this->getAll();
          }          
     }

      /**
     *  specified resource get .
     *
     * @param  string  $company_code
     * @return \Illuminate\Http\Response
     */

     
     public function getByAdminID($admin_id = NULL){
          if (!empty($admin_id)) {
               return $this->model::where('admin_id', $admin_id)->get();
          }else{
               return $this->getAll();
          }
     }

     /**
     * resource create
     * @param  $request
     * @return \Illuminate\Http\Response
     */

     public function create($request){
          try {
               $branch = $this->model;
               $branch->company_id = (int) auth()->user()->company_id;
               $branch->name = $request->name;
               $branch->code = $request->code;
               $branch->order_prefix = $request->order_prefix;
               $branch->person = $request->contact_person;
               $branch->email = $request->contact_email;
               $branch->phone = $request->contact_mobile;
               $branch->address = $request->contact_address;
               $branch->type = $request->type? $request->type: NULL;
               $branch->save();
               return $branch;
          } catch (\Throwable $th) {
               return $th->getMessage();
          }
          
     }  

    /**
      * specified resource update
      *
      * @param string $id
      * @param  $request
      * @return \Illuminate\Http\Response
      */

     public function update($id, $request){
          $branch = $this->getById($id);
          $branch->name = $request->name;
          $branch->code = $request->code;
          $branch->order_prefix = $request->order_prefix;
          $branch->person = $request->contact_person;
          $branch->email = $request->contact_email;
          $branch->phone = $request->contact_mobile;
          $branch->address = $request->contact_address;
          $branch->type = $request->type? $request->type: NULL;
          $branch->update();
          return $branch;
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

     public function getSlider($limit=null)
     {
         if($limit!=null){
               return $this->model::where('status',1)->latest()->get();
          }else{
               return $this->model::where('status',1)->latest()->limit($limit)->get();
          }
     }
}
