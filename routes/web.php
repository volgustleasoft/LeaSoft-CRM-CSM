<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\ClientenController;
use App\Http\Controllers\OpenQuestionController;
use App\Http\Controllers\PersonalCaregiverController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NewQuestionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controller;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::middleware(['web', 'auth:person', 'pincode', 'prevent-back-history'])->group(function () {
    Route::any('/', [Controller::class, 'redirectTo'])->name('rootRoute');

    Route::get('/login', [AuthController::class, 'loginPhoneView'])->name('login');
    Route::post('/login', [AuthController::class, 'loginPhoneAction'])->name('loginPhoneAction');
    Route::get('/login/code', [AuthController::class, 'loginCodeView'])->name('loginCodeView');
    Route::post('/login/code', [AuthController::class, 'loginCodeAction'])->name('loginCodeAction');
    Route::get('/logout', [AuthController::class, 'logoutAction'])->name('logoutAction');
    Route::get('/token/{token}', [AuthController::class, 'loginTokenView'])->name('loginTokenView');
    Route::get('/login/token/{token}', [AuthController::class, 'loginTokenAction'])->name('loginTokenAction');

    Route::get('/personalCaregiver', [PersonalCaregiverController::class, 'showClientenPage']);
    Route::post('/personalCaregiver/removeCG', [PersonalCaregiverController::class, 'removeCG']);
    Route::post('/personalCaregiver/addCG', [PersonalCaregiverController::class, 'addCG']);

    //OpenQuestion pages
    Route::get('/openQuestion-caregiver', [OpenQuestionController::class, 'showCaregiverView']);

    //New Question pages
    Route::get('/question/category', [NewQuestionController::class, 'newQuestionCategoryView'])->name('questionCategory');
    Route::get('/question/category/edit', [NewQuestionController::class, 'newQuestionCategoryView'])->name('editQuestionCategory');
    Route::post('/question/category', [NewQuestionController::class, 'newQuestionCategoryAction']);
    Route::get('/question/description', [NewQuestionController::class, 'newQuestionDescriptionView']);
    Route::post('/question/description', [NewQuestionController::class, 'newQuestionDescriptionAction']);
    Route::get('/question/appointmentType', [NewQuestionController::class, 'newQuestionAppointmentTypeView']);
    Route::post('/question/appointmentType', [NewQuestionController::class, 'newQuestionAppointmentTypeAction']);
    Route::get('/question/dateTime', [NewQuestionController::class, 'newQuestionDateTimeView']);
    Route::post('/question/dateTime', [NewQuestionController::class, 'newQuestionDateTimeAction']);
    Route::get('/question/summary', [NewQuestionController::class, 'newQuestionSummaryView']);
    Route::post('/question/summary', [NewQuestionController::class, 'newQuestionSummaryAction']);
    Route::get('/question/done', [NewQuestionController::class, 'newQuestionDoneView']);
    Route::post('/question/ajaxPostHandler', [NewQuestionController::class, 'ajaxPostHandler']);
    Route::get('/question/cancel', [NewQuestionController::class, 'newQuestionCancelAction'])->name('questionCancel');

    //Agenda pages
    Route::post('/agenda/ajaxPostHandler', [AgendaController::class, 'ajaxPostHandler']);
    Route::get('/agenda/ajaxHandler', [AgendaController::class, 'ajaxHandler']);
    Route::get('/agenda/client', [AgendaController::class, 'showClientPage'])->name('agendaClient');
    Route::get('/agenda/caregiver', [AgendaController::class, 'showCaregiverPage']);
    Route::post('/agenda/caregiver', [AgendaController::class, 'showCaregiverPage']);
    Route::post('/agenda/caregiver/cancelAppointment', [AgendaController::class, 'cancelAppointmentAction']);
    Route::post('/agenda/caregiver/reportAppointment', [AgendaController::class, 'reportAppointment']);
    Route::post('/agenda/client/cancelQuestion', [AgendaController::class, 'cancelQuestionAction']);
    Route::post('/agenda/client/reportQuestionAction', [AgendaController::class, 'reportQuestionAction']);

});

