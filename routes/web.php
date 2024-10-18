<?php

use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\SprintUpdateController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/generate', [AssessmentController::class, 'generate']);
Route::get('/assessments/{id}/feedback', [AssessmentController::class, 'getFeedback']);
Route::get('/assessments/{sessionId}/student/{studentId}/feedback', [AssessmentController::class, 'getStudentFeedback']);

Route::get('/feedback/{activityId}/{sessionId}', [AssessmentController::class, 'aiFeedback']);


/***********sprint update AI project ***************/

Route::get('/sprint/run_sprint_update_process', [SprintUpdateController::class, 'index'])->name('sprint.update');

Route::get('/sprint/tickets', [SprintUpdateController::class, 'tickets'])->name('sprint.tickets');
Route::get('/sprint/summary', [SprintUpdateController::class, 'sprintSummary'])->name('sprint.tickets.summary');
Route::get('/sprint/summary_audio', [SprintUpdateController::class, 'generateSummaryAudio'])->name('sprint.tickets.summary.audio');
Route::get('/sprint/summary_audio_convert', [SprintUpdateController::class, 'convertAudioToMp4'])->name('sprint.tickets.summary.audio.convert');
Route::get('/sprint/upload_video', [SprintUpdateController::class, 'uploadVideoToGoogleDrive'])->name('sprint.tickets.summary.video.upload');
Route::get('/sprint/insert_tickets_to_slide', [SprintUpdateController::class, 'insertTicketsToSlide'])->name('sprint.slide.tickets');
Route::get('/sprint/insert_video_to_slide', [SprintUpdateController::class, 'insertVideoToSlide'])->name('sprint.slide.video');

Route::get('google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('google.callback');
