<?php

use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\SprintUpdateController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleDriveController;
use App\Http\Controllers\GoogleSlidesController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/generate', [AssessmentController::class, 'generate']);
Route::get('/assessments/{id}/feedback', [AssessmentController::class, 'getFeedback']);
Route::get('/assessments/{sessionId}/student/{studentId}/feedback', [AssessmentController::class, 'getStudentFeedback']);

Route::get('/feedback/{activityId}/{sessionId}', [AssessmentController::class, 'aiFeedback']);


/***********sprint update AI project ***************/
Route::get('/sprint/index', [SprintUpdateController::class, 'index'])->name('sprint.update');
Route::get('/sprint/video', [SprintUpdateController::class, 'insertVideo'])->name('sprint.insert.video');
Route::get('/sprint/text', [SprintUpdateController::class, 'insertText'])->name('sprint.insert.text');

Route::get('upload-audio', [GoogleDriveController::class, 'uploadAudio'])->name('upload.audio');
Route::get('upload-video', [GoogleDriveController::class, 'uploadVideo'])->name('upload.video');
Route::get('google/drive/callback', [GoogleDriveController::class, 'handleGoogleCallback'])->name('google.drive.callback');

Route::get('insert-audio', [GoogleSlidesController::class, 'insertAudio'])->name('insert.audio');
Route::get('insert-text', [GoogleSlidesController::class, 'addTextToSlide'])->name('insert.text');
Route::get('google/slides/auth', [GoogleSlidesController::class, 'redirectToGoogle'])->name('google.slides.auth');
Route::get('google/slides/callback', [GoogleSlidesController::class, 'handleGoogleCallback'])->name('google.slides.callback');
