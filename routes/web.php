<?php

use App\Http\Controllers\AssessmentController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/assessments/{id}/feedback', [AssessmentController::class, 'getFeedback']);
Route::get('/assessments/{sessionId}/student/{studentId}/feedback', [AssessmentController::class, 'getStudentFeedback']);
