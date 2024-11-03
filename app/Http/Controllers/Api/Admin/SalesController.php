<?php
namespace App\Http\Controllers\Api\Admin;

use Inertia\Inertia;
use App\Models\Payment;
use App\Http\Requests\OrderRequest;
use App\Http\Controllers\Controller;
use App\Repositories\AdminRepository;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\SalesRepository;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    protected $sales;
    protected $company;
    protected $admin;
    protected $branch;
    protected $setting;

    public function __construct(SalesRepository $sales, CompanyRepository $company, AdminRepository $admin, BranchRepository $branch)
    {
         $this->sales   = $sales;
         $this->company = $company;
         $this->admin   = $admin;
         $this->branch    = $branch;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */

    public function index()
    {
        $user = auth()->user();
        if ($user->can('show_sales')) {
            $sales = [];
            if ($user->company_id) {
                $sales =  $this->sales->getAll($user->company_id);
            }else{
                $sales =  $this->sales->getAll();
            }
            return response()->json([
                'status' => true,
                'data' => $sales,
                'errors' => '', 
                'message' => "Sales List Loaded",
            ]);
        }else {
            return response()->json([
                'status' => false,
                'data' => [],
                'errors' => '', 
                'message' => "You have no permission to show the sales",
            ]);
        }
        
        
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */

    public function search(Request $request)
    {
        $user = auth()->user();
        if ($user->can('show_sales')) {

            $user = auth()->user();
            $sales = [];
            if ($user->company_id) {
                $sales =  $this->sales->search($request, $user);
            }else {
                $sales =  $this->sales->search($request);
            }
            return response()->json([
                'status' => true,
                'data' => $sales,
                'errors' => '', 
                'message' => "Sales List Loaded",
            ]);
        }else {
            return response()->json(["status" => false,"data"=> [],"errors"=> "permission","message"=> "You have no permission to show the sales"]);
        }
    }

    public function download(Request $request)
    {
        $user = auth()->user();
        if ($user->can('download_sales')) {
            $user = auth()->user();
            $sales = [];
            if ($user->company_id) {
                $sales =  $this->sales->download($request, $user);
            }else {
                $sales =  $this->sales->download($request);
            }
            return response()->json([
                'status' => true,
                'data' => $sales,
                'errors' => '', 
                'message' => "Sales List Loaded",
            ]);
        }else {
            return response()->json(["status"=> false,"data"=> [],"errors"=> "You have no permission to Download the"]) ;
        }
    }


   /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function create(OrderRequest $request)
    {
        $user = auth()->user();
        if ($user->can('create_sales')) {
            return $orderResponse = $this->sales->create($request);
            return response()->json([
                'status' => $orderResponse['status'],
                'data' => $orderResponse['data'],
                'errors' => '', 
                'message' => $orderResponse['message']
            ]);
        }else {
            return response()->json(['status' => false,'data'=> [],'errors'=> 'You have no permission to create the sales']) ;
        }
        
    } 

    public function contractualDelivery(OrderRequest $request)
    {
        $user = auth()->user();
        if ($user->can('create_sales')) {
            $orderResponse = $this->sales->create($request);
            return response()->json([
                'status' => $orderResponse['status'],
                'data' => $orderResponse['data'],
                'errors' => '', 
                'message' => $orderResponse['message']
            ]);
        }else {
            return response()->json(['status' => false,'data'=> [],'errors'=> 'You have no permission to create the sales']) ;
        }
        
    } 

    

    

    public function branchBulkUpload(Request $request)
    {
        $user = auth()->user();
        if ($user->can('create_sales_bulk')) {
            return $this->sales->branchBulkUpload($request);
        }else {
            return response()->json(['status' => false,'data'=> [],'errors'=> 'You have no permission to create the bulk sales']) ;
        }
    }

    

    public function rollBack(Request $request)
    {
        return $this->sales->rollBackOrder($request);
    }

    public function regularBulkUpload(Request $request)
    {
        return $this->sales->regularBulkUpload($request);
    }

     /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function returnList()
    {
        $user = auth()->user();
        return $this->sales->returnList($user->company_id);
    }

    public function returnSubForm(Request $request)
    {
        $returnedList = $this->sales->returnSubForm($request);
        return response()->json([
            'status' => true,
            'data' => $returnedList,
            'errors' => '', 
            'message' => "Sales return has been loaded",
        ]);
    }

    public function returnShow($id)
    {
        $returnedDetails = $this->sales->getReturnById($id);
        return response()->json([
            'status' => true,
            'data' => $returnedDetails,
            'errors' => '', 
            'message' => "Sales return has been loaded",
        ]);
    }

    public function return(Request $request)
    {
        return $this->sales->salesReturn($request);
    }

    public function returnDownload(Request $request) {
        $company_id = $request->company_id? $request->company_id: auth()->user()->company_id;
        return $this->sales->returnDownload($request, $company_id);
    }

    public function manualCreditNote(Request $request)
    {
        return $this->sales->manualCreditNote($request);
    }

   /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */

    public function show($id)
    {
        $sales = $this->sales->getById($id);
        return response()->json([
            'status' => true,
            'data' => $sales,
            'errors' => '', 
            'message' => "Sales List Loaded",
        ]);
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
        if (auth()->user()->can('update_sales')) {
            return $this->sales->salesUpdate($request);
        }else{
            return response()->json(
                [
                    'status' => false, 
                    'data' => [],
                    'errors' => '', 
                    'message' => "You have no permission to update the sales"
                ]);
        }
    }

    function updateItem(Request $request) {
        if (auth()->user()->can('update_sales_item')) {
            return $this->sales->updateItem($request);
        }else{
            return response()->json(
                [
                    'status' => false, 
                    'data' => [],
                    'errors' => '', 
                    'message' => "You have no permission to update the sales item"
                ]);
        }
        
    }

    function addSalesItem(Request $request) {
        if (auth()->user()->can('add_sales_item')) {
            return $this->sales->addSalesItem($request);
        }else{
            return response()->json(
                [
                    'status' => false, 
                    'data' => [],
                    'errors' => '', 
                    'message' => "You have no permission to add the sales item"
                ]);
        }
    }

    public function itemRemove(Request $request) {
        if (auth()->user()->can('remove_sales_item')) {
            return $this->sales->removeSalesItem($request);
        }else{
            return response()->json(
                [
                    'status' => false, 
                    'data' => [],
                    'errors' => '', 
                    'message' => "You have no permission to remove the sales item"
                ]);
        }
    }

    public function print(Request $request)
    {
        $sales = $this->sales->getById($request->id);
        $sales->printed += 1;
        $sales->update();
        return response()->json($sales);
    }

    public function orderPayment($id)
    {
        $payments = Payment::where('order_id', $id)->get();
        return  response()->json($payments);
        // return $payments;
        return Inertia::render('Order/Payments', compact('payments'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy(Request $request)
    {
        if (auth()->user()->can('delete_sales')) {
            return $this->sales->delete($request);
        }else{
            return response()->json(
                [
                    'status' => false, 
                    'data' => [],
                    'errors' => '', 
                    'message' => "You have no permission to delete this sales"
                ]);
        }
    }

    function deleteSalesBulk(Request $request) {
        // if (auth()->user()->can('delete_sales')) {
            $sales = $this->sales->deleteSalesBulk($request);
            return response()->json(
                [
                    'status' => true, 
                    'data' => $sales,
                    'errors' => '', 
                    'message' => "Sales Successfully Deleted"
                ]);
        // }else{
        //     return response()->json(
        //         [
        //             'status' => false, 
        //             'data' => [],
        //             'errors' => '', 
        //             'message' => "You have no permission to delete the sales"
        //         ]);
        // }
    }

    public function download22()
    {
        $orders =  $this->sales->getFull(auth()->user()->company_id);
        $fileName = date('d-m-Y_H:m:s').'_orders.csv';
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array(
            'Order ID',
            'Order No.', 
            'Order Date', 
            'Order Time',
            'Company Name', 
            'Branch Name',
            'Customer Name',
            'Customer Mobile',  
            'Customer Email',
            'Order Status',
            'Item SKU',
            'Item Name',
            'Selling Price',
            'Item Qty',
            'Total Price',
            'Paid Amount',   
            'Order Note'
        );

        $callback = function() use($orders, $columns) {
            $rowData = [];
            $sl = 0;
            foreach ($orders as $order) {           
                $sl++;
                $file = fopen('php://output', 'w');
                if ($sl<2) {
                    fputcsv($file, $columns);
                }
                $totalPaid = 0;
                foreach ($order->payments as $key => $payment) {
                    $totalPaid += $payment->amount_credit;
                }
                foreach($order->orderItem as $item){
                    $row['order_id']    = $order->id;
                    $row['order_no']    = $order->order_number;
                    $row['order_date']  = date('j M y', strtotime($order->created_at));
                    $row['order_time']  = date('h:i A', strtotime($order->created_at));
                    $row['company']     = $order->user->company->name;
                    $row['branch']      = $order->user->shop->title;
                    $row['customer_name']   = ucwords($order->customer_name);                    
                    $row['customer_phone']  = $order->customer_phone;
                    $row['customer_email']  = $order->customer_email;
                    $row['order_status']    = ucwords($order->order_status);
                    $row['item_sku']        = $item->itemInfo->sku;
                    $row['item_name']       = $item->name;
                    $row['selling_price']   = $item->item_price;
                    $row['qty']             = $item->qty;
                    $row['total_price']     = ($item->item_price*$item->qty);
                    $row['totalPaid']       = $totalPaid;
                    $row['order_note']       = $order->order_note;
                    fputcsv($file, $row);
                }
                // return $rowData;
                fclose($file);
            }
        };
        return response()->stream($callback, 200, $headers);
    }


    public function emailTemplate( $payment_id = 1)
    {
        $payment = $this->sales->getById($payment_id);
        // return $payment->orderPayments;
        // return $order->orderItems[0]->itemInfo->photo;
        return view('mail.admin.paymentPlaced', compact('payment'));
        // return $this->view('mail.admin.orderPlaced', compact('order'));
    }
    

    public function draftSales(OrderRequest $request)
    {
        $order = $this->sales->draftSales($request);
        return response()->json([
            'status' => true,
            'data' => $order,
            'errors' => '', 
            'message' => "Sales Draft has been successfully created",
        ]);
    }

    public function draftUpdate(OrderRequest $request)
    {
        $order = $this->sales->draftUpdate($request);
        return response()->json([
            'status' => true,
            'data' => $order,
            'errors' => '', 
            'message' => "Sales Draft has been successfully updated",
        ]);
    }

    public function checkMushok($challan)
    {
        $challan = $this->sales->getByChallan($challan);
        return response()->json([
            'status' => true,
            'data' => $challan,
            'errors' => '', 
            'message' => "Challan Has Been Loaded",
        ]);
    }

    public function creditNote($challan)
    {
        $challan = $this->sales->getReturnByChallan($challan);
        return response()->json([
            'status' => true,
            'data' => $challan,
            'errors' => '', 
            'message' => "Challan Has Been Loaded",
        ]);
    }

    function salesSubForm(Request $request) {
        $user = auth()->user();
        return $this->sales->salesSubForm($request, $user->company_id);
    }

    function comparisonReport(Request $request) {
        $user = auth()->user();
        return $this->sales->comparisonReport($request, $user->company_id);
    }

    function customerStatement(Request $request) {
        $user = auth()->user();
        return $this->sales->customerStatement($request, $user->company_id);
    }

    function productSalesStatement(Request $request) {
        $user = auth()->user();
        return $this->sales->productSalesStatement($request, $user->company_id);
    }

    function smsTest($id) {
        $data = $this->sales->smsTest($id);
        return response()->json($data);
    }
}
