<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\ValueAdditionHeadRepository;
use Illuminate\Http\Request;

class ValueAdditionHeadController extends Controller
{
    protected $values;

    public function __construct(ValueAdditionHeadRepository $values)
    {
        $this->values = $values;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index()
    {
        $values =  $this->values->getAll();
        return response()->json([
            'status' => true,
            'data' => $values,
            'errors' => '', 
            'message' => "Value Addition Heads has been loaded",
        ]);
    }

    public function create(Request $request)
    {
        return $this->values->create($request);
    }

    public function update(Request $request)
    {
        return $this->values->update($request->id, $request);
    }

    public function show($id)
    {
        $valueDetails = $this->values->getById($id);
        return response()->json([
            'status' => true,
            'data' => $valueDetails,
            'errors' => '', 
            'message' => "Value Addition Head has been loaded",
        ]);
    }

    public function destroy(Request $request)
    {
        $this->values->delete($request->id);
        return response()->json([
            'status' => true,
            'data' => [],
            'errors' => '', 
            'message' => "Value Addition Head has been successfully deleted",
        ]);
    }
}
