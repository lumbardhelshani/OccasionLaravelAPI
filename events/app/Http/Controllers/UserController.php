<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Events;
use App\UserEvents;

class UserController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/';

    public function login(Request $request){

        $loginData = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if(!Auth::attempt($loginData)){
            return response()->json(['message' =>'Not authorized!'], 401);
        }

        $user = Auth::user();
        $accessToken = $user->createToken('authToken')->accessToken;

        return response()->json([
            'message' => 'Successfully Logged in!',
            'token' => $accessToken
        ]);
    }

    public function createEvent(Request $request){
        $event = new Events();
        $event->name = $request->name;
        $event->date = $request->date;
        $event->description = $request->description;
        $event->longitude = $request->longitude;
        $event->latitude = $request->latitude;
        $event->photo = $request->photo;
        $event->user_id = Auth::user()->id;

        if($event->save()){
            return response()->json([
                'message' => 'Event saved successfully.',
            ] , 200);
        }
        return response()->json([
            'message' => 'Something went wrong!',
        ] , 401);
    }

    public function eventsToGo(){
        if(Auth::check()){
            $events = Events::select("events.*")
            ->join('User_events' , 'events.id' , 'User_events.event_id')
            ->where('User_events.user_id' , Auth::user()->id)
            ->get();

            return response()->json($events, 200);
        }else{
            return response()->json([
                'message' => 'Unauthorized!',
            ] , 403);
        }
       
    }

    public function myEvents(){
        $events = Events::where('user_id' , Auth::user()->id)->get();

        return response()->json($events,200);
    }

    public function respondEvent(Request $r){
        //$event = Events::where('id' , $r->event_id)->get();
        //$eventID = $event->pluck('id');
        $eventID = Events::where('id' ,$r->event_id)->pluck('id');
        //$userEvent = UserEvents::select("users_events.*")->where('event_id' , $eventID)->where('user_id' , Auth::user()->id)->first();
        $userEvent = UserEvents::where('event_id' , $eventID)->where('user_id' , Auth::user()->id)->first();

        $userEvent->status = $r->status;

        if($userEvent->save()){
            return response()->json([
                'message' => 'Event successfully '.$r->status, 
                'status' => $r->status,
            ] , 200);
        }
        return response()->json([
            'message' => 'Something went wrong!',
        ] , 401);
    }

    public function getEventStatus(Request $r){
        $eventID = Events::where('id' ,$r->event_id)->pluck('id');
        $userEvent = UserEvents::where('event_id' , $eventID)->where('user_id' , Auth::user()->id)->first();
        return response()->json([
            'status' => $userEvent->status,
        ],200);
    }

    public function invite(Request $r){
        $userEvent = new UserEvents();

        $userEvent->event_id = $r->event;
        $userID = User::where('email' , $r->email)->pluck('id');
        $userEvent->user_id = $userID;

        if($userEvent->save()){
            return response()->json([
                'message' => 'User Successfully Invited.',
            ] , 200);
        }

        return response()->json([
            'message' => 'Something went wrong!',
        ] , 401);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'message' => 'You logged out!'
        ]);
    }

    public function register(Request $request)
    {
        $currentUser = Auth::guard('api')->user();

        if(!User::where('email', '=', $request->email)->exists()){
            $request->password = bcrypt($request->password);

            User::create($request->all());

            Log::create([
                'user_name' =>  $currentUser->email,
                'message' => $currentUser->email.' created the user '.$request->email
            ]);

            $users = User::orderby('created_at','desc')->get();
            return response()->json($users);
        }else{
            return response()->json([
                'message' => 'User exists!'
            ]);
        }
    }

    public function delete($user_id)
    {
        $currentUser = Auth::guard('api')->user();

        $user = User::find($user_id);
        $users = User::orderby('created_at','desc')->get();

        $deletedEmail = $user->email;
        $user->delete();

        Log::create([
            'user_name' =>  $currentUser->email,
            'message' => $currentUser->email.' deleted the user '.$deletedEmail
        ]);

        return response()->json($users);
    }

}
