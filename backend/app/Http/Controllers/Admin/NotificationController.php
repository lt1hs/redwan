<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Notification;
use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Kreait\Firebase\Messaging\MessageTarget;

class NotificationController extends Controller
{

    public function notify(Request $request)
    {
        $messaging = app('firebase.messaging');
        $message = CloudMessage::withTarget(MessageTarget::TOPIC, 'general')
            ->withHighestPossiblePriority()
            ->withNotification(FirebaseNotification::create($request->title,  $request->body, $request->image))
            // ->withData(['title' => $request->title, 'body' => $request->body, 'image' => $request->image])
        ;
        return $messaging->send($message);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Notification::latest()->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->notify($request);
        return Notification::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(Notification $notification) {}


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Notification $notification) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notification $notification)
    {
        $notification->delete();
    }
}
