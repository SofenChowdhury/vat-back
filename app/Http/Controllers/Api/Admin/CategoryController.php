<?php
namespace App\Http\Controllers\Api\Admin;

use Inertia\Inertia;
use App\Http\Controllers\Controller;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class CategoryController extends Controller
{
    protected $category;

    public function __construct(CategoryRepository $category)
    {
         $this->category = $category;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index()
    {
        $categories =  $this->category->getAll();
        return response()->json([
            'status' => true,
            'data' => $categories,
            'errors' => '', 
            'message' => "You have no permission to update user",
        ]);
    }


   /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:20'
        ]);
        $category = $this->category->create($request);
        return response()->json([
            'status' => true,
            'data' => $category,
            'errors' => '', 
            'message' => $request->name." Category has been successfully created",
        ]);

    }
    
    
    public function categoryEdit($uuid)
    {
        $category = $this->category->getByUuid($uuid);
        return response()->json($category);
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
        $request->validate([
            'name' => 'required|string|max:20'
        ]);
        $category = $this->category->update($request->id, $request);
        return response()->json([
            'status' => true,
            'data' => $category,
            'errors' => '', 
            'message' => $request->name." Category has been successfully Updated",
        ]);
    }

    public function show($id)
    {
        $category = $this->category->getById($id);
        return response()->json([
            'status' => true,
            'data' => $category,
            'errors' => '', 
            'message' => "Category has been successfully loaded",
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy(Request $request)
    {
        $this->series->delete($request->id);
        return response()->json([
            'status' => true,
            'data' => [],
            'errors' => '', 
            'message' => "The category has been successfully deleted",
        ]);

    }


    public function categoryProduct($slug)
    {        
        return  $this->category->productBycategory($slug);
    }
}
