<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Admin;
use App\Classes\Helper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use App\Repositories\ActivityLogsRepository;

class ActivityLogsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


     protected $activity;
    public function __construct(ActivityLogsRepository $activity )
    {
        $this->activity  = $activity;
    }
    public function search(Request $request)
    {
        $user = auth()->user();
        $path = app_path() . "/Models";
        if ($user->can('show_activity_logs')) {
            $user = auth()->user();
            $lastActivity = $this->activity->search($request, $user->company_id); //returns the last logged activity
            $options['module'] = $this->getAllModels();
            $options['actions'] = ["Created", "Updated", "Deleted", "Printed"];
            return response()->json([
                'status' => true,
                'search_options' => $options,
                'data' => $lastActivity,                
                'errors' => '', 
                'message' => "activity details loaded",
            ]);
        }else{
            return response()->json(['status' => false, 'errors' => 'permission', 'data' => [], 'message' => "You have no permission to show this user"]);
        }

    }

    public function getAllModels()
    {
        $modelList = [];
        $path = app_path() . "/Models";
        $results = scandir($path);
 
        foreach ($results as $result) {
            if ($result === '.' or $result === '..') continue;
            $filename = $result;
  
            if (is_dir($filename)) {
                $modelList = array_merge($modelList, getModels($filename));
            }else{                
                $modelList[] = substr($filename,0,-4);
            }
        }
  
        return $modelList;
    }

    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // $activity = Activity::find($id)->subject;
        
        $user = auth()->user();
        if ($user->can('show_activity_logs')) {
            $activity =  $this->activity->getById($id);
            $activity->subject;
            $activity->causer;
            return response()->json([
                'status' => true,
                'data' => $activity,
                'errors' => '', 
                'message' => "Activity details loaded",
            ]);
        }else{
            return response()->json(['status' => false, 'errors' => 'permission', 'data' => [], 'message' => "You have no permission to show this user"]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return false;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return false;
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return false;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return false;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return false;
    }
}
