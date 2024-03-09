<?php

namespace App\Http\Controllers;

use App\Models\Register;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class RegisterController extends Controller
{
    public function index()
    {
        return Register::all();
    }

    public function store(Request $request)
    {
        $url = $request->url;

        if ($this->validateUrl($url) != null) {
            return $this->validateUrl($url);
        }

        $url_parts = parse_url($url);
        if (isset($url_parts['query'])) {
            $params = explode('&', $url_parts['query']);
            foreach ($params as $param) {
                $item = explode('=', $param);
                if($item[1] == "") {
                    //strpos of the param
                    $pos = strpos($url, $param);
                    //remove the param from the url plus a "&"
                    $url = substr_replace($url, '', $pos, strlen($param) + 1);
                }
            }
        }

        if ($this->siteStatus($url) != null) {
            return $this->siteStatus($url);
        }

        $validateData = new Validator();
        $validateData = $validateData::make(["url" => $url], [
            'url' => 'required|unique:register|url'
        ]);

        if ($validateData->fails()) {
            return response()->json($validateData->errors()->first(), 400);
        }

        $register = new Register();
        $site_status = true;
        $register->status = $site_status;
        $register->url = $url;
        $register->last_access = now();
        $register->save();
        $register->code = Hashids::encode($register->id);
        $register->save();

        return response()->json($register, 201);
    }

    public function update(Request $request, $id)
    {
        $url = $request->url;


        if ($url == null) {
            return response()->json('url is required', 400);
        }

        if ($this->validateUrl($url) != null) {
            return $this->validateUrl($url);
        }

        if ($this->siteStatus($url) != null) {
            return $this->siteStatus($url);
        }

        $register = Register::find($id);

        if ($register == null) {
            return response()->json('register not found', 404);
        }

        $status = $register->status;

        if (isset($request->status)) {
            $status = $request->status;
        }

        $register->url = $url;
        $register->status = filter_var($status, FILTER_VALIDATE_BOOLEAN);
        $register->save();

        return response()->json($register, 200);
    }

    public function destroy($id)
    {
        $register = Register::find($id);
        $register->status = false;
        $register->save();
        $register->delete();
    }

    public function restore($id)
    {
        $register = Register::withTrashed()->find($id);
        $register->status = true;
        $register->save();
        $register->restore();
    }

    private function siteStatus($url)
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

    private function validateUrl($url)
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
