<?php

namespace App\Http\Controllers;

use App\Services\AiService;
use App\Services\GoogleService;
use App\Services\JiraService;
use App\Services\MediaService;
use Illuminate\Http\Request;

class SprintUpdateController extends Controller
{
    protected $aiService;
    protected $jiraService;
    protected $googleService;
    protected $mediaService;

    public function __construct(
        AiService $aiService, 
        JiraService $jiraService, 
        GoogleService $googleService,
        MediaService $mediaService,
        Request $request
    ){
        $this->aiService = $aiService;
        $this->jiraService = $jiraService;
        $this->googleService = $googleService;
        $this->mediaService = $mediaService;
        $this->googleService->client->setState($request->route()->getName());
    }

    public function index(Request $request)
    {
        $mediaFolder = env('MEDIA_FOLDER');

        $tickets = $this->jiraService->getSprintTickets();
        $summary = $this->aiService->getSprintSummary(json_encode($tickets));

        $audioResult = $this->aiService->generateAudio($summary);
        $audioFileName = $audioResult['audio'] ?? null;
        if (!file_exists(public_path($mediaFolder. '/' .$audioFileName))) {
            return response()->json($audioResult, 404);
        }

        $videoResult = $this->mediaService->convertAudioToMp4($audioFileName);
        $videoFileName = $videoResult['video'] ?? null;
        if (!file_exists(public_path($mediaFolder. '/' .$videoFileName))) {
            return response()->json($videoResult, 404);
        }

        $googleClient = $this->googleService->authenticate();

        if ($googleClient instanceof \Illuminate\Http\RedirectResponse) {
            return $googleClient;
        }

        $uploadResult = $this->googleService->uploadVideoToDrive($googleClient, $videoFileName);
        $fileId = $uploadResult['file_id'] ?? null;
        if ($fileId === null) {
            return response()->json($uploadResult , 500);
        }

        $response = $this->googleService->addTicketsToSlide($googleClient, $tickets);

        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return $response;
        }

        $result = $this->googleService->insertVideoToSlide($googleClient, $fileId);

        return response()->json([
            'tickets' => $tickets,
            'summary' => $summary,
            'audio' => $audioFileName,
            'video' => $videoFileName,
            'fileId' => $fileId,
            'result' => $result,
        ]);
    }

    /**
     * List all tickets from the sprint
     */
    public function tickets()
    {
        return response()->json($this->jiraService->getSprintTickets());
    }

    public function sprintSummary()
    {
        $tickets = $this->jiraService->getSprintTickets();
        $summary = $this->aiService->getSprintSummary(json_encode($tickets));

        return response()->json([
            'message' => 'generate summary successfully',
            'summary' => $summary, 
        ]);
    }

    public function generateSummaryAudio()
    {
        $tickets = $this->jiraService->getSprintTickets();
        $summary = $this->aiService->getSprintSummary(json_encode($tickets));
        $result = $this->aiService->generateAudio($summary);

        return response()->json([
            'message' => 'generate audio successfully',
            'summary' => $summary, 
            'response' => $result
        ]);
    }

    public function convertAudioToMp4(Request $request)
    {
        $audioFileName = $request->input('audio');
        if (!$audioFileName) {
            return response()->json(['error' => 'audio file not provided, please provide ?audio=xxx.mp3 in your url'], 500);
        }
        $mediaFolder = env('MEDIA_FOLDER');
        if (!file_exists(public_path($mediaFolder . '/' . $audioFileName))) {
            return response()->json(['message' => 'audio file not existed'], 404);
        }
        $result = $this->mediaService->convertAudioToMp4($audioFileName);

        return response()->json([
            'message' => 'covert audio to video successfully', 
            'file' => $audioFileName, 
            'response' => $result
        ]);
    }

    public function uploadVideoToGoogleDrive(Request $request)
    {
        $videoFileName = $request->input('video');
        if (!$videoFileName) {
            return response()->json(['error' => 'video file not provided, please provide ?video=xxx.mp4 in your url'], 500);
        }

        $mediaFolder = env('MEDIA_FOLDER');
        if (!file_exists(public_path($mediaFolder . '/' . $videoFileName))) {
            return response()->json(['message' => 'video file not existed'], 404);
        }

        $googleClient = $this->googleService->authenticate();

        if ($googleClient instanceof \Illuminate\Http\RedirectResponse) {
            return $googleClient;
        }

        $response = $this->googleService->uploadVideoToDrive(
            $googleClient,
            $videoFileName
        );

        return response()->json([
            'message' => 'upload video to google drive successfully', 
            'file' => $videoFileName, 
            'response' => $response
        ]);
    }

    public function insertTicketsToSlide(Request $request)
    {
        $tickets = $this->jiraService->getSprintTickets();
        
        $googleClient = $this->googleService->authenticate();

        if ($googleClient instanceof \Illuminate\Http\RedirectResponse) {
            return $googleClient;
        }

        $response = $this->googleService->addTicketsToSlide($googleClient, $tickets);

        return response()->json([
            'message' => 'Tickets added to slide', 
            'tickets' => $tickets, 
            'response' => $response
        ]);
    }

    public function insertVideoToSlide(Request $request)
    {
        $fileId = $request->input('file_id');
        if (!$fileId) {
            return response()->json(['error' => 'video file ID not provided, please provide ?file_id=xxx in the url, you can get the file id by share->copy link'], 500);
        }

        $googleClient = $this->googleService->authenticate();

        if ($googleClient instanceof \Illuminate\Http\RedirectResponse) {
            return $googleClient;
        }

        $response = $this->googleService->insertVideoToSlide(
            $googleClient,
            $fileId
        );

        return response()->json([
            'message'=> 'Insert video to google slide successfully',
            'fileId' => $fileId, 
            'response' => $response
        ]);
    }
}
