<?php

namespace App\Http\Controllers;

use App\Services\GoogleService;

class GoogleDriveController extends GoogleController
{
    protected $tokenPath = GoogleService::GOOGLE_DRIVE_TOKEN_PATH;
}
