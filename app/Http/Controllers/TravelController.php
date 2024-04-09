<?php

namespace App\Http\Controllers;

use App\Models\BoatsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Models\TravelModel;
use App\Models\DestinationModel;

class TravelController extends Controller
{
    public function getTravelByBoatID(Request $request)
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

        $results = TravelModel::where('boat_id', $request->input('boat_id'))->with(['boats.type', 'boats.destination'])->latest()->first();

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

    public function addTravelDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "boat_id" => "required",
            "departure_time" => ["required", 'regex:/^(0?[1-9]|1[0-2]):[0-5][0-9][APMapm]{2}$/'],
            "arrival_time" => ["required", 'regex:/^(0?[1-9]|1[0-2]):[0-5][0-9][APMapm]{2}$/'],
            "passenger_list" => "required",
        ]);

        if ($validator->fails()) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0006", // validator fails
                'errors' => $validator->errors()
            ], 422);
        }

        $boatChecker = BoatsModel::where('id', $request->input('boat_id'))
            ->where("isActive", 1)
            ->get();

        $latestToday = TravelModel::where('boat_id', $request->input('boat_id'))
            ->latest()
            ->whereDate('created_at', today())
            ->first();

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
                "message" => "Travel Details Already Set. Please, proceed to editing",
            ], 200);
        }

        $dataToSave = [
            "boat_id" => $request->input('boat_id'),
            "departure_time" => $request->input('departure_time'),
            "arrival_time" => $request->input('arrival_time'),
            "passenger_list" => $request->input('passenger_list'),
            'created_at' => now()->toDateString()
        ];

        $savedData = TravelModel::create($dataToSave);

        if ($request->input('destination')) {
            $destination_info = DestinationModel::where('boat_id', $request->input('boat_id'))->first();

            $boatsUpdate['destination_desc'] = $request->input('destination');

            $destination_info->update($boatsUpdate);
        }

        return response([
            "status" => "success",
            "code" => "BIMSAPI0008",
            "msg" => "Saving succcessful",
            "data" => $savedData,
        ], 200);
    }

    public function updateTravelDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "travel_id" => "required",
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

        $record = TravelModel::find($request->input('travel_id'));

        if ($request->input('destination')) {
            $destination_info = DestinationModel::where('boat_id', $record->boat_id)->first();

            $boatsUpdate['destination_desc'] = $request->input('destination');

            $destination_info->update($boatsUpdate);
        }

        if (!$record) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0012",
                "message" => "No such travel ID",
            ], 401);
        }

        $record->departure_time = $request->input('departure_time', $record->departure_time);
        $record->arrival_time = $request->input('arrival_time', $record->arrival_time);
        $record->passenger_list = $request->input('passenger_list', $record->passenger_list);
        $record->save();

        return response([
            "status" => "success",
            "code" => "BIMSAPI0008",
            "msg" => "succcessful Updated",
        ], 200);
    }

    public function getAllTravels(Request $request)
    {
        $results = TravelModel::with(['boats.type', 'boats.destination'])
            ->whereHas('boats', function ($query) {
                $query->where('isActive', '1');
            })
            ->orderBy('id', 'desc')
            ->get();

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
