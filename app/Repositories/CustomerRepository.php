<?php
namespace App\Repositories;

use App\Jobs\CustomerBulkUploadJob;
use App\Models\Customer;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class CustomerRepository implements BaseRepository{

     protected  $model;
     
     public function __construct(Customer $model)
     {
        $this->model=$model;
     }

     /**
      * all resource get
      * @return Collection
      */
     public function getAll($company_id = NULL){
          if (!empty($company_id)) {
               return $this->model::with('company')
               ->where('company_id', $company_id)
               ->paginate(20);
          }else{
               return $this->model::with('company')->paginate(20);
          }
          
     }

     public function search($request, $company_id)
     {
          $user = auth()->user();
          $query = $this->model::query();
          $query->with('company');
          
          $query->where(function ($query) use ($request)
          {
               if ($request->name != "") {
                    $query->orWhere('name', 'LIKE', '%' . $request->name . '%');
               }
               if ($request->code != "") {
                    $query->orWhere('code', 'LIKE', '%' . $request->code . '%');               
               }
               if ($request->phone != "") {
                    $query->orWhere('phone', 'LIKE', '%' . $request->phone . '%');
               }
               if ($request->email != "") {
                    $query->orWhere('email', 'LIKE', '%' . $request->email . '%');
               }
               if ($request->type != "") {
                    $query->orWhere('type', $request->type);
               }
               if ($request->bin != "") {
                    $query->orWhere('bin', $request->bin);
               }
          });
          $query->where('company_id', $user->company_id);
          return $query->paginate(20);
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
     
     public function getByMobile(int $mobile){
          return $this->model::with('company')
          ->where('company_id', auth()->user()->company_id)
          ->where('phone', $mobile)
          ->first();
     }

     public function searchByPhoneNameEmail($query)
     {
          return $this->model::with('company')
          ->where('company_id', auth()->user()->company_id)
          ->where(function ($q) use ($query)
          {
               $q->where('phone','LIKE','%'.$query.'%')
               ->orWhere('code','LIKE','%'.$query.'%')
               ->orWhere('name', 'LIKE', '%'.$query.'%')
               ->orWhere('email', 'LIKE', '%'.$query.'%');
          })
          ->get();
     }

     /**
     * resource create
     * @param $request
     * @return \Illuminate\Http\Response
     */

     public function create($request){
          $customer = $this->model;
          $customer->name = $request->name;
          $customer->code = $request->code;;
          $customer->email = $request->email;
          $customer->phone = $request->phone;
          $customer->address = $request->address;
          $customer->shipping_address = $request->shipping_address;
          $customer->company_id = auth()->user()->company_id;
          $customer->type = $request->type;
          $customer->tin = $request->tin;
          $customer->bin = $request->bin;
          $customer->nid = $request->bin;
          $customer->save();
          return $customer;
     }  

    /**
      * specified resource update
      *
      * @param int $id
      * @param  $request
      * @return \Illuminate\Http\Response
      */

     public function update( int $id, $request){

          $customer = $this->getById($id);
          $customer->name = $request->name;
          $customer->code = $request->code;;
          $customer->email = $request->email;
          $customer->phone = $request->phone;
          $customer->address = $request->address;
          $customer->shipping_address = $request->shipping_address?$request->shipping_address:$request->address;
          $customer->company_id = auth()->user()->company_id;
          $customer->type = $request->type;
          $customer->tin = $request->tin;
          $customer->bin = $request->bin;
          $customer->nid = $request->bin;
          $customer->update();
          return $customer;
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

     public function BulkQueueUpload($request)
     {
          if( $request->has('csvfile')) {

            $csv    = file($request->csvfile);
            $chunks = array_chunk($csv,200);
            $header = [
                'name',
                'company_id',
                'code', 
                'phone', 
                'email', 
                'address', 
                'shipping_address', 
                'type', 
                'nid', 
                'tin',
                'bin'
            ];
            foreach ($chunks as $key => $chunk) {
               
            $data = array_map('str_getcsv', $chunk);
                if($key == 0){
                    unset($data[0]);
                }
                try {
                    CustomerBulkUploadJob::dispatch($header, $data);
                } catch (\Throwable $th) {
                    return $th->getMessage();
                }
            }
        }
     }
}
