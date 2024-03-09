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

        if (count($logs) === 0) {
            return response()->json(['error' => 'Invalid token'], 404);
        }

        $logs[0]->redirects = $this->getAccessQuery($logs[0]->logs);

        $logs[0]->top_referer = $this->getTopReferer($logs[0]->logs);

        $logs[0]->last_10_days = $this->getLast10Days($logs[0]->logs);

        return response()->json($logs);
    }

    public function logs(Request $_request, $redirect)
    {
        $logs = Register::where('code', $redirect)->with('logs')->get();

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

    private function getAccessQuery($logs)
    {
        $total = count($logs);
        $unique = count($logs->groupBy('ip'));
        return ['total' => $total, 'uniques' => $unique];
    }

    private function getTopReferer($logs)
    {
        $topReferer = $logs->groupBy('header')->map(function ($item, $key) {
            return ['referer' => $key, 'total' => count($item)];
        })->sortByDesc('total')->values()->first();

        return $topReferer;
    }

    private function getLast10Days($logs)
    {
        //Uma array com total de acessos dos últimos 10 dias (Ex: [{ "date": "2021-01-01": "total": 10, "unique": 8 }]) with where date > now() - 10
        $last10Days = $logs->where('created_at', '>', now()->subDays(10))->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        })->map(function ($item, $key) {
            return ['date' => $key, 'total' => count($item), 'unique' => count($item->groupBy('ip'))];
        })->values();

        return $last10Days;
    }
}
