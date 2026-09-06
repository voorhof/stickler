<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreMessageRequest;
use App\Mail\ContactMessageReceived;
use App\Models\Message;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Show the contact page.
     */
    public function index(): View
    {
        return view('public.contact', [
            'headTitle' => __('Contact'),
        ]);
    }

    /**
     * Store a newly created contact message in storage.
     */
    public function store(StoreMessageRequest $request)
    {
        $message = Message::create($request->validated());

        // Send mail to the admin
        Mail::queue(new ContactMessageReceived($message));

        return redirect()
            ->route('contact.index')
            ->with('success', __('Thank you for your message! We’ll get back to you as soon as possible.'));
    }
}
