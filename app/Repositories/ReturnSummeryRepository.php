<?php
namespace App\Repositories;

use App\Models\ReturnSummary;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class ReturnSummeryRepository implements BaseRepository{

     protected  $model;
     
     public function __construct(ReturnSummary $model)
     {
        $this->model = $model;
     }

     /**
      * all resource get
      * @return Collection
      */
     public function getAll(){
         return $this->model::all();
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

     function getByMonth($request, $company_id) {
          $data['start_date'] = date('Y-m-01');
          $data['end_date'] = date('Y-m-d');
          
          if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
              $data['start_date'] = date('Y-m-d',  strtotime($request->start_date));
              $data['end_date'] = date('Y-m-d', strtotime($request->end_date));
          }
          
          $summary =  $this->model::
          whereBetween('ledger_month', [$data['start_date'], $data['start_date']])
          ->where('company_id', $company_id)
          ->orderBy('id', 'desc')
          ->first();
  
          return $summary;
     }
     
     /**
     *  specified resource get .
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */

     function mushokReturn($request, $company_id)  {
        $data['start_date'] = date('Y-m-01');
        $data['end_date'] = date('Y-m-d');
        
        if ((isset($request->start_date) && $request->start_date !="") && (isset($request->end_date) && $request->end_date !="")) {
            $data['start_date'] = date('Y-m-d',  strtotime($request->start_date));
            $data['end_date'] = date('Y-m-d', strtotime($request->end_date));
        }
        
        $summary =  $this->model::
        where('company_id', $company_id)
        ->where('ledger_month', '<=', $data['end_date'])
        ->orderBy('id', 'desc')
        ->first();
        return $summary;
     }



     /**
     * resource create
     * @param  $request
     * @return \Illuminate\Http\Response
     */

     public function create($request){ 
          $company = $this->model;
          $request['company_id'] = auth()->user()->company_id;
          $request['return_submitted_by'] = auth()->user()->id;
          // return $request->all();
          $company->updateOrCreate($request->all());
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
