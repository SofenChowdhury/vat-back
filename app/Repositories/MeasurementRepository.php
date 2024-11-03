<?php
namespace App\Repositories;

use App\Models\MeasurementUnit;
use Illuminate\Support\Facades\Auth;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class MeasurementRepository implements BaseRepository{

   protected $model;
   
   public function __construct(MeasurementUnit $model)
   {
      $this->model = $model;
   }

   /**
   * all resource get
   * @return array
   */
   public function getAll(){
      return $this->model::paginate(20);
   }
     

   public function search($request)
   {

      $query = $request->name;
      
      return $this->model::where(function($q) use ($query){
                     $q->where('name','LIKE', $query.'%');
                  })
                  ->orderBy("name", "asc")
                  ->get();
   }


     public function getById(int $id){
          return $this->model::find($id);
     }
     
    public function getByName($name){
      return $this->model::
      where('name', 'LIKE', '%'.$name.'%')
      ->first();
    }

    public function create($request){
        $measurement = $this->model;
        $measurement->company_id = auth()->user()->company_id;
        $measurement->name = $request->name;          
        $measurement->remarks = $request->remarks? $request->remarks:NULL;
        $measurement->save();
        return $measurement;
   }

    public function update( int $id,  $request)
    {
        $measurement= $this->getById($id);
        $measurement->name = $request->name;
        $measurement->remarks = $request->remarks;
        $measurement->update();
        return $measurement;
    }

    public function delete($id)
    {
        return $this->getById($id)->delete();
    }

    public function guard()
    {
        return Auth::guard('api');
    }
}
