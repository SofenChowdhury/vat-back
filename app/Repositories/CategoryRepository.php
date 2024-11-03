<?php
namespace App\Repositories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class CategoryRepository implements BaseRepository{

     protected  $model;
     
     public function __construct(Category $model)
     {
        $this->model = $model;
     }

     /**
      * all resource get
      * @return array
      */
     public function getAll(){
         return $this->model::where('company_id', auth()->user()->company_id)
         ->get();
     }
     
     /**
     *  specified resource get .
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function getById(int $id){
          return $this->model::where('company_id', auth()->user()->company_id)
          ->find($id);
     }  
     
     /**
     *  specified resource get .
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */

     
    public function getByUuid($uuid){
          return $this->model::where('company_id', auth()->user()->company_id)->where('uuid', $uuid)->first();
     } 

     /**
     * resource create
     * @param  $request
     * @return \Illuminate\Http\Response
     */

     public function create($request){
          $category = $this->model;
          $category->company_id = auth()->user()->company_id;
          $category->name = $request->name;          
          $category->status = $request->status? 1:0;
          $category->save();
          return $category;
     }  

    /**
      * specified resource update
      *
      * @param int $id
      * @param  $request
      * @return \Illuminate\Http\Response
      */

     public function update($id, $request){

          $category= $this->getById($id);
          $category->name = $request->name;
          $category->status = $request->status;
          $category->update();
          return $category;
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


     public function categories()
     {
          return  $this->model::where('status', 1)
          ->where('company_id', auth()->user()->company_id)
          ->get();
     }

     public function productBycategory($slug)
     {
          $category = $this->model::where('company_id', auth()->user()->company_id)
          ->where('status', 1)
          ->where('slug', $slug)
          ->first();
          $product = Product::where('status', 1)->where('category_id', $category->id)->paginate(12);
          $data['category']=$category;
          $data['products']=$product;
          return $data;
          
     }


     public function categoryProduct($limit)
     {
         return $this->model::with(['products'=>function($query){
               $query->where('status',1)->limit(8)->get();
          }])->where('status', 1)->limit($limit)->get();
     }

}
