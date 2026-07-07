<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\RequisitionController;
use Illuminate\Support\Facades\Route;

// Home -> straight into the approval queue
Route::redirect('/', '/approvals');

/*
|--------------------------------------------------------------------------
| Flow: Create Requisition (Screen 3) -> Route for Approval (Screen 2)
|       -> Approval Queue (Screen 1)
|--------------------------------------------------------------------------
*/

Route::prefix('requisitions')->name('requisitions.')->group(function () {
    Route::get('/create', [RequisitionController::class, 'create'])->name('create');
    Route::post('/', [RequisitionController::class, 'store'])->name('store');

    Route::get('/{id}/route', [RequisitionController::class, 'routeEdit'])->name('route.edit');
    Route::post('/{id}/route', [RequisitionController::class, 'routeStore'])->name('route.store');
});

Route::prefix('approvals')->name('approvals.')->group(function () {
    Route::get('/', [ApprovalController::class, 'index'])->name('index');
    Route::post('/{id}/decide', [ApprovalController::class, 'decide'])->name('decide');
});
