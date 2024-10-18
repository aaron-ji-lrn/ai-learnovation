<?php

namespace App\Http\Controllers;

use App\Services\GoogleService;

class GoogleSlidesController extends GoogleController
{
    protected $tokenPath = GoogleService::GOOGLE_SLIDES_TOKEN_PATH;
}
