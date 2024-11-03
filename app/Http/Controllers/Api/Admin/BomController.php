<?php
namespace App\Http\Controllers\Api\Admin;

use Inertia\Inertia;
use App\Models\Payment;
use App\Http\Requests\OrderRequest;
use App\Http\Controllers\Controller;
use App\Repositories\BomRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BomController extends Controller
{
    protected $bom;
    protected $company;
    protected $admin;
    protected $branch;
    protected $setting;
    protected $user;

    public function __construct(BomRepository $bom)
    {
         $this->bom   = $bom;
         $this->user = auth()->user();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */

    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user->can('show_bom')) {
            $boms = $this->bom->search($request);
            return response()->json([
                'status' => true,
                'data' => $boms,
                'errors' => '', 
                'message' => "BOM List has been loaded",
            ]);
        }else{
            return response()->json(['status' => false, 'errors'=> 'permission', 'message'=> 'You have no permission to show the BOM']);

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
        if ($user->can('create_bom')) {
            $bom = $this->bom->create($request);
            return response()->json($bom);
        }else{
            return response()->json(['status'=> false,'errors'=> 'permission', 'message' => "You have no permission to create the BOM"]);
        }
    }

    public function bulkUpload(Request $request){
        $rules = [
            'product_id' => 'required',
            'csvfile'      => 'required|mimes:csv,txt',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        $sales = $this->bom->bulkUpload($request);
        return response()->json([
            'status' => true,
            'data' => $sales,
            'errors' => '', 
            'message' => "Purchases List Loaded",
        ]);
    }

    function bulkUploadCreate(Request $request)
    {
        $user = auth()->user();
        if ($user->can('create_bom')) {
            $data = $this->bom->bulkUploadRmCreate($request);
            return response()->json($data);
        }else{
            return response()->json(['status'=> false,'errors'=> 'permission', 'message' => "You have no permission to create the BOM"]);
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
        if ($user->can('show_bom')) {
            $bom = $this->bom->getById($id);
            return response()->json([
                'status' => true,
                'data' => $bom,
                'errors' => '', 
                'message' => "BOM List Loaded",
            ]);
        }else{
            return response()->json(['status'=> false,'errors'=> 'permission','message'=> 'You have no permission to show the BOM']);
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */

    public function print($id)
    {
        $order = $this->order->getShowById($id);;
        $setting = $this->setting->getAll();
        return view('admin/order/print', compact('order', 'setting'));
    }

   /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function edit($id)
    {
        
        $order = $this->order->getById($id);
        return view('order.edit', compact('order'));
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
        if ($user->can('create_bom')) {
            $response = $this->bom->update($request->bom_id, $request);
            return response()->json($response);
        }else{
            return response()->json(['status'=> false,'errors'=> 'permission','message'=> 'You have no permission to Update the BOM']);
        }
    }

    function reportHistory(Request $request) {
        $user = auth()->user();
        if ($user->can('show_bom_history_report')) {
            return $this->bom->reportHistory($request, $this->user->company_id);
        }else{
            return response()->json(['status'=> false, 'errors' => 'permission','message'=> 'You have no permission to show the BOM history report']);
        }
    }
}
