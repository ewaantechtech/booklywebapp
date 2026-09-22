<?php

namespace App\Services;

use Illuminate\Support\Facades\App;

class SendSmsService
{
    /**
     * Send a sms message to the given mobile.
     *
     * @param string $mobile
     * @param String $msg
     * @return \Illuminate\Http\JsonResponse
     */
    public static function toSms($number, $msg)
    {
        try {
            
            \Log::info('toSms called', ['input_number' => $number, 'env' => App::environment()]);

            if (App::environment('production')) {
                // $countryCode = '971'; // Example: UAE
                //  $phone = ltrim($number, '0');
                // $fullPhone = $countryCode . $phone;

                //new edits
                $phone = preg_replace('/[^0-9]/', '', $number);
                $phone = ltrim($phone, '0');
                if (!str_starts_with($phone, '966')) {
                    $phone = '966' . $phone;
                }
                //end
                $fields = array(
                    "userName" => config('sms.msegat.userName'),
                    "numbers" => $phone,
                    "userSender" => config('sms.msegat.userName'),
                    "apiKey" => config('sms.msegat.key'),
                    "msg" => $msg,
                    "msgEncoding" => "UTF8"
                );
                $postvars = http_build_query($fields);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, config('sms.msegat.link'),);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/json",
                ));
                $response = curl_exec($ch);
                if (curl_errno($ch)) {
                    $curlError = curl_error($ch);
                    curl_close($ch);
                    \Log::error('toSms curl error', ['phone' => $phone, 'error' => $curlError]);
                    return response()->json(['status' => FALSE, 'message' => $curlError]);
                }
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if (intval($httpcode) != 200) {
                    \Log::error('toSms non-200 response from Msegat', ['phone' => $phone, 'http_code' => $httpcode, 'response' => $response]);
                    return response()->json(['status' => FALSE, 'message' => $httpcode]);
                }
                $text = '';
                $start = '{';
                $end = '}';
                $pattern = sprintf('/%s(.+?)%s/ims', preg_quote($start, '/'), preg_quote($end, '/'));
                if (preg_match($pattern, $response, $matches)) {
                    list(, $match) = $matches;
                    $text = $match;
                }
                $text = "{" . $text . "}";
                $response_array = json_decode($text);
                if (intval($response_array->code ?? 0) != 1) {
                    \Log::error('toSms Msegat rejected message', ['phone' => $phone, 'code' => $response_array->code ?? null, 'message' => $response_array->message ?? null, 'response' => $response]);
                    return response()->json(['status' => FALSE, 'message' => $response_array->message ?? 'SMS gateway error']);
                }
                \Log::info('toSms sent', ['phone' => $phone, 'code' => $response_array->code]);
            }
            return response()->json(['status' => TRUE]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public static function sendSms($number, $msg)
    {
        try {
            if (App::environment('production')) {
                $fields = array(
                    "userName" => config('sms.msegat.userName'),
                    "numbers" => $number,
                    "userSender" => config('sms.msegat.userName'),
                    "apiKey" => config('sms.msegat.key'),
                    "msg" => $msg,
                    "msgEncoding" => "UTF8"
                );
                $postvars = http_build_query($fields);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, config('sms.msegat.link'),);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/json",
                ));
                $response = curl_exec($ch);
                if (curl_errno($ch)) {
                    return response()->json(['status' => FALSE, 'message' => $ch]);
                }
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if (intval($httpcode) != 200) {
                    return response()->json(['status' => FALSE, 'message' => $httpcode]);
                }
                $text = '';
                $start = '{';
                $end = '}';
                $pattern = sprintf('/%s(.+?)%s/ims', preg_quote($start, '/'), preg_quote($end, '/'));
                if (preg_match($pattern, $response, $matches)) {
                    list(, $match) = $matches;
                    $text = $match;
                }
                $text = "{" . $text . "}";
                $response_array = json_decode($text);
                if (intval($response_array->code) != 1) {
                    return response()->json(['status' => FALSE, 'message' => $response_array->message]);
                }
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
