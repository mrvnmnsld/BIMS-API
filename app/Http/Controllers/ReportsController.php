<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Models\ReportsModel;
use App\Models\BoatsModel;

class ReportsController extends Controller
{
    public function getReportsByBoatID(Request $request)
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

        $boatsRes =  BoatsModel::with(['type', 'papers', 'destination'])
            ->where('id', $request->input('boat_id'))
            ->first();

        if ($boatsRes === null) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0012",
                "message" => "No such boat exist",
            ], 401);
        }


        // $results = ReportsModel::where('boat_id', $request->input('boat_id'))->with(['boats.type', 'boats.destination'])->latest()->first();

        $results = ReportsModel::where('boat_id', $request->input('boat_id'))
            ->where('isResolved', 0) // Add another condition
            ->with(['boats.type', 'boats.destination'])
            ->get();

        if (count($results) <= 0) {
            return response([
                "status" => "success",
                "code" => "BIMSAPI0001", // success
                "data" => [
                    "isProceed" => 1,
                    "msg" => "No Unresolved Reports",
                ]
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

    public function getAllInspections(Request $request)
    {
        $results = ReportsModel::with(['boats.type'])
            ->whereHas('boats', function ($query) {
                $query->where('isActive', '1');
            })
            ->orderBy('id', 'desc')->get();

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

    public function resolveReportByID(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "report_id" => "required",
        ]);

        if ($validator->fails()) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0006", // validator fails
                'errors' => $validator->errors()
            ], 422);
        }

        $record = ReportsModel::find($request->input('report_id'));

        if (!$record) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0012",
                "message" => "No such Report ID",
            ], 401);
        }

        $record->isResolved = 1;
        $record->save();

        return response([
            "status" => "success",
            "code" => "BIMSAPI0008",
            "msg" => "succcessful Updated",
        ], 200);
    }

    public function addReportDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "boat_id" => "required",
            "remarks" => "required",
            "title" => "required",

        ]);

        if ($validator->fails()) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0006", // validator fails
                'errors' => $validator->errors()
            ], 422);
        }

        $boatChecker = BoatsModel::where('id', $request->input('boat_id'))
            ->where('isActive', 1)
            ->get();

        if (count($boatChecker) < 1) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0012",
                "message" => "No such boat exist",
            ], 401);
        }

        $dataToSave = [
            "boat_id" => $request->input('boat_id'),
            "remarks" => $request->input('remarks'),
            "title" => $request->input('title'),
            'created_at' => now()->toDateString()
        ];

        $savedData = ReportsModel::create($dataToSave);

        return response([
            "status" => "success",
            "code" => "BIMSAPI0008",
            "msg" => "Saving succcessful",
            "data" => $savedData,
        ], 200);
    }
}
