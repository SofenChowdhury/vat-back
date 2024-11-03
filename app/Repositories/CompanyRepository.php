<?php
namespace App\Repositories;

use App\Models\Company;
use Illuminate\Support\Str;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class CompanyRepository implements BaseRepository{

     protected  $model;
     
     public function __construct(Company $model)
     {
        $this->model = $model;
     }

     /**
      * all resource get
      * @return Collection
      */
     public function getAll(){
         return $this->model::with('branches')->get();
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
     *  specified resource get .
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */


     
      /**
     *  specified resource get
     *
     * @param  string  $company_code
     * @return \Illuminate\Http\Response
     */

     
    public function getByCode($company_code){
        return $this->model::where('company_code', $company_code)->first();
   } 

     /**
     * resource create
     * @param  $request
     * @return \Illuminate\Http\Response
     */

     public function create($request){ 
          unset($request['uuid']);
          $company = $this->model;
          $company->updateOrCreate($request);
          return $company;
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
