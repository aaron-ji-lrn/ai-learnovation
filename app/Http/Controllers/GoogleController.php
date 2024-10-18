<?php

namespace App\Http\Controllers;

use App\Services\GoogleService;
use Illuminate\Http\Request;

class GoogleController extends Controller
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
            $originalRoute = $request->input('state');
            $callbackRoute = $request->route()->getName();

            $this->googleService->handleCallback(
                $authCode, 
                GoogleService::GOOGLE_TOKEN_PATH, 
                $originalRoute, 
                $callbackRoute
            );

            return redirect()->route($originalRoute);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }
}
