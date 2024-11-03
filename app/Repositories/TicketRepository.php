<?php

namespace App\Repositories;

//classes
use App\Classes\FileUpload;

//models
use App\Models\AssignedTicket;
use App\Models\Ticket;
use App\Models\Comment;

//repositories
use App\Repositories\AdminRepository;

//mail
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketCreated;


use Mahabub\CrudGenerator\Contracts\BaseRepository;

class TicketRepository implements BaseRepository
{

    protected $model;
    protected $assigned;
    protected $admin;
    protected $comment;
    protected $file;

    public function __construct(Ticket $model, AssignedTicket $assigned, AdminRepository $admin, Comment $comment, FileUpload $fileUpload)
    {
        $this->model = $model;
        $this->assigned = $assigned;
        $this->admin = $admin;
        $this->comment = $comment;
        $this->file = $fileUpload;
    }


    public function getAll()
    {
        return $this->model::with(
            'assigned.assignedTo',
            'assigned.assignedBy',
            'comment.commentedBy',
            'submittedByUser'
        )->latest()->paginate(20);
    }


    public function getById(int $id)
    {
        return $this->model::with(
            'assigned.assignedTo',
            'assigned.assignedBy',
            'comment.commentedBy',
            'submittedByUser'
        )->find($id);
    }


    public function create($request)
    {
        try {
            if ($request->file == NULL) {
                $request->validate([
                    "file"    => "image:mime:jpg,png,jpeg,webp",
                ]);
            }

            $ticket = $this->model;
            $ticket->title = $request->title;
            $ticket->description = $request->description;
            $ticket->priority = $request->priority;
            $ticket->module = $request->module;
            $ticket->submitted_by = auth()->user()->id;
            $ticket->status = "Pending";
            $ticket->file = $this->file->base64ImgUpload($request->file, $file = "", $folder = 'tickets');
            $ticket->save();

            $user = auth()->user();

            $adminEmail = auth()->user()->email;
            Mail::to($adminEmail)->send(new TicketCreated($ticket, $user));

            return response()->json([
                'message' => 'Ticket created successfully',
                'data' => $ticket,
                'status' => true,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'data' => [],
                'status' => false,
            ], 500);
        }
    }

    public function update($id, $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer', // Validate the ID field
            ]);
            
            $ticket = $this->getById($id);

            $fileName = NULL;
            if (substr($request->file, 0, 22) == 'data:image/jpg;base64,'  ||  substr($request->file, 0, 22) == "data:image/png;base64," || substr($request->file, 0, 22) == "data:image/webp;base64" || substr($request->file, 0, 22) == "data:image/jpeg;base64") {
                if ($request->file != NULL) {
                    $fileName = $this->file->base64ImgUpload($request->file, $file = $product->file, $folder = "tickets");
                }
            } else {
                $ticket->file = $fileName ?  $fileName :  $ticket->file;
            }

            $ticket->title = $request->title;
            $ticket->description = $request->description;
            $ticket->priority = $request->priority;
            $ticket->module = $request->module;
            $ticket->status = $request->status;
        
            if ($request->file != "") {
                $ticket->file = $this->file->base64ImgUpload($request->file, $file = "", $folder = 'tickets');
            }

            $ticket->update();
            return response()->json([
                'message' => 'Ticket updated successfully',
                'data' => $ticket,
                'status' => true,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'data' => [],
                'status' => false,
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $ticket = $this->getById($id);
            if (!empty($ticket)) {
                $ticket->delete();
                return response()->json([
                    'message' => 'Ticket deleted successfully',
                    'status' => true,
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Ticket not found!',
                    'status' => false,
                ], 500);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'data' => [],
                'status' => false,
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $ticket = $this->getById($id);

            if (empty($ticket)) {
                return response()->json([
                    'message' => 'Ticket details not found',
                    'data' => [],
                    'status' => false,
                ], 404);
            }

            return response()->json([
                'message' => 'Ticket details found',
                'data' => $ticket,
                'status' => true,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'data' => [],
                'status' => false,
            ], 500);
        }
    }

    public function assign($request)
    {
        try {
            $request->validate([
                'ticket_id' => 'required|integer', // Validate the ticket ID
                'assigned_to_id' => 'required|integer', // Validate the admin ID
                'deadline' => 'required', // Validate the deadline
            ]);

            $ticket = $this->findOrFail($request->ticket_id);
            $adminData = $this->admin->findOrFail($request->assigned_to_id);

            if (empty($ticket)) {
                return response()->json(
                    [
                        'message' => 'Ticket not found!',
                        'data' => null,
                        'status' => false
                    ],
                    404
                );
            }
            if (empty($adminData)) {
                return response()->json(
                    [
                        'message' => 'User not found!',
                        'data' => null,
                        'status' => false
                    ],
                    404
                );
            }

            $assigned = $this->assigned::where('ticket_id', $ticket->id)->first();
            if (!empty($assigned)) {
                $ticket->status = $request->status;
                $ticket->update();
            }else{
                $assigned = new $this->assigned;
                $assigned->ticket_id = $ticket->id;
                $assigned->assigned_to_id = $adminData->id;
                $assigned->assigned_by_id = auth()->user()->id;
                $assigned->deadline = date("Y-m-d H:i:s", strtotime($request->deadline));
                $assigned->save();
                $ticket->status = "Assigned";
                $ticket->update();
            }
            
            return response()->json(
                [
                    'message' => 'Admin assigned to ticket successfully',
                    'data' => $assigned,
                    'status' => true
                ],
                200
            );
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'data' => [],
                'status' => false,
            ], 500);
        }
    }

    public function findOrFail($id)
    {
        try {
            $ticket = $this->model->find($id);
            return $ticket;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /* =========== COMMENT METHODS ================ */
    public function createComment($request)
    {
        try {
            $comment = $this->comment;
            $comment->message = $request->message;
            $comment->ticket_id = $request->ticket_id;
            $comment->commented_by_id = auth()->user()->id;
            $comment->save();
            return response()->json([
                'message' => 'Your comment has been added!',
                'data' => $comment,
                'status' => true,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'data' => [],
                'status' => false,
            ], 500);
        }
    }

    /* ===========GET TICKET COMMENTS BY AHASAN ================ */
    public function getComments($ticket_id)
    {
        try {
            $comment = $this->comment;
            $comments = $comment::with(['ticket', 'commentedBy'])->where('ticket_id', $ticket_id)->get();
            return response()->json([
                'message' => 'Your comment has been added!',
                'data' => $comments,
                'status' => true,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'data' => [],
                'status' => false,
            ], 500);
        }
    }
}