<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Mail\ContactReceivedMail;
use App\Models\Request as ContactRequest;
use App\Models\Service;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends Controller
{
    public function token(HttpRequest $request): Response
    {
        $request->session()->start();

        return response()->json([
            'token' => csrf_token(),
            'success' => true,
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function store(StoreContactRequest $request): Response
    {
        if (filled($request->input('website'))) {
            return response('OK', 200)->header('Content-Type', 'text/plain; charset=UTF-8');
        }

        $key = 'contact:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response('Too many requests. Please try again later.', 429)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }

        RateLimiter::hit($key, 3600);

        $serviceId = null;
        $slug = $request->validated('service');
        if (filled($slug)) {
            $serviceId = Service::query()
                ->published()
                ->where('language', 'en')
                ->where('slug', $slug)
                ->value('id');
        }

        $record = ContactRequest::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'subject' => $request->validated('subject'),
            'message' => $request->validated('message'),
            'status' => 'new',
            'service_id' => $serviceId,
        ]);

        $this->notify($record);

        return response('OK', 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    private function notify(ContactRequest $record): void
    {
        $to = config('cms.contact_email');
        if (! filled($to)) {
            return;
        }

        try {
            Mail::to($to)->send(new ContactReceivedMail($record));
        } catch (\Throwable $exception) {
            Log::error('Contact notification email failed after the request was stored.', [
                'request_id' => $record->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
