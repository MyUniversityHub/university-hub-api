<?php

use Illuminate\Support\Facades\Http;

if (!function_exists('passportResponse')) {
    function passportResponse($email = null, $password = null, $grantType = 'password', $refreshToken = null): \Illuminate\Http\Client\Response
    {
        $data = [
            'grant_type' => $grantType,
            'client_id' => env('PASSPORT_PASSWORD_CLIENT_ID'),
            'client_secret' => env('PASSPORT_PASSWORD_SECRET'),
            'scope' => ''
        ];
        if (!empty($email)) {
            $data['username'] = $email;
        }
        if (!empty($password)) {
            $data['password'] = $password;
        }
        if (!empty($refreshToken)) {
            $data['refresh_token'] = $refreshToken;
        }
        return Http::post(env('APP_URL') . '/oauth/token', $data);

    }
}
