<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;
    public $user;

    public function __construct(Ticket $ticket, $user)
    {
        $this->ticket = $ticket;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('New Ticket Created')
            ->view('mail.admin.TicketCreated')
            ->with([
                'ticket' => $this->ticket,
                'user' => $this->user,
            ]);
    }
}