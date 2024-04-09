<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Validator;

class TokenHandlerController extends Controller
{

    //VIEW ALL SALES
    public function generateToken(Request $request)
    {

        $secretKey = '0110110101100001011100100111011001110011';

        $validator = Validator::make($request->all(), [
            'password' => 'required|max:10',
            'username' => 'required|max:10',
        ]);

        if ($validator->fails()) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0006", // validator fails
                'errors' => $validator->errors()
            ], 422);
        }

        $results = DB::select('SELECT * FROM api_creds_tbl WHERE username = ?', [$request->input('username')]);

        if (count($results) == 0 || !Hash::check($request->input('password'), $results[0]->password)) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0003", //invalid credentials
            ], 401);
        }

        // $expTime = time() + (60 * 5); //return this to 5 minutes
        // $expTime = time() + (60 * .30);
        $expTime = time() + (60 * 100);
        
        $payload = [
            "username" => $request->input('username'),
            "password" => $request->input('password'),
            "exp" =>  $expTime
        ];

        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', "$header.$payload", $secretKey, true);
        $signature = base64_encode($signature);
        $jwtToken = "$header.$payload.$signature";

        return response([
            "status" => "success",
            "code" => "BIMSAPI0001", // success
            "data" => [
                'token'=>$jwtToken,
                'headerName'=>"Authorization",
                'expTimestamp'=>$expTime,
            ]
        ],200);

    }

    public function generatePassword()
    {
        $password = "test@123";
        return Hash::make($password);
    }
}
