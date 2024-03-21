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
        $logs = Register::where('code', $redirect)->with('logs')->first();

        if (empty($logs)) {
            return response()->json(['error' => 'Invalid token'], 404);
        }

        $logs->redirects = $this->helpers->getAccessQuery($logs->logs);

        $logs->top_referer = $this->helpers->getTopReferer($logs->logs);

        $logs->last_10_days = $this->helpers->getLast10Days($logs->logs);

        return response()->json($logs);
    }

    public function logs(Request $_request, $redirect)
    {
        $logs = Register::where('code', $redirect)->with('logs')->get();

        return response()->json($logs);
    }
}
