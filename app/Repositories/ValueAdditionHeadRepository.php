<?php
namespace App\Repositories;

use App\Models\Product;
use App\Models\ValueAddition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class ValueAdditionHeadRepository implements BaseRepository{

     protected  $model;
     protected  $product;
     
     public function __construct(ValueAddition $model, Product $product)
     {
        $this->model = $model;
        $this->product = $product;
     }

     /**
      * all resource get
      * @return Collection
      */
     public function getAll($company_id = NULL){ 
        return $this->model::where('company_id', auth()->user()->company_id)
        ->orderBy('serial', 'asc')->get();             
          
     }

     public function getById(int $id, $company_id = NULL){
          return $this->model::where('id', $id)->first();
     }  


     /**
     * resource create
     * @param  $request
     * @return \Illuminate\Http\Response
     */

    public function create($request){ 
        try {
            $validator = Validator::make($request->all(), [
                'serial' => 'required',
                'head' => 'required',
                'percentage' => 'required'
            ]);

            if( $validator->fails()){
                return ['status' => false , 'errors' => $validator->errors()];
            }   
                  
            $settings = $this->model;
            $settings->serial    = $request->serial;
            $settings->company_id    = auth()->user()->company_id;
            $settings->head    = $request->head;
            $settings->percentage    = $request->percentage;
            $settings->status    = $request->status;
            $settings->save();
            return response()->json([
                'status' => true,
                'data' => $settings,
                'errors' => '', 
                'message' => $settings->head." price settings has been successfully created"
            ]);
            
        }catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'data' => [],
                'errors' => '', 
                'message' => $th->getMessage()
            ]);
            
        }
        
    }  

    /**
      * specified resource update
      *
      * @param int $id
      * @param  $request
      * @return \Illuminate\Http\Response
      */

     public function update( int $id,  $request){
        $settings = $this->getById($id);
        $settings->serial    = $request->serial;
        $settings->head    = $request->head;
        $settings->percentage    = $request->percentage;
        $settings->status    = $request->status;
        $settings->update();
        return response()->json([
            'status' => true,
            'data' => $settings,
            'errors' => '', 
            'message' => $settings->head." price settings has been successfully updated"
        ]);
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

     public function guard()
     {
          return Auth::guard('api');
     }

}
