<?php
namespace App\Http\Controllers\Api\Admin;

use App\Classes\FileUpload;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\CompanyRepository;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    protected $company;
    protected $file;

    public function __construct(CompanyRepository $company, FileUpload $file)
    {
         $this->company = $company;
         $this->file = $file;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();
        if ($user->can('show company')) {
            $companies = $this->company->getAll();
            return response()->json(['status' => true, 'data' => $companies]);
        }else{
            return response()->json(['status' => false, 'errors' => '', 'message' => "You have no permission to show company list"]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        if ($user->can('create_company')) {
            $validator= Validator::make($request->all(),[
                'name' => 'required|string|max:255',
                'contact_person' => 'string|max:100',
                'contact_email' => 'string|email|max:100',
                'contact_number' => 'string|max:100',
                'contact_address' => 'string|max:255',
                'company_tin' => 'string|max:100',
                'company_bin' => 'required|string|max:100'
           ]);

           if($validator->fails()){
                return response()->json(['status'=>false ,'errors'=> $validator->errors()]);
           }
            $request = array_merge($request->all(), ['uuid' => Str::uuid(), 'slug' => Str::slug($request->name)]);
            $company = $this->company->create($request);
            return response()->json(['status' => true, 'data' => $company]);
        }else{
            return response()->json(['status' => false, 'errors' => '', 'message' => "You have no permission to show the company"]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = auth()->user();
        if ($user->can('show_company')) {
            $company = $this->company->getById($id);
            return response()->json(['status' => true, 'data' => $company]);
        }else{
            return response()->json(['status' => false, 'errors' => '', 'message' => "You have no permission to show the company"]);
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
        $user = auth()->user();
        if ($user->can('update_company')) {
            
            $validator= Validator::make($request->all(),[
                'name' => 'required|string|max:255',
                'contact_person' => 'string|max:100',
                'contact_email' => 'string|email|max:50',
                'contact_number' => 'string|max:20',
                'contact_address' => 'string|max:255',
                'company_tin' => 'string|max:100',
                'company_bin' => 'required|string|max:20'
            ]);

            if($validator->fails()){
                return response()->json(['status'=>false ,'errors'=> $validator->errors()]);
            }
            // File Upload
            $brand_logo = NULL;
            $logo = NULL;
            if($request->hasFile('logo')){
                $logo = $this->file->uploadFile($request, $fieldname = "logo", $file = NULL, $folder = "logos");
            }
            if($request->hasFile('brand_logo')){
                $brand_logo = $this->file->uploadFile($request, $fieldname = "brand_logo", $file = NULL, $folder = "logos");
            }
            // return $request;
            $data['name'] = $request->name;
            $data['slug'] = Str::slug($request->name);
            $data['company_code'] = $request->company_code;
            $data['contact_person'] = $request->contact_person;
            $data['contact_number'] = $request->contact_number;
            $data['contact_email'] = $request->contact_email;
            $data['contact_address'] = $request->contact_address;
            $data['company_tin'] = $request->company_tin;
            $data['company_bin'] = $request->company_bin;            
            $data['logo'] = $logo;            
            $data['brand_logo'] = $brand_logo;         
            $this->company->update($request->id, $data);
            return response()->json(['status' => true, 'data' => $data]);
        }else{
            return response()->json(['status' => false, 'errors' => '', 'message' => "You have no permission to update the company"]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $user = auth()->user();
        if ($user->can('update_company')) {
            $this->company->delete($request->id);
            return response()->json([
                'status' => true,
                'data' => [],
                'errors' => '', 
                'message' => "The vendor has been successfully deleted",
            ]);
        }else{  
            return response()->json(["status"=> false,"errors"=> "permission", "message"=> "You have no permission to delete the company"]);
        }
    }
}
