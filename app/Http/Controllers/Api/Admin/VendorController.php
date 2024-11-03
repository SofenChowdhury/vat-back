<?php
namespace App\Http\Controllers\Api\Admin;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\VendorRepository;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    protected $vendor;

    public function __construct(VendorRepository $vendor)
    {
         $this->vendor = $vendor;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
            $vendors = $this->vendor->getAll();
            return response()->json([
                'status' => true,
                'data' => $vendors,
                'errors' => '', 
                'message' => "Vendors List Loaded"
            ]);
    }

    public function search(Request $request)
    {
        $vendors = $this->vendor->search($request);
        return response()->json([
            'status' => true,
            'data' => $vendors,
            'errors' => '', 
            'message' => "Vendors List Loaded"
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // if (auth()->user()->can('create vendor')) {
            $validator= Validator::make($request->all(),[
                'name' => 'required|string|max:255',
                'contact_person' => 'string|max:100',
                'contact_email' => 'string|max:100',
                'contact_number' => 'string|max:100',
                'contact_address' => 'string|max:255',
                'vendor_bin' => 'required|string|max:100'
           ]);

           if($validator->fails()){
                return response()->json(['status'=>false ,'errors'=> $validator->errors()]);
           }
           
            $vendor = $this->vendor->create($request->all());
            return response()->json(['status' => true, 'data' => $vendor]);
        // }else{
        //     return response()->json(['status' => false, 'errors' => '', 'message' => "You have no permission to show the vendor"]);
        // }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // if (auth()->user()->can('show vendor')) {
            $vendor = $this->vendor->getById($id);
            return response()->json(['status' => true, 'data' => $vendor]);
        // }else{
        //     return response()->json(['status' => false, 'errors' => '', 'message' => "You have no permission to show the vendor"]);
        // }
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
        // if (auth()->user()->can('create vendor')) {
            $validator= Validator::make($request->all(),[
                'name' => 'required|string|max:255',
                'contact_person' => 'string|max:100',
                'contact_email' => 'string|max:100',
                'contact_number' => 'string|max:100',
                'contact_address' => 'string|max:255',
                'vendor_tin' => 'string|max:100',
                'vendor_bin' => 'required|string|max:100'
            ]);

            if($validator->fails()){
                return response()->json(['status'=>false ,'errors'=> $validator->errors()]);
            }
            
            // return $request;
            $data['name'] = $request->name;
            $data['vendor_code'] = $request->vendor_code;
            $data['contact_person'] = $request->contact_person;
            $data['contact_number'] = $request->contact_number;
            $data['contact_email'] = $request->contact_email;
            $data['contact_address'] = $request->contact_address;
            $data['vendor_tin'] = $request->vendor_tin;
            $data['vendor_bin'] = $request->vendor_bin;
            $this->vendor->update($request->id, $data);
            return response()->json(['status' => true, 'data' => $data]);
            
        // }else{
        //     return response()->json(['status' => false, 'errors' => '', 'message' => "You have no permission to show the vendor"]);
        // }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $this->vendor->delete($request->id);
        return response()->json([
            'status' => true,
            'data' => [],
            'errors' => '', 
            'message' => "The vendor has been successfully deleted",
        ]);
    }
}
