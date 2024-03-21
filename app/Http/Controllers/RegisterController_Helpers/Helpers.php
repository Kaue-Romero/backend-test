<?php

namespace App\Http\Controllers\RegisterController_Helpers;

class Helpers
{
    public function siteStatus($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $httpcode = (string) $httpcode;
        if ($httpcode === "0") {
            return response()->json('ERROR: DNS problem', 400);
        }
        if ($httpcode != "200") {
            return response()->json('ERROR: site is down or redirecting with code ' . $httpcode, 400);
        }

        return null;
    }

    public function validateUrl($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
            return response()->json('ERROR: insert a valid url', 400);
        }

        if (strpos($url, env('APP_URL')) !== false) {
            return response()->json('ERROR: Do not insert the APP url', 400);
        }

        if (strpos($url, 'https://') === false) {
            return response()->json('ERROR: insert a https url', 400);
        }

        return null;
    }
}
