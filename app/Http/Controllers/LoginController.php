<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    public function getUsersList(Request $request) {
        $user = $request->user();
        return response()->json(User::paginate($request->get('count', 10), '*', '', $request->get('page')));
    } 

    public function deleteUser(Request $request) {
        $user = $request->user();
        if(!$user->admin){
            return response()->json([
                'not admin'
            ], 403);
        }
        $user = User::find($request->get("id"));
        if(!$user)
            return response()->json([
                'not found'
            ], 404);
        $user->delete();
        return response()->json("successfully deleted");
    }

    public function register(Request $request)
    {
        $user2 = User::where('email', $request->get('email'))->first();
        if ($user2)
            return response()->json(['email exist']);
        if ($request->get('password') != $request->get('password_confirm'))
            return response()->json(['password and Confirm password does not match']);
        $user = User::create($request->all()); //instance
        Mail::html('<p>Welcome to <a href="http:localhost:3000/giude"> SAMA WEB </a> and enjoy it</p>', function ($message) use ($user) {
            $message->subject('Welcome To SAMA WEB')->to($user->email);
        });
        return response()->json([
            'success'
        ], 201);
    }

    public function updateUser(Request $request, $id)
    {
        // $request = request();
        $user2 = User::find($id);
        if (!$user2)
            return response()->json(['not found'], 404);
        $user = $request->user();
        if ($user2->id != $user->id)
            return response()->json(['access denied']);
        if ($request->has("password")&&$request->get('password') != $request->get('password_confirm'))
            return response()->json(['passwords does not match']);
        $user->update($request->all());
        return response()->json([
            'success'
        ], 202);
    }

    public function login(Request $request)
    {
        $user = User::where('email', $request->get('email'))->first();
        if (!$user || !Hash::check($request->get('password'), $user->password))
            return response()->json([
                'not valid'
            ], 422);
        return response()->json(['token' => $user->createToken('maghami')->plainTextToken, 'id' => $user->id,  'name' => $user->first_name, 'admin' => $user->admin ]);
    }

    public function passwordResetEmail(Request $request)
    {
        $user = User::where('email', $request->get('email'))->first();
        if (!$user)
            return response()->json([
                'not valid'
            ], 422);
        $token = \Ramsey\Uuid\Uuid::uuid4();
        while (User::where('token', $token)->first())
            $token = \Ramsey\Uuid\Uuid::uuid4();
        $user->token = $token;
        $user->save();
        Mail::html('<p>click <a href="http:localhost:3000/forgot?token=' . $token . '&email=' . $user->email . '">hear</a> to reset your password</p>', function ($message) use ($user) {
            $message->subject('Password reset')->to($user->email);
        });
        return response()->json([
            'success'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $user = User::where('token', $request->get('token'))->first();
        if (!$user || $request->get('email') != $user->email)
            return response()->json('not valid', 422);
        $user->password = $request->get('password');
        $user->save();
        return response()->json('success');
    }

    public function logout()
    {
        request()->user()->tokens()->delete();
        return response()->json('success');
    }

    public function getUser()
    {
        return response()->json(request()->user());
    }

    public function report(Request $request) {
        $user = $request->user();
        if(!$user->admin){
            return response()->json([
                'not Admin'
            ], 403);
        }
        $data = [
            'normal-users' => User::where("admin",false)->count(),
            'super-users' => User::where("admin",true)->count(),
            'new-tickets' => Ticket::where("status", "new")->count(),
            'open-tickets' => Ticket::where("status", "open")->count(),
            'progress-tickets' => Ticket::where("status", "In Progress")->count(),
            'answered-tickets' => Ticket::where("status", "Answered")->count(),
            'done-tickets' => Ticket::where("status", "done")->count()
        ];
        return response()->json($data);
    }
}
