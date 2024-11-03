<?php
namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Repositories\CompanyRepository;
use App\Repositories\CustomerRepository;

class CustomerController extends Controller
{
    protected $customer;
    protected $company;
    protected $shop;

    public function __construct(CustomerRepository $customer, CompanyRepository $company)
    {
         $this->customer = $customer;
         $this->company = $company;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        $customers =  $this->customer->search($request, auth()->user()->company_id);
        if (!empty($customers)) {
            return response()->json([
                'status' => true,
                'errors' => [],
                'data' => $customers,
                'message' => "Customers successfully loaded"
            ]);
        }else{
            return response()->json([
                'status' => true,
                'errors' => [],
                'data' => [],
                'message' => "Customers not found"
            ]);
        }
    }

    public function searchByPhoneNameEmail($query = NULL)
    {
        $customers = $this->customer->searchByPhoneNameEmail($query);
        if (!empty($customers)) {
            return response()->json([
                'status' => true,
                'errors' => [],
                'data' => $customers,
                'message' => "Customers successfully loaded"
            ]);
        }else{
            return response()->json([
                'status' => false,
                'errors' => [],
                'data' => $customers,
                'message' => "Customers not found"
            ]);
        }
    }


   /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function create(CustomerRequest $request)
    {
        $customer = $this->customer->create($request);
        return response()->json([
            'status' => true,
            'data' => $customer,
            'errors' => '', 
            'message' => $request->name." Category has been successfully created",
        ]);

    }

   /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */

     public function show($id)
    {
        $customer = $this->customer->getById($id);
        if (!empty($customer)) {
            return response()->json([
                'status' => true,
                'errors' => [],
                'data' => $customer,
                'message' => "Customer successfully loaded"
            ]);
        }else{
            return response()->json([
                'status' => false,
                'errors' => [],
                'data' => $customer,
                'message' => "Customer not found"
            ]);
        }
    }
    

   /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request)
    {
        $customer = $this->customer->update($request->id, $request);
        return response()->json([
            'status' => true,
            'data' => $customer,
            'errors' => '', 
            'message' => $request->name." has been successfully updated",
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy(Request $request)
    {
        if($this->customer->delete($request->id)){
            return response()->json([
                'status' => true,
                'data' => [],
                'errors' => '', 
                'message' => "Customer has been successfully deleted",
            ]);
        }else{
            return response()->json([
                'status' => false,
                'data' => [],
                'errors' => '', 
                'message' => "An error has been occurred",
            ]);
        }
        
    }

    public function bulkUpload(Request $request) {
        $customers = $this->customer->BulkQueueUpload($request);
        return response()->json([
            'status' => true,
            'data' => $customers,
            'errors' => '', 
            'message' => "New Customers Successfully Uploaded",
        ]);
    }
}
