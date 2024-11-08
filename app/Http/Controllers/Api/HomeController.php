<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\ShopsRepository;
// use App\Repositories\SliderRepository;
use App\Repositories\ThanaRepository;
use App\Repositories\ProductRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\DistrictRepository;

class HomeController extends Controller
{
    //
   
    //    protected $slider;
   protected $product;
   protected $district;
   protected $thana;
   protected $shop;
   protected $category;

    // public function __construct(SliderRepository $slider, ProductRepository $product, DistrictRepository $district, ThanaRepository $thana, ShopsRepository $shop)
    public function __construct(ProductRepository $product, DistrictRepository $district, ThanaRepository $thana, ShopsRepository $shop,
    CategoryRepository $category)
     {
        //  $this->slider=$slider;
         $this->product=$product;
         $this->district = $district;
         $this->thana = $thana;
         $this->shop = $shop;
         $this->category = $category;
     }

     public function  index()
     {
        //  $data['sliders']= $this->slider->getSlider($limit=5);
        //  $data['categories']=$this->product->getCategory();

         $data['categories']=$this->category->categoryProduct($limit=5);
         $data['products']=$this->product->getProduct($limit=20);
         $data['populars']=$this->product->popularProduct($limit=5);
         $data['featureds']=$this->product->featuredProduct($limit=5);
         $data['featureds']=$this->product->featuredProduct($limit=5);
       
         return response()->json(['success'=>true, 'data'=>$data]);    
     }

    public function district($division_id = NULL)
    {
        $districts = $this->district->getByDivisionID($division_id);
        return response()->json([
            "status" => true,
            "districts" => $districts
        ]);
        
    }

    public function thana($district_id = NULL)
    {
        $thanas = $this->thana->getByDistrictID($district_id);
        return response()->json([
            "status" => true,
            "thanas" => $thanas
        ]);        
    }

    public function shop($thana_id = NULL)
    {
        $shops = $this->shop->getBThanaID($thana_id);
        return response()->json([
            "status" => true,
            "shops" => $shops
        ]);        
    }

    public function category()
    {
     
          $categories= Category::get();

          return response()->json([
            "status" => true,
            "categories" => $categories
        ]);
    }
     
}
