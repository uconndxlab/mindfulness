<?php

use App\Http\Controllers\XapiStatementController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.xapi.check_authorization_token')->group(function () {
    // auth sanctum protection occurs in the controller
    Route::put('/xapi/statements/{statementId?}', [XapiStatementController::class, 'store']);
    Route::post('/xapi/statements', [XapiStatementController::class, 'store']);
    Route::get('/xapi/completion/{packageId}', [XapiStatementController::class, 'checkCompletion']);
});