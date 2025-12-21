<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactUsController extends Controller
{
    protected $database;

    public function __construct()
    {
        $factory = (new \Kreait\Firebase\Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->database = $factory->createDatabase();
    }

    # LIST VIEW
    public function index()
    {
        $messages = $this->database->getReference('contact_messages')->getValue();
        return view('volunteers-admin.contactUs', compact('messages'));
    }

    # STORE PUBLIC FORM SUBMISSION
    public function store(Request $request)
    {
        $data = [
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'subject'    => $request->subject,
            'message'    => $request->message,
            'status'     => 'new',
            'created_at' => now()->timestamp,
            'last_action_at' => now()->timestamp,
            'replies'    => []
        ];

        $this->database->getReference('contact_messages')->push($data);

        return back()->with('success', 'Form submitted successfully!');
    }

    # VIEW SINGLE MESSAGE
    public function show($id)
    {
        $message = $this->database->getReference("contact_messages/$id")->getValue();
        return view('volunteers-admin.contactUs-view', compact('message', 'id'));
    }

    # REPLY TO MESSAGE
public function reply(Request $request, $id)
{
    $validated = $request->validate([
        'reply_message' => 'required|string|max:1000',
    ]);

    $reference = $this->database->getReference("contact_messages/$id");

    // Build reply payload without admin_id
    $reply = [
        'message'    => $validated['reply_message'],
        'replied_at' => now()->toDateTimeString(),
    ];

    // Push reply to thread
    $reference->getChild('replies')->push($reply);

    // Update message status + last action
    $reference->update([
        'status'        => 'replied',
        'last_action_at'=> now()->toDateTimeString(),
    ]);

    return back()->with('success', 'Reply sent successfully!');
}

}
