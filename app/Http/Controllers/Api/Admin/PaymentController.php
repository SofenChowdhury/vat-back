<?php
namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Payment;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Repositories\CompanyRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\ShopsRepository;
use Illuminate\Support\Facades\Redirect;

class PaymentController extends Controller
{
    protected $payment;
    protected $company;
    protected $shop;

    public function __construct(PaymentRepository $payment, CompanyRepository $company, ShopsRepository $shop)
    {
         $this->payment = $payment;
         $this->company = $company;
         $this->shop = $shop;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index()
    {
        if (!auth()->user()->can('show payment')) {
            return Redirect::route('dashboard')->with('error', 'You have no permission to show the payments');
        }
        $companies = [];
        if (auth()->user()->can('show company')) {
            $companies = $this->company->getAll();
        }
        $shops = $this->shop->getAll(auth()->user()->company_id);
        $payments=  $this->payment->getAll(auth()->user()->company_id);
        return Inertia::render('Payment/Index', compact('payments', 'companies', 'shops'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function create()
    {
        if (!auth()->user()->can('create payment')) {
            return Redirect::route('dashboard')->with('error', 'You have no permission to create the payments');
        }
        $companies = [];
        if (auth()->user()->can('show company')) {
            $companies = $this->company->getAll();
        }
        $shops = $this->shop->getAll(auth()->user()->company_id);
        $payments=  $this->payment->getAll(auth()->user()->company_id);
        return Inertia::render('Payment/Create', compact('payments', 'companies', 'shops'));
    }


   /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(PaymentRequest $request)
    {
        if (!auth()->user()->can('create payment')) {
            return Redirect::route('dashboard')->with('error', 'You have no permission to create the payments');
        }
        $payment = $this->payment->create($request->all());

       return back();

    }

   /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */

     public function show($id)
    {
        if (!auth()->user()->can('show payment')) {
            return Redirect::route('dashboard')->with('error', 'You have no permission to show the payments');
        }
        $payment = $this->payment->getById($id);
        return Inertia::render('Payment/Show', compact('payment'));
    }

   /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function edit($id)
    {
        if (!auth()->user()->can('edit payment')) {
            return Redirect::route('dashboard')->with('error', 'You have no permission to edit the payments');
        }
        $payment = $this->payment->getById($id);
        
        return view('payment.edit', compact('payment'));
    }

   /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(PaymentRequest $request, $id)
    {
        if (!auth()->user()->can('edit payment')) {
            return Redirect::route('dashboard')->with('error', 'You have no permission to edit the payments');
        }
        $payment=$this->payment->update($id, $request->all());
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function approve(PaymentRequest $request)
    {
        if (!auth()->user()->can('approve deny payment')) {
            return Redirect::route('dashboard')->with('error', 'You have no permission to edit the payments');
        }
        $this->payment->approveDeny($request->id, $request);
        return back();
    }

    

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        if (!auth()->user()->can('delete payment')) {
            return Redirect::route('dashboard')->with('error', 'You have no permission to delete the payments');
        }
        $this->payment->delete($id);
        return back();

    }
}
