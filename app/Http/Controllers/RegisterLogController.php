<?php

namespace App\Http\Controllers;

use App\Models\RegisterLog;
use Illuminate\Http\Request;

class RegisterLogController extends Controller
{
    public function index(Request $request, $token)
    {
        return $this->logRequest($request, $token);
    }

    private function logRequest(Request $request, string $token)
    {
        $log = new RegisterLog();
        $log->id = $token;
        $log->ip = $request->ip();
        $log->user_agent = $request->userAgent();
        //Referrer from header
        $log->header = $request->header('referer');
        $log->query_params = json_encode($request->query());

        dd($log);
        //$log->save();
    }
}
