<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HospitalController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MedicalController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(['prefix' => 'hospital'], function () {
    Route::post('/create-hospital', [HospitalController::class, 'createHospital'])->name('hospital.create');
    Route::post('/get-hospital', [HospitalController::class, 'getHospital'])->name('hospital.get');
});
Route::group(['prefix' => 'member'], function () {
    Route::post('/create-member', [MemberController::class, 'createMember'])->name('member.create');
    Route::post('/get-member', [MemberController::class, 'getMember'])->name('member.getMember');
});
Route::group(['prefix' => 'medical'], function () {
    Route::post('/create-medical', [MedicalController::class, 'createMedicalRecord'])->name('medical.create');
    Route::post('/get-medical', [MedicalController::class, 'getDataMedicalRecord'])->name('medical.get');
    Route::post('/accessrequiremen-medical', [MedicalController::class, 'addHospitalAccessRequirement'])->name('accessrequiremen.add');
    Route::post('/add-hospitals-medical', [MedicalController::class, 'addMedicalHistoriesHospital'])->name('addMedicalHistoriesHospital.add');
    Route::post('/add-appointmenthospital-medical', [MedicalController::class, 'addAppointmentHospital'])->name('addAppointmentHospital.add');
    Route::post('/add-addexaminationhospital-medical', [MedicalController::class, 'addExaminationHospital'])->name('addExaminationHospital.add');
    Route::post('/add-addconditionhospital-medical', [MedicalController::class, 'addConditionHospital'])->name('addconditionhospital.add');
});