<?php
namespace App\Repositories;

use App\Models\Company;
use Illuminate\Support\Str;
use Mahabub\CrudGenerator\Contracts\BaseRepository;
use Spatie\Activitylog\Models\Activity;

class ActivityLogsRepository implements BaseRepository{

     protected  $model;
     
     public function __construct(Activity $model)
     {
        $this->model = $model;
     }

     /**
      * all resource get
      * @return Collection
      */
     public function getAll(){
         $activities =  $this->model::paginate(20);
         return $activities;
     }

     public function search($request, $company_id = null){
        $query = $this->model::query();
        if ($request->has('user_id') && $request->user_id != "") {
            $query->where('causer_type', 'App\Models\Admin')->where('causer_id', $request->user_id);
        }
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }
        $query->latest('id');
        $activities = $query->paginate(20);
        
        return $activities;
        
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
