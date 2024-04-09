<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\OwnerModel;
use App\Models\BoatsModel;

class OwnerController extends Controller
{
    public function getAllOwners()
    {
        $results = OwnerModel::get();

        return response([
            "status" => "success",
            "code" => "BIMSAPI0001", // success
            "data" => [
                "isProceed" => 1,
                "msg" => "Successfully fetched all owners data",
                'ownerData' => $results,
            ]
        ], 200);
    }

    public function deleteOwner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'owner_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0006", // validator fails
                'errors' => $validator->errors()
            ], 422);
        }

        $owner_info = OwnerModel::find($request->input('owner_id'));
        $boats = BoatsModel::where('owner_id', $request->input('owner_id'))->get();

        if (!$owner_info) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0012",
                "message" => "No such Owner ID",
            ], 401);
        }
        
        if(count($boats)){
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0014",
                "message" => "Cannot Delete owner with existing boats, Please do delete the boats first or transfer the ownership to another owner",
                "boats_to_delete" => $boats
            ], 401);
        }

        $owner_info->delete();

        return response([
            "status" => "success",
            "code" => "BIMSAPI0008",
            "message" => "Record deleted successfully",
        ], 200);
    }

    public function addNewOwner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fname' => 'required',
            'mname' => 'required',
            'lname' => 'required',
            'address' => 'required'
        ]);

        if ($validator->fails()) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0006", // validator fails
                'errors' => $validator->errors()
            ], 422);
        }

        $boatsData = [
            'fname' => $request->input('fname'),
            'mname' => $request->input('mname'),
            'lname' => $request->input('lname'),
            'address' => $request->input('address'),
            'created_at' => now()->toDateString(),
        ];

        $ownerSavedData = OwnerModel::create($boatsData);

        return response([
            "status" => "success",
            "code" => "BIMSAPI0008",
            "msg" => "Saving new owner succcessful",
            "data" => $ownerSavedData,
        ], 200);
    }

    public function updateOwner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'owner_id' => 'required|max:10',
            'cols_to_change' => 'required|array|min:1',
            'cols_values' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0006", // validator fails
                'errors' => $validator->errors()
            ], 422);
        }

        $id = $request->input('owner_id');

        for ($i=0; $i < count($request->input("cols_to_change")); $i++) { 
            $dataToUpdate[$request->input("cols_to_change")[$i]] = $request->input("cols_values")[$i];
        }

        $owner = OwnerModel::find($request->input('owner_id'));

        if(!$owner){
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0012",
                "message" => "No such Owner ID",
            ], 401);
        }

        $owner->update($dataToUpdate);

        return response([
            "status" => "success",
            "code" => "BIMSAPI0013",
            "message" => "Record updated successfully",
            "owner" => $owner,
        ], 200);
    }

}
