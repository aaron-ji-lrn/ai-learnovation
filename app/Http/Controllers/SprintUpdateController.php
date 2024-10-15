<?php

namespace App\Http\Controllers;

use App\Services\AiService;
use App\Services\GoogleService;
use App\Services\JiraService;
use App\Services\MediaService;

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
        MediaService $mediaService
    ){
        $this->aiService = $aiService;
        $this->jiraService = $jiraService;
        $this->googleService = $googleService;
        $this->mediaService = $mediaService;
    }

    public function index()
    {
        $sprintId = 1258;
        $presentationId = env('GOOGLE_SLIDE_ID');
        $pageObjectId = env('GOOGLE_SLIDE_PAGE_ID');
        $googleDriveFolderId = env('GOOGLE_DRIVE_FOLDER_ID');
        $mediaFolder = env('MEDIA_FOLDER');

        $tickets = $this->jiraService->getTicketsFromSprint($sprintId);
        $tickets = $this->jiraService->processTickets($tickets);

        $response = $this->googleService->addTicketsToSlide($tickets, $presentationId, $pageObjectId);

        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return $response;
        }
        
        $summary = $this->aiService->getSprintSummary(json_encode($tickets));

        $audioResult = $this->aiService->generateAudio($summary, $mediaFolder);
        $audioFileName = $audioResult['audio'] ?? null;
        if (!file_exists(public_path($audioFileName))) {
            return response()->json($audioResult, 404);
        }

        $videoResult = $this->mediaService->convertAudioToMp4($audioFileName, $mediaFolder);
        $videoFileName = $videoResult['video'] ?? null;
        if (!file_exists(public_path($videoFileName))) {
            return response()->json($videoResult, 404);
        }

        $uploadResult = $this->googleService->uploadVideoToDrive($videoFileName, $googleDriveFolderId);
        $fileId = $uploadResult['file_id'] ?? null;
        if ($fileId === null) {
            return response()->json($uploadResult , 500);
        }

        $result = $this->googleService->insertVideoToSlide($fileId, $presentationId, $pageObjectId);

        return response()->json([
            'tickets' => $tickets,
            'summary' => $summary,
            'audio' => $audioFileName,
            'video' => $videoFileName,
            'fileId' => $fileId,
            'result' => $result,
        ]);
    }

    public function insertText()
    {
        $sprintId = 1258;
        $presentationId = env('GOOGLE_SLIDE_ID');
        $pageObjectId = env('GOOGLE_SLIDE_PAGE_ID');

        $tickets = $this->jiraService->getTicketsFromSprint($sprintId);
        $tickets = $this->jiraService->processTickets($tickets);

        $response = $this->googleService->addTicketsToSlide($tickets, $presentationId, $pageObjectId);

        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return $response;
        }

        return response()->json($tickets);
    }

    public function insertVideo()
    {
        $fileId = '13vKA3RurlBh-VgBokQgILNrP4kVkJJgB';
        $presentationId = '1drzZI_o1Qrm3or2gvmXj8l_SYk8h80bjj59SqEAJkeM';
        $pageObjectId = 'g30af94bd423_0_0';
        $result = $this->googleService->insertVideoToSlide($fileId, $presentationId, $pageObjectId);

        return response()->json($result);
    }
}
