<?php
namespace App\Repositories;

use App\Classes\FileUpload;
use App\Models\HsCode;
use App\Models\ItemStock;
use Illuminate\Support\Facades\Auth;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class HsCodeRepository implements BaseRepository{

   protected $model;
   
   public function __construct(HsCode $model)
   {
      $this->model = $model;
   }

   /**
   * all resource get
   * @return Collection
   */
   public function getAll(){
      return $this->model::paginate(20);
   }

   public function getAllWithSearch($query)
   {
      if ($query->hscode !="") {
         return $this->model::where('code','LIKE', $query->hscode.'%')
         ->orWhere('code_dot', 'LIKE', $query->hscode.'%')
         ->orWhere('description', 'LIKE', '%'.$query->hscode.'%')
         ->paginate(20);
      }else{
         return $this->model::paginate(20);
      }
      
   }
     

   public function search($request)
   {

      $query = $request->keyword;
      
      return $this->model::where(function($q) use ($query){
                     $q->where('code','LIKE', $query.'%');
                     $q->orWhere('code_dot', 'LIKE', $query.'%');
                     $q->orWhere('description', 'LIKE', '%'.$query.'%');
                  })
                  ->orderBy("description", "asc")
                  ->take(100)
                  ->get();
   }


     public function getById(int $id){
          return $this->model::with('products')->find($id);
     }
     
    public function getByCode($code){
      return $this->model::with('products')
      ->where('code', $code)
      ->orWhere('code_dot', 'LIKE', '%'.$code.'%')
      ->first();
    }

   public function download($request)
   {
      $query = $request->keyword;
   
      return $this->model::where(function($q) use ($query){
                  $q->where('code','LIKE', $query.'%');
                  $q->orWhere('code_dot', 'LIKE', $query.'%');
                  $q->orWhere('description', 'LIKE', '%'.$query.'%');
               })
               ->orderBy("description", "asc")
               ->get();
   }

   public function create($request)
   {
      return false;
   }

    public function update( int $id,  $request)
    {
    return false;
    }

    public function delete($id)
    {
        return false;
    }

    public function guard()
    {
        return Auth::guard('api');
    }
}
