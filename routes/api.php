<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TokenHandlerController;
use App\Http\Controllers\APIUserController;

use App\Http\Controllers\BoatsController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\TravelController;
use App\Http\Controllers\UsersController;

Route::post('/generateToken', [TokenHandlerController::class, 'generateToken']);

Route::group(['middleware' => 'verify.token'], function () {

    Route::group(['prefix' => 'user'], function() {
        Route::post('/checkCreds', [APIUserController::class, 'checkCreds']);
    });

    Route::group(['prefix' => 'boats'], function() {
        Route::post('/getAllBoats', [BoatsController::class, 'getAllBoats']);
        Route::post('/getBoatByID', [BoatsController::class, 'getBoatByID']);
        Route::post('/saveNewBoat', [BoatsController::class, 'saveNewBoat']);
        Route::post('/deleteBoat', [BoatsController::class, 'deleteBoat']);
        Route::post('/updateBoat', [BoatsController::class, 'updateBoat']);
        Route::post('/getExpiredPapers', [BoatsController::class, 'getExpiredPapers']);
        Route::post('/getAllArchivedBoats', [BoatsController::class, 'getAllArchivedBoats']);
        

        Route::post('/generateBoatQRCode', [BoatsController::class, 'generateBoatQRCode']); 
    });

    Route::group(['prefix' => 'owner'], function() {
        Route::post('/getAllOwners', [OwnerController::class, 'getAllOwners']);
        Route::post('/deleteOwner', [OwnerController::class, 'deleteOwner']);
        Route::post('/addNewOwner', [OwnerController::class, 'addNewOwner']);
        Route::post('/updateOwner', [OwnerController::class, 'updateOwner']);
    });

    Route::group(['prefix' => 'users'], function() {
        Route::post('/getAllUsers', [UsersController::class, 'getAllUsers']);
        Route::post('/checkCredentials', [UsersController::class, 'checkCredentials']);

        Route::post('/checkViaEmail', [UsersController::class, 'checkViaEmail']);
        Route::post('/checkViaUsername', [UsersController::class, 'checkViaUsername']);

        Route::post('/getUserInfoByID', [UsersController::class, 'getUserInfoByID']);
        Route::post('/deleteUser', [UsersController::class, 'deleteUser']);
        Route::post('/updateUser', [UsersController::class, 'updateUser']);
        Route::post('/addNewUser', [UsersController::class, 'addNewUser']); 
    });

    Route::group(['prefix' => 'travel'], function() {
        Route::post('/getTravelByBoatID', [TravelController::class, 'getTravelByBoatID']);
        Route::post('/addTravelDetails', [TravelController::class, 'addTravelDetails']);
        Route::post('/updateTravelDetails', [TravelController::class, 'updateTravelDetails']);
        Route::post('/getAllTravels', [TravelController::class, 'getAllTravels']);
    });

    Route::group(['prefix' => 'inspection'], function() {
        Route::post('/getInspectionByBoatID', [InspectionController::class, 'getInspectionByBoatID']);
        Route::post('/getInspectionByInspectionID', [InspectionController::class, 'getInspectionByInspectionID']);
        Route::post('/getInspectionByBoatIDForQR', [InspectionController::class, 'getInspectionByBoatIDForQR']);
        
        Route::post('/addInspectionDetails', [InspectionController::class, 'addInspectionDetails']);
        Route::post('/updateInspectionDetails', [InspectionController::class, 'updateInspectionDetails']);
        Route::post('/getAllInspections', [InspectionController::class, 'getAllInspections']); 
    });

    Route::group(['prefix' => 'reports'], function() {
        Route::post('/getReportsByBoatID', [ReportsController::class, 'getReportsByBoatID']);
        Route::post('/getAllInspections', [ReportsController::class, 'getAllInspections']);
        Route::post('/resolveReportByID', [ReportsController::class, 'resolveReportByID']);
        Route::post('/addReportDetails', [ReportsController::class, 'addReportDetails']);
    });

    

});


