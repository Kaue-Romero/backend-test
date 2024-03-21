<?php

namespace App\Http\Controllers;

use App\Http\Controllers\RegisterLogController_Helpers\Helpers;
use App\Models\Register;
use Illuminate\Http\Request;

class RegisterLogController extends Controller
{
    public $helpers;

    public function __construct()
    {
        $this->helpers = new Helpers();
    }

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

        return $this->helpers->logRequest($request, $redirect, $requestString);
    }

    public function stats(Request $_request, $redirect)
    {
        $logs = Register::where('code', $redirect)->with('logs')->get();

        if (count($logs) === 0) {
            return response()->json(['error' => 'Invalid token'], 404);
        }

        $logs[0]->redirects = $this->helpers->getAccessQuery($logs[0]->logs);

        $logs[0]->top_referer = $this->helpers->getTopReferer($logs[0]->logs);

        $logs[0]->last_10_days = $this->helpers->getLast10Days($logs[0]->logs);

        return response()->json($logs);
    }

    public function logs(Request $_request, $redirect)
    {
        $logs = Register::where('code', $redirect)->with('logs')->get();

        return response()->json($logs);
    }
}
