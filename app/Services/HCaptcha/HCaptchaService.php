<?php
namespace App\Services\HCaptcha;

class HCaptchaService {

    static public function verify($token)
    {
        $data = [
            'secret' => config('hcaptcha.hcaptcha_secret_Key'),
            'response' => $token
        ];

        $verify = curl_init();
        curl_setopt($verify, CURLOPT_URL, "https://hcaptcha.com/siteverify");
        curl_setopt($verify, CURLOPT_POST, true);
        curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($verify);
        
        $responseData = json_decode($response);
        return $responseData->success ? $responseData->success : false;
    }
}
