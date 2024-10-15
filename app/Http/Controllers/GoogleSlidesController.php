<?php

namespace App\Http\Controllers;

use App\Services\GoogleService;
use Illuminate\Http\Request;

class GoogleSlidesController extends Controller
{
    protected $googleService;

    public function __construct(GoogleService $googleService)
    {
        $this->googleService = $googleService;
    }

    public function handleGoogleCallback(Request $request)
    {
        if ($request->has('code')) {
            $authCode = $request->input('code');
            $this->googleService->handleCallback($authCode, GoogleService::GOOGLE_SLIDES_TOKEN_PATH);

            return redirect()->route('sprint.update');
        }

        return redirect()->route('google.slides.auth');
    }

    public function redirectToGoogle()
    {
        $client = $this->googleService->authenticate(GoogleService::GOOGLE_SLIDES_TOKEN_PATH, 'google.slides.callback');
        $authUrl = $client->createAuthUrl();
        
        return redirect($authUrl);
    }
}
