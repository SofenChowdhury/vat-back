<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PurchaseRequest;
use App\Repositories\PurchaseRepository;

class PurchaseController extends Controller
{
    protected $purchase;

    public function __construct(PurchaseRepository $purchase)
    {
        $this->purchase = $purchase;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index()
    {
        $user = auth()->user();
        if ($user->can('show_purchase')) {
            $purchases =  $this->purchase->getAll();
            return response()->json([
                'status' => true,
                'data' => $purchases,
                'errors' => '', 
                'message' => "Purchases List Loaded",
            ]);
        }else{
            return response()->json(['status' => false, 'errors' => 'permission', 'message' => "You have no permission to show the purchase"]);
        } 
        
    }

    public function returnList()
    {
        $user = auth()->user();
        if ($user->can('show_purchase')) {
            $purchasesReturn =  $this->purchase->returnList(auth()->user()->company_id);
            return response()->json([
                'status' => true,
                'data' => $purchasesReturn,
                'errors' => '', 
                'message' => "Purchases Return List Loaded",
            ]);
        }else{
            return response()->json(['status' => false, 'errors' => 'permission', 'message' => "You have no permission to show the return list"]);
        } 
        
    }

    public function returnDownload()
    {
        $user = auth()->user();
        if ($user->can('show_purchase')) {
            $purchasesReturn =  $this->purchase->returnDownload($user->company_id);
            return response()->json([
                'status' => true,
                'data' => $purchasesReturn,
                'errors' => '', 
                'message' => "Purchases Return List Loaded",
            ]);
        }else{
            return response()->json(['status' => false, 'errors' => 'permission', 'message' => "You have no permission to show the return list"]);
        }
        
    }

    public function returnEntryManualBulk(Request $request)
    {
        $purchasesReturn =  $this->purchase->manualPurchaseReturnBulk($request);
        return response()->json([
            'status' => true,
            'data' => $purchasesReturn,
            'errors' => '', 
            'message' => "Purchases Return List Loaded",
        ]);
    }

    public function returnEntryManual(Request $request)
    {
        $purchasesReturn =  $this->purchase->manualPurchaseReturn($request);
        return $purchasesReturn;
    }

    public function create(PurchaseRequest $request)
    {
        $user = auth()->user();
        if ($user->can('create_purchase')) {
            $purchaseInfo = $this->purchase->create($request);
            return response()->json([
                'status' => true,
                'data' => $purchaseInfo,
                'errors' => '', 
                'message' => "A new purchase has been successfully created",
            ]);
        }else{
            return response()->json(['status' => false, 'errors' => 'permission', 'message' => "You have no permission to create the purchase"]);
        }        
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        if ($user->can('create_purchase')) {
            $purchaseInfo = $this->purchase->update($request->id, $request);
            return response()->json([
                'status' => true,
                'data' => $purchaseInfo,
                'errors' => '', 
                'message' => "A new purchase has been successfully created",
            ]);
        }else{
            return response()->json(['status' => false, 'errors' => 'permission', 'message' => "You have no permission to create the purchase"]);
        }
        
    }

    public function createBulk(Request $request)
    {
        $purchaseInfo = $this->purchase->createBulk($request);
        return response()->json([
            'status' => true,
            'data' => $purchaseInfo,
            'errors' => '', 
            'message' => "A new purchase has been successfully created",
        ]);
    }

    public function return(Request $request)
    {        
        $user = auth()->user();
        if ($user->can('create_purchase_return')) {
            return $this->purchase->purchaseReturn($request);
        }else{
            return response()->json(['status' => false, 'errors' => 'permission', 'message' => "You have no permission to create the purchase"]);
        }
    }

    public function returnUpdate(Request $request)
    {        
        $user = auth()->user();
        if ($user->can('create_purchase_return')) {
            return $this->purchase->updateReturn($request);
        }else{
            return response()->json(['status' => false, 'errors' => 'permission', 'message' => "You have no permission to create the purchase"]);
        }
    }

    public function returnSubForm(Request $request)
    {
        $user = auth()->user();
        if ($user->can('show_purchase_return')) {
            $returnList = $this->purchase->returnSubForm($request);
            return response()->json([
                'status' => true,
                'data' => $returnList,
                'errors' => '', 
                'message' => "Purchase return list has been loaded",
            ]);
        }else{
            return response()->json(['status' => false, 'errors' => 'permission', 'message' => "You have no permission to create the purchase"]);
        }
        
    }

    public function purchaseSubForm(Request $request)
    {
       return $this->purchase->purchaseSubForm($request, auth()->user()->company_id);
        
    }
    

    public function show($id)
    {
        $user = auth()->user();
        if ($user->can('show_purchase')) {
            $purchaseDetails = $this->purchase->getById($id);
            return response()->json([
                'status' => true,
                'data' => $purchaseDetails,
                'errors' => '', 
                'message' => "Purchase list has been loaded",
            ]);
        }else{
            return response()->json(['status' => false, 'errors' => 'permission', 'message' => "You have no permission to show the purchase"]);
        }
        
    }

    public function returnShow($id)
    {
        $user = auth()->user();
        if ($user->can('show_purchase_return')) {
            $returnedDetails = $this->purchase->getReturnById($id);
            return response()->json([
                'status' => true,
                'data' => $returnedDetails,
                'errors' => '', 
                'message' => "Purchase return has been loaded",
            ]);
        }else{
            return response()->json(['status' => false, 'errors' => 'permission', 'message' => "You have no permission to show the purchase return"]);
        }
        
    }

    // Purchase search
    public function search(Request $request)
    {
        $user = auth()->user();
        return $this->purchase->search($request, $user);
    }

    public function download(Request $request){
        return $this->purchase->download($request, auth()->user());
    }

    // Mushok 6.1
    public function mushok_six_one(Request $request)
    {
        $purchases =  $this->purchase->mushok_six_one($request);        
        return response()->json([
            'status' => true,
            'data' => $purchases,
            'errors' => '', 
            'message' => "Item Purchase Loaded",
        ]);
    }

     // Mushok 6.1
     public function mushok_six_two(Request $request)
     {
         $purchases =  $this->purchase->mushok_six_two($request);        
         return response()->json([
             'status' => true,
             'data' => $purchases,
             'errors' => '', 
             'message' => "Item Purchase Loaded",
         ]);
     }

    public function debitNote($challan)
    {
        $returnedDetails = $this->purchase->getReturnByChallan($challan);
        return response()->json([
            'status' => true,
            'data' => $returnedDetails,
            'errors' => '', 
            'message' => "Purchase return has been loaded",
        ]);
    }

    function addItem(Request $request) {
        if (auth()->user()->can('add_purchase_item')) {
            return $this->purchase->addPurchaseItem($request);
        }else{
            return response()->json(
                [
                    'status' => false, 
                    'data' => [],
                    'errors' => '', 
                    'message' => "You have no permission to add item in this purchase"
                ]);
        }
        
    }

    function removeItem(Request $request) {
        if (auth()->user()->can('remove_purchase_item')) {
            return $this->purchase->removePurchaseItem($request);
        }else{
            return response()->json(
                [
                    'status' => false, 
                    'data' => [],
                    'errors' => '', 
                    'message' => "You have no permission to remove item from this purchase"
                ]);
        }
        
    }

    

    function updateItem(Request $request) {
        if (auth()->user()->can('update_purchase_item')) {
            return $this->purchase->updateItem($request);
        }else{
            return response()->json(
                [
                    'status' => false, 
                    'data' => [],
                    'errors' => '', 
                    'message' => "You have no permission to update item on this purchase"
                ]);
        }
        
    }

    function comparisonReport(Request $request) {
        $user = auth()->user();
        return $this->purchase->comparisonReport($request, $user->company_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function destroy(Request $request)
     {
         if (auth()->user()->can('delete_purchase')) {
             return $this->purchase->delete($request->id);
         }else{
             return response()->json(
                 [
                     'status' => false, 
                     'data' => [],
                     'errors' => '', 
                     'message' => "You have no permission to delete this purchase"
                 ]);
         }
     }

     /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */

    public function guard()
    {
        return Auth::guard('api');
    }

    function vendorStatement(Request $request) {
        $user = auth()->user();
        return $this->purchase->vendorStatement($request, $user->company_id);
    }

    function productPurchaseStatement(Request $request) {
        $user = auth()->user();
        return $this->purchase->productPurchaseStatement($request, $user->company_id);
    }
}
