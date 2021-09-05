<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\PaginatedResourceResponse;

class TicketController extends Controller
{
    public function list(Request $request)
    {
        $user = $request->user();
        // $tickets = Ticket::orderBy('ticket','DESC');
        if ($user->admin)
            $tickets = Ticket::where(function ($query) use ($request) {
                if ($request->has('priority'))
                    $query->where('priority', $request->get('priority'));
                if ($request->has('status'))
                    $query->where('status', $request->get('status'));
                if ($request->has('type'))
                    $query->where('type', $request->get('type'));
                if ($request->has('subject'))
                    $query->where('subject','like', '%'.$request->get('subject').'%'); 
                if ($request->has('archived') && $request->get('archived'))
                    $query->where('archived', true);
            })->orderBy('id', 'DESC')->paginate($request->get('count', 10), '*', '', $request->get('page'));
        else
            $tickets = $user->tickets()->where(function ($query) use ($request) {
                if ($request->has('priority'))
                    $query->where('priority', $request->get('priority'));
                if ($request->has('status'))
                    $query->where('status', $request->get('status'));
                if ($request->has('type'))
                    $query->where('type', $request->get('type'));
                if ($request->has('subject'))
                $query->where('subject','like', '%'.$request->get('subject').'%');
                if ($request->has('archived') && $request->get('archived'))
                    $query->where('archived', true);
            })->orderBy('id', 'DESC')->paginate($request->get('count', 10), '*', '', $request->get('page'));
        return response()->json($tickets);
    }

    public function show($id)
    {
        $user = request()->user();
        $ticket = Ticket::with('contents')->find($id);
        if (!$ticket)
            return response()->json(['not found'], 404);
        if (!$user->admin && $ticket->user_id != $user->id)
            return response()->json(['not valid']);
        if ($user->admin && $ticket->status == 'new')
            $ticket->update([
                'status' => 'open'
            ]);
        return response()->json([$ticket]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $ticket = $user->tickets()->create($request->all());
        $ticket->contents()->create(['body' => $request->get('content'),'user_id'=>$user->id]);
        return response()->json(['success'], 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $ticket = Ticket::find($id);
        if (!$ticket)
            return response()->json(['not found'], 404);
        if (!$user->admin && $ticket->user_id != $user->id)
            return response()->json(['not valid']);
        $fields = $request->only(['archived', 'status']);
        if (isset($fields['status']) && $fields['status'] == 'In Progress' && !$user->admin)
            unset($fields['status']);
        if (!empty($fields))
            $ticket->update($fields);
        if ($request->has('content')) {
            $status = ($user->admin) ? 'Answered' : 'New';
            $ticket->update(['status' => $status]);
            $ticket->contents()->create(['body' => $request->get('content'),'user_id'=>$user->id]);
        }
        return response()->json(['success'], 202);
    }
}