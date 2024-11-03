<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MeasurementUnit;
use App\Repositories\MeasurementRepository;
use Illuminate\Http\Request;

class MeasurementController extends Controller
{
    protected $measurement;

    public function __construct(MeasurementRepository $measurement)
    {
        $this->measurement = $measurement;
    }

    public function index(Request $request)
    {
        $measurements = $this->measurement->getAll();
        return response()->json([
            'status' => true,
            'data' => $measurements,
            'errors' => '', 
            'message' => "Measurements code List Loaded",
        ]);
    }

    public function show($id)
    {
        $hscode = $this->measurement->getById($id);
        return response()->json([
            'status' => true,
            'data' => $hscode,
            'errors' => '', 
            'message' => "Measurements List Loaded",
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:20'
        ]);
        $measurement = $this->measurement->create($request);
        return response()->json([
            'status' => true,
            'data' => $measurement,
            'errors' => '', 
            'message' => $request->name." Measurement has been successfully created",
        ]);
    }

    public function update(Request $request)
    {
        // return $request->all();
        $company_id = auth()->user()->company_id;
        $request->validate([
            'name' => 'required|unique:measurement_units,name,'.$request->id
        ]);
        $measurement = $this->measurement->update($request->id, $request);
        return response()->json([
            'status' => true,
            'data' => $measurement,
            'errors' => '', 
            'message' => $request->name." Measurement has been successfully Updated",
        ]);
    }

    public function getByCode($name)
    {
        $hscode = $this->measurement->getByName($name);
        return response()->json([
            'status' => true,
            'data' => $hscode,
            'errors' => '', 
            'message' => "Measurements List Loaded",
        ]);
    }

    public function delete(Request $request)
    {
        $id = $request->id;
        $hscode = $this->measurement->delete($id );
        return response()->json([
            'status' => true,
            'data' => $hscode,
            'errors' => '', 
            'message' => "Measurements has been deleted",
        ]);
    }
}
