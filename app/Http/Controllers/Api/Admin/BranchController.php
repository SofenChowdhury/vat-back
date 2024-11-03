<?php
namespace App\Http\Controllers\Api\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\BranchRequest;
use App\Repositories\AdminRepository;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\CustomerRepository;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{

    protected $branch;
    protected $company;
    protected $admin;
    protected $customer;

    public function __construct(BranchRepository $branch, CompanyRepository $company, AdminRepository $admin, CustomerRepository $customer)
    {
        $this->branch = $branch;
        $this->company = $company;
        $this->admin = $admin;
        $this->customer = $customer;        
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $company_id = auth()->user()->company_id;
        if ($request->company_id !="") {
            $company_id = $request->company_id;
        }
        $branches = $this->branch->getAll($company_id);
        return response()->json([
            'status' => true,
            'data' => $branches,
            'errors' => '', 
            'message' => "Branches List Loaded",
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        $company_id = auth()->user()->company_id;
        if ($request->company_id !="") {
            $company_id = $request->company_id;
        }
        $branches = $this->branch->getPage($company_id, $request);
        return response()->json([
            'status' => true,
            'data' => $branches,
            'errors' => '', 
            'message' => "Branches List Loaded",
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\BranchRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function create(BranchRequest $request)
    {
        $this->branch->create($request);
        return response()->json([
            'status' => true,
            'data' => [],
            'errors' => '', 
            'message' => "Branch list has been loaded",
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
        $branch = $this->branch->getById($id);
        return response()->json([
            'status' => true,
            'data' => $branch,
            'errors' => '', 
            'message' => "Branch details has been loaded",
        ]); 
    }
    

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(BranchRequest $request)
    {
        try {
            $this->branch->update($request->id, $request);
            return response()->json([
                'status' => true,
                'data' => $this->branch->getById($request->id),
                'errors' => '', 
                'message' => "Branch update has been successfully completed",
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'data' => [],
                'errors' => '', 
                'message' => $th->getMessage(),
            ]); 
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
