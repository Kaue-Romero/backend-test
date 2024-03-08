<?php

namespace App\Http\Controllers;

use App\Models\Register;
use App\Models\RegisterLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class RegisterLogController extends Controller
{
    public function index(Request $request, $redirect)
    {
        $requestParams = $request->query();
        $requestString = '';
        foreach ($requestParams as $key => $value) {
            $value = htmlspecialchars($value);

            $requestString .= $key . '=' . $value;
            if ($key !== array_key_last($requestParams)) {
                $requestString .= '&';
            }
        }

        return $this->logRequest($request, $redirect, $requestString);
    }

    public function stats(Request $_request, $redirect)
    {
        $logs = Register::where('code', $redirect)->with('logs')->get();

        $total = count($logs[0]->logs);
        $unique = count($logs[0]->logs->groupBy('ip'));

        $access = ['total' => $total, 'uniques' => $unique];

        $logs[0]->redirects = $access;
        $logs[0]->top_referer = $logs[0]->logs->groupBy('header')->map(function ($item, $key) {
            return [
                'header' => $key,
                'count' => count($item)
            ];
        })->sortDesc()->values()->first();

        return response()->json($logs);
    }

    private function logRequest(Request $request, string $token, string $requestString)
    {
        $redirect = Register::where('code', $token)->first();

        if (!$redirect) {
            return response()->json(['error' => 'Invalid token'], 404);
        }

        $log = new RegisterLog();
        $log->ip = $request->ip();
        $log->user_agent = $request->userAgent();
        $log->header = $request->header('referer');
        $log->query_params = $requestString;
        $log->redirect_id = $redirect->id;
        $log->save();

        $destination = $this->checkForExistantQueryString($redirect->url, $requestString);

        return Redirect::to($destination);
    }

    private function checkForExistantQueryString($queryString, $requestString)
    {
        if (strpos($queryString, '?') !== false) {
            if (substr($queryString, -1) !== '?') {
                $queryString .= '&' . $requestString;
            } else {
                $queryString .= $requestString;
            }
        } else {
            $queryString .= '?' . $requestString;
        }

        return $queryString;
    }
}
