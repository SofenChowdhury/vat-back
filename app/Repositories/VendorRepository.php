<?php
namespace App\Repositories;

use App\Models\Vendor;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class VendorRepository implements BaseRepository{

     protected  $model;
     
     public function __construct(Vendor $model)
     {
        $this->model = $model;
     }

     /**
      * all resource get
      * @return Collection
      */
     public function getAll(){
          $company_id = auth()->user()->company_id;
          if ($company_id>0) {
               return $this->model::with('company')
               ->where('company_id', $company_id)
               ->orderBy("id", "desc")->paginate(20);
          }else{
               return $this->model::with('company')
               ->orderBy("id", "desc")->paginate(20);
          }
         
     }

     public function getAllPage(){
          $company_id = auth()->user()->company_id;
          if ($company_id>0) {
               return $this->model::with('company')
               ->where('company_id', $company_id)
               ->orderBy("id", "desc")->get();
          }else{
               return $this->model::with('company')
               ->orderBy("id", "desc")->get();;
          }
      }

     public function search($request)
     {          
          $query = $request->keyword;    
          $company_id = auth()->user()->company_id;
          return $this->model::with('company')
          ->where('company_id', $company_id)
          ->where(function($q) use ($query){
               $q->where('name','LIKE', $query.'%');
               $q->orWhere('contact_number', 'LIKE', $query.'%');
               $q->orWhere('vendor_bin', 'LIKE', $query.'%');
               $q->orWhere('contact_email', 'LIKE', $query.'%');
          })
          ->orderBy("name", "asc")
          ->take(100)
          ->get();
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
     *  specified resource get
     *
     * @param  string  $company_code
     * @return \Illuminate\Http\Response
     */

     
    public function getByCode($vendor_code){
        return $this->model::where('vendor_code', $vendor_code)->first();
   } 

     /**
     * resource create
     * @param  $request
     * @return \Illuminate\Http\Response
     */

     public function create($request){          
          $vendor = $this->model;
          $request['company_id'] = auth()->user()->company_id;
          $vendor->updateOrCreate($request);
          return $vendor;
     }  

    /**
      * specified resource update
      *
      * @param int $id
      * @param  $request
      * @return \Illuminate\Http\Response
      */

     public function update($id, $request){
          $company = $this->model->where('id', $id)->update($request);
          return $company;
          // $company->update($request);
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
