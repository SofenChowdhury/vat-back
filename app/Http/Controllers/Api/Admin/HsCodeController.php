<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\HsCodeRepository;
use Illuminate\Support\Facades\Auth;

class HsCodeController extends Controller
{
    protected $hscode;

    public function __construct(HsCodeRepository $hscode)
    {
        $this->hscode = $hscode;
    }

    public function index(Request $request)
    {
        $hscodes = $this->hscode->getAllWithSearch($request);
        return response()->json([
            'status' => true,
            'data' => $hscodes,
            'errors' => '', 
            'message' => "HS code List Loaded",
        ]);
    }

    public function advanceSearch(Request $request)
    {
        $hscode = $this->hscode->getAllWithSearch($request);
        return response()->json([
            'status' => true,
            'data' => $hscode,
            'errors' => '', 
            'message' => "hscode List Loaded",
        ]);
    }

    public function search(Request $request)
    {
        $hscode = $this->hscode->search($request);
        return response()->json([
            'status' => true,
            'data' => $hscode,
            'errors' => '', 
            'message' => "hscode List Loaded",
        ]);
    }

    public function getById($id)
    {
        $hscode = $this->hscode->getById($id);
        return response()->json([
            'status' => true,
            'data' => $hscode,
            'errors' => '', 
            'message' => "hscode List Loaded",
        ]);
    }

    public function getByCode($code)
    {
        $hscode = $this->hscode->getByCode($code);
        return response()->json([
            'status' => true,
            'data' => $hscode,
            'errors' => '', 
            'message' => "hscode List Loaded",
        ]);
    }

    public function download(Request $request)
    {
        $hsCodes = $this->hscode->download($request);
        return response()->json([
            'status' => true,
            'data' => $hsCodes,
            'errors' => '', 
            'message' => "hscode List Loaded",
        ]);
    }
}
