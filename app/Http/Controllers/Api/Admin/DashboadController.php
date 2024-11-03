<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ShopsRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\ProductRepository;

class DashboadController extends Controller
{
    //
    protected $order;
    protected $company;
    protected $shop;
    protected $user;
    protected $product;
    public function __construct(OrderRepository $order, CompanyRepository $company, ShopsRepository $shop, UserRepository $user, ProductRepository $product)
    {
         $this->order = $order;
         $this->company = $company;
         $this->shop = $shop;
         $this->user = $user;
         $this->product = $product;
    }
    

    public function index(Request $request)
    {
        $companies = 0;
        $products = [];
        $orders = $this->order->getAll(auth()->user()->company_id);
        if (auth()->user()->can('show company')) {
            $companies = $this->company->getAll()->count();
        }
        $shops = $this->shop->getPageTen(auth()->user()->company_id); 
        $customers = $this->user->getPageTen(auth()->user()->company_id);
        if (auth()->user()->can('show product')) {
            $products = $this->product->getPageTen();
        }
        return Inertia::render('Dashboard', compact('orders', 'companies', 'shops', 'customers', 'products'));
    }
}
