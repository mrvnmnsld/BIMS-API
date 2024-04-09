<?php

namespace App\Http\Middleware;

use Closure;

class VerifyTokenMiddleware 
{
    public function handle($request, Closure $next)
    {
        $token = $request->header('Authorization');
        $secretKey = '0110110101100001011100100111011001110011';

        if (!$token) {
            return response([
                "status" => "failed. please add token header",
                "code" => "BIMSAPI0010", //invalid token
            ], 401);
        }


        list($header, $payload, $signature) = explode('.', $token);

        $base64UrlHeader = strtr($header, '-_', '+/');
        $base64UrlPayload = strtr($payload, '-_', '+/');

        $decodedHeader = base64_decode($base64UrlHeader);
        $decodedPayload = base64_decode($base64UrlPayload);

        $decodedSignature = base64_decode($signature);

        $calculatedSignature = hash_hmac('sha256', "$header.$payload", $secretKey, true);

        if (hash_equals($calculatedSignature, $decodedSignature)) {
            $decodedData = json_decode($decodedPayload, true);

            if (isset($decodedData['exp']) && $decodedData['exp'] < time()) {
                return response([
                    "status" => "failed",
                    "code" => "BIMSAPI0004", //expired
                ], 401);
            } else {
                // Token is valid
                $request->decodedData = $decodedData; // Attach decoded data to request
                return $next($request);
            }
        } else {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0005", //invalid token
            ], 401);
        }
    }
}