<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;

use App\Repositories\TicketRepository;

use Illuminate\Http\Request;

class CommentController extends Controller
{

    protected $ticket;

    public function __construct(TicketRepository $ticket)
    {
        $this->ticket   = $ticket;
    }
    
    public function index()
    {
        return $this->ticket->getComments($request);
    }

    
    public function store(Request $request)
    {
        return $this->ticket->createComment($request);
    }
    
    public function getComments($ticket_id) {
        return $this->ticket->getComments($ticket_id);
    }

    
    public function show(Comment $comment)
    {
        //
    }

    
    public function update(Request $request, Comment $comment)
    {
        //
    }

    
    public function destroy(Comment $comment)
    {
        //
    }
}