<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TicketRequest;

//repositories
use App\Repositories\TicketRepository;
use Illuminate\Http\Request;

class TicketController extends Controller
{

    protected $admin;
    protected $ticket;

    public function __construct(TicketRepository $ticket)
    {
        
        $this->ticket   = $ticket;
    }

    public function index()
    {
        $tickets = $this->ticket->getAll();

        return response()->json([
            'data' => $tickets,
            'status' => true,
            'message' => "Ticket List Loaded"
        ], 200);
    }

    public function store(TicketRequest $request)
    {
        return $this->ticket->create($request);
    }

    public function show($id)
    {
        return $this->ticket->show($id);
    }

    public function update(TicketRequest $request)
    {
        return $this->ticket->update($request->id, $request);
    }

    public function delete(Request $request)
    {
        return $this->ticket->delete($request->id);
    }

    public function assign(Request $request)
    {
        return $this->ticket->assign($request);
    }
}