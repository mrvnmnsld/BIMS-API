<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Validator;

class APIUserController extends Controller
{
    public function checkCreds(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'username' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0006", // validator fails
                'errors' => $validator->errors()
            ], 422);
        }

        $results = DB::select('SELECT * FROM user_tbl WHERE username = ?', [$request->input('username')]);

        if (count($results) == 0 || !Hash::check($request->input('password'), $results[0]->password)) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0003", //invalid credentials
            ], 401);
        }

        return response([
            "status" => "success",
            "code" => "BIMSAPI0001", // success
            "data" => [
                "isProcedd" => 1,
                "msg" => "Credential is correct. proceed to dashboard",
                'userData' => [
                    "username" => $results[0]->username,
                    "id" => $results[0]->id,
                    "email" => $results[0]->email,
                    "name" => $results[0]->name,
                    "dob" => $results[0]->dob,
                    "designation" => $results[0]->designation,
                    "deployement" => $results[0]->deployment,
                    "created_at" => $results[0]->created_at
                ]
            ]
        ],200);


    }
}
