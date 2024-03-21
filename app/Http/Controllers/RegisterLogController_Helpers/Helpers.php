<?php

namespace App\Http\Controllers\RegisterLogController_Helpers;

use App\Models\Register;
use App\Models\RegisterLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class Helpers
{
    public function logRequest(Request $request, string $token, string $requestString)
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

    public function checkForExistantQueryString($queryString, $requestString)
    {
        if (!$requestString) {
            return $queryString;
        }

        if (strpos($queryString, '?') !== false) {
            if (substr($queryString, -1) !== '?') {
                $queryString .= '&' . $requestString;
            } else {
                $queryString .= $requestString;
            }
        } else {
            $queryString .= '?' . $requestString;
        }

        //get the params from the query string
        $queryParams = explode('?', $queryString)[1];
        $queryParams = explode('&', $queryParams);

        $requestParams = explode('&', $requestString);

        foreach ($queryParams as $key => $value) {
            $param = explode('=', $value);
            foreach ($requestParams as $requestParam) {
                $requestParam = explode('=', $requestParam);
                if ($param[0] === $requestParam[0]) {
                    $param[1] = $requestParam[1];
                }
            }
            $queryParams[$key] = implode('=', $param);
        }

        $queryParams = array_unique($queryParams);

        $queryString = explode('?', $queryString)[0] . '?' . implode('&', $queryParams);

        return $queryString;
    }

    public function getAccessQuery($logs)
    {
        $total = count($logs);
        $unique = count($logs->groupBy('ip'));
        return ['total' => $total, 'uniques' => $unique];
    }

    public function getTopReferer($logs)
    {
        $topReferer = $logs->groupBy('header')->map(function ($item, $key) {
            return ['referer' => $key, 'total' => count($item)];
        })->sortByDesc('total')->values()->first();

        return $topReferer;
    }

    public function getLast10Days($logs)
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
