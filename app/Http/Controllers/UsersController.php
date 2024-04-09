<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Models\UsersModel;

class UsersController extends Controller
{
    public function getAllUsers()
    {
        $results = UsersModel::get();

        return response([
            "status" => "success",
            "code" => "BIMSAPI0001", // success
            "data" => [
                "isProceed" => 1,
                "msg" => "Successfully fetched all users data",
                'boatsData' => $results,
            ]
        ], 200);
    }

    public function getUserInfoByID(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|max:10',
        ]);

        if ($validator->fails()) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0006", // validator fails
                'errors' => $validator->errors()
            ], 422);
        }

        $id = $request->input('user_id');

        $results = UsersModel::where('id', $id)->first();

        if (!$results) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0012",
                "message" => "No such user",
            ], 401);
        }

        return response([
            "status" => "success",
            "code" => "BIMSAPI0001",
            "data" => [
                "isProceed" => 1,
                "msg" => "Successfully fetched user data",
                'boatsData' => $results,
            ]
        ], 200);
    }

    public function checkCredentials(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0006", // validator fails
                'errors' => $validator->errors()
            ], 422);
        }

        $results = UsersModel::where('username', $request->input('username'))->first();

        if (!$results) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0012",
                "message" => "Invalid Credentials",
            ], 401);
        }

        if (!Hash::check($request->input('password'), $results->password)) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0012",
                "message" => "Invalid Credentials",
            ], 401);
        }

        return response([
            "status" => "success",
            "code" => "BIMSAPI0001", // success
            "data" => [
                "isProceed" => 1,
                "msg" => "Successfully verified credentials",
                'userData' => $results,
            ]
        ], 200);
    }
 
    public function checkViaEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0006", // validator fails
                'errors' => $validator->errors()
            ], 422);
        }

        $results = UsersModel::where('email', $request->input('email'))->first();

        if (!$results) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0012",
                "message" => "Invalid Credentials",
            ], 401);
        }

        return response([
            "status" => "success",
            "code" => "BIMSAPI0001", // success
            "data" => [
                "isProceed" => 1,
                "msg" => "Successfully verified email",
            ]
        ], 200);
    }

    public function checkViaUsername(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0006", // validator fails
                'errors' => $validator->errors()
            ], 422);
        }

        $results = UsersModel::where('username', $request->input('username'))->first();

        if (!$results) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0012",
                "message" => "Invalid Credentials",
            ], 401);
        }

        return response([
            "status" => "success",
            "code" => "BIMSAPI0001", // success
            "data" => [
                "isProceed" => 1,
                "msg" => "Successfully verified email"
            ]
        ], 200);

    }

    public function deleteUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0006", // validator fails
                'errors' => $validator->errors()
            ], 422);
        }

        $user_info = UsersModel::find($request->input('user_id'));

        if (!$user_info) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0012",
                "message" => "No such User ID",
            ], 401);
        }

        $user_info->delete();

        return response([
            "status" => "success",
            "code" => "BIMSAPI0008",
            "message" => "Record deleted successfully",
        ], 200);
    }

    public function updateUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|max:10',
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

        $id = $request->input('user_id');

        for ($i = 0; $i < count($request->input("cols_to_change")); $i++) {
            if ($request->input("cols_to_change")[$i] == "password") {
                $dataToUpdate[$request->input("cols_to_change")[$i]] = Hash::make($request->input("cols_values")[$i]);
            }else if($request->input("cols_to_change")[$i] == "email"){
                $users = UsersModel::where('email', $request->input("cols_values")[$i])->first();

                if ($users) {
                    return response([
                        "status" => "failed",
                        "code" => "BIMSAPI0012",
                        "message" => "User already exists. Make sure to have a unique Email & username",
                    ], 401);
                }

                $dataToUpdate[$request->input("cols_to_change")[$i]] = $request->input("cols_values")[$i];

            }else if($request->input("cols_to_change")[$i] == "username"){
                $users = UsersModel::where('username', $request->input("cols_values")[$i])->first();

                if ($users) {
                    return response([
                        "status" => "failed",
                        "code" => "BIMSAPI0012",
                        "message" => "User already exists. Make sure to have a unique Email & username",
                    ], 401);
                }

                $dataToUpdate[$request->input("cols_to_change")[$i]] = $request->input("cols_values")[$i];

            } else {
                $dataToUpdate[$request->input("cols_to_change")[$i]] = $request->input("cols_values")[$i];
            }

            
        }

        $user_info = UsersModel::find($request->input('user_id'));

        if (!$user_info) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0012",
                "message" => "No such User ID",
            ], 401);
        }

        $user_info->update($dataToUpdate);

        return response([
            "status" => "success",
            "code" => "BIMSAPI0013",
            "message" => "Record updated successfully",
            "user_info" => $user_info,
        ], 200);
    }

    public function addNewUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "username" => "required",
            "password" => "required",
            "email" => "required",
            "name" => "required",
            "dob" => "required",
            "designation" => "required",
            "deployment" => "required",
        ]);

        if ($validator->fails()) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0006", // validator fails
                'errors' => $validator->errors()
            ], 422);
        }

        $users = UsersModel::where('email', $request->input('email'))
            ->orWhere('username', $request->input('username'))
            ->first();

        if ($users) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0012",
                "message" => "User already exists. Make sure to have a unique Email & username",
            ], 401);
        }

        $userData = [
            "username" => $request->input('username'),
            "password" => hash::make($request->input('password')),
            "email" => $request->input('email'),
            "name" => $request->input('name'),
            "dob" => $request->input('dob'),
            "designation" => $request->input('designation'),
            "deployment" => $request->input('deployment'),
            'created_at' => now()->toDateString()
        ];

        $usersSavedData = UsersModel::create($userData);

        return response([
            "status" => "success",
            "code" => "BIMSAPI0008",
            "msg" => "Saving new user succcessful",
            "data" => $usersSavedData,
        ], 200);
    }
}
