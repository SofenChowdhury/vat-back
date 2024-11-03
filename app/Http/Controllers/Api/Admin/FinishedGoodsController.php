<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\FinishedGoodsRepository;
use Illuminate\Support\Facades\Auth;

class FinishedGoodsController extends Controller
{
    protected $goods;
    protected $stock;

    public function __construct(FinishedGoodsRepository $goods)
    {
        $this->goods = $goods;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index()
    {
        $goods =  $this->goods->getAll(auth()->user()->company_id);
        return response()->json([
            'status' => true,
            'data' => $goods,
            'errors' => '', 
            'message' => "goods List Loaded",
        ]);
    }

    public function search(Request $request)
    {
        return $this->goods->search($request);
    }

    public function goodsDownload(Request $request)
    {
        return $this->goods->goodsDownload($request);
    }

    public function create(Request $request)
    {
        return $this->goods->create($request);
    }

    public function show($id)
    {
        $goodsDetails = $this->goods->getById($id);
        return response()->json([
            'status' => true,
            'data' => $goodsDetails,
            'errors' => '', 
            'message' => "Purchase list has been loaded",
        ]);
    }

    public function dateWiseReport(Request $request)
    {
        $user = auth::user();
        $goods =  $this->goods->getFull($request, $user->company_id);
        return response()->json([
            'status' => true,
            'data' => $goods,
            'errors' => '', 
            'message' => "goods List Loaded",
        ]);
    }

     /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */

    
    function removeItem(Request $request) {
        if (auth()->user()->can('remove_finished_goods_rm')) {
            return $this->goods->removeConsumptionItem($request);
        }else{
            return response()->json(
                [
                    'status' => false, 
                    'data' => [],
                    'errors' => '', 
                    'message' => "You have no permission to remove item consumption from this FG Receive"
                ]);
        }

    }

    public function destroy(Request $request)
    {
        // if (auth()->user()->can('delete_finished_goods_received')) {
            return $this->goods->delete($request->id);
        // }else{
        //     return response()->json(
        //         [
        //             'status' => false, 
        //             'data' => [],
        //             'errors' => '', 
        //             'message' => "You have no permission to delete this Finished Goods Receive"
        //         ]);
        // }
    }
    public function guard()
    {
        return Auth::guard('api');
    }
}
