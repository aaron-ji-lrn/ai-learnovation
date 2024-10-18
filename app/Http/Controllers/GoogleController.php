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

            $this->googleService->client->setState($originalRoute);
            $accessToken = $this->googleService->client->fetchAccessTokenWithAuthCode($authCode);
            $this->googleService->client->setAccessToken($accessToken);
            file_put_contents(storage_path(GoogleService::GOOGLE_TOKEN_PATH), json_encode($this->googleService->client->getAccessToken()));

            return redirect()->route($originalRoute);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }
}
