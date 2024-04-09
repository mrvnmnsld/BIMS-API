<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InspectionModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Models\TravelModel;
use App\Models\BoatsModel;

class InspectionController extends Controller
{
    public function getInspectionByBoatID(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "boat_id" => "required",
        ]);

        if ($validator->fails()) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0006", // validator fails
                'errors' => $validator->errors()
            ], 422);
        }

        // $results = InspectionModel::where('boat_id', $request->input('boat_id'))->latest()->first();

        $results = InspectionModel::where('boat_id', $request->input('boat_id'))
            // ->whereDate('created_at', today()) // Assuming 'created_at' is the timestamp column
            ->with(['boats.type', 'boats.destination'])
            ->latest()
            ->first();

        if (!$results) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0012",
                "message" => "No Inspection for the boat id given (today)",
            ], 200);
        }

        return response([
            "status" => "success",
            "code" => "BIMSAPI0001", // success
            "data" => [
                "isProceed" => 1,
                "msg" => "Successfully fetched data",
                'results' => $results,
            ]
        ], 200);
    }

    public function getInspectionByInspectionID(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "inspection_id" => "required",
        ]);

        if ($validator->fails()) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0006", // validator fails
                'errors' => $validator->errors()
            ], 422);
        }

        $record = InspectionModel::with(['boats.type'])->find($request->input('inspection_id'));

        if (!$record) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0012",
                "message" => "No such Inspection ID",
            ], 401);
        }

        return response([
            "status" => "success",
            "code" => "BIMSAPI0001", // success
            "data" => [
                "isProceed" => 1,
                "msg" => "Successfully fetched data",
                'results' => $record,
            ]
        ], 200);
    }

    public function getInspectionByBoatIDForQR(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "boat_id" => "required",
        ]);

        if ($validator->fails()) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0006", // validator fails
                'errors' => $validator->errors()
            ], 422);
        }

        // $results = InspectionModel::where('boat_id', $request->input('boat_id'))->latest()->first();

        $results = InspectionModel::where('boat_id', $request->input('boat_id'))
            ->whereDate('created_at', today()) // Assuming 'created_at' is the timestamp column
            ->with(['boats.type', 'boats.destination'])
            ->latest()
            ->first();

        if (!$results) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0012",
                "message" => "No Inspection for the boat id given (today)",
            ], 200);
        }

        return response([
            "status" => "success",
            "code" => "BIMSAPI0001", // success
            "data" => [
                "isProceed" => 1,
                "msg" => "Successfully fetched data",
                'results' => $results,
            ]
        ], 200);
    }

    public function addInspectionDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "boat_id" => "required",
            "reg_up_to_date" => "required",
            "blidge_pump" => "required",
            "battery" => "required",
            "electrical_system" => "required",
            "interior" => "required",
            "hull_bow" => "required",
            "safety_gear" => "required",
        ]);

        if ($validator->fails()) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0006", // validator fails
                'errors' => $validator->errors()
            ], 422);
        }

        $boatChecker = BoatsModel::where('id', $request->input('boat_id'))->get();

        $latestToday = InspectionModel::latest()->where('boat_id', $request->input('boat_id'))->first();

        if (count($boatChecker) < 1) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0012",
                "message" => "No such boat exist",
            ], 401);
        }

        if ($latestToday) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0012",
                "message" => "Inspection Details Already Set. Please, proceed to editing",
            ], 200);
        }

        $dataToSave = [
            "boat_id" => $request->input('boat_id'),
            "reg_up_to_date" => $request->input('reg_up_to_date'),
            "blidge_pump" => $request->input('blidge_pump'),
            "battery" => $request->input('battery'),
            "electrical_system" => $request->input('electrical_system'),
            "interior" => $request->input('interior'),
            "hull_bow" => $request->input('hull_bow'),
            "safety_gear" => $request->input('safety_gear'),
            'created_at' => now()->toDateString()
        ];

        $savedData = InspectionModel::create($dataToSave);

        return response([
            "status" => "success",
            "code" => "BIMSAPI0008",
            "msg" => "Saving succcessful",
            "data" => $savedData,
        ], 200);
    }

    public function updateInspectionDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "inspection_id" => "required",
            'departure_time' => ['regex:/^(0?[1-9]|1[0-2]):[0-5][0-9][APMapm]{2}$/'],
            'arrival_time' => ['regex:/^(0?[1-9]|1[0-2]):[0-5][0-9][APMapm]{2}$/'],
        ]);

        if ($validator->fails()) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0006", // validator fails
                'errors' => $validator->errors()
            ], 422);
        }

        $record = InspectionModel::find($request->input('inspection_id'));

        if (!$record) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0012",
                "message" => "No such travel ID",
            ], 401);
        }

        $record->reg_up_to_date = $request->input('reg_up_to_date', $record->reg_up_to_date);
        $record->blidge_pump = $request->input('blidge_pump', $record->blidge_pump);
        $record->battery = $request->input('battery', $record->battery);
        $record->electrical_system = $request->input('electrical_system', $record->electrical_system);
        $record->interior = $request->input('interior', $record->interior);
        $record->hull_bow = $request->input('hull_bow', $record->hull_bow);
        $record->safety_gear = $request->input('safety_gear', $record->safety_gear);

        $record->save();

        return response([
            "status" => "success",
            "code" => "BIMSAPI0008",
            "msg" => "succcessful Updated",
        ], 200);
    }

    public function getAllInspections(Request $request)
    {
        $results = InspectionModel::with(['boats.type'])
            ->whereHas('boats', function ($query) {
                $query->where('isActive', '1');
            })
            ->orderBy('id', 'desc')
            ->get()
        ;

        return response([
            "status" => "success",
            "code" => "BIMSAPI0001", // success
            "data" => [
                "isProceed" => 1,
                "msg" => "Successfully fetched data",
                'results' => $results,
            ]
        ], 200);
    }
}
