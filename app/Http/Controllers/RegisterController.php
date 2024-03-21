<?php

namespace App\Http\Controllers;

use App\Http\Controllers\RegisterController_Helpers\Helpers;
use App\Models\Register;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class RegisterController extends Controller
{
    public $helpers;

    public function __construct()
    {
        $this->helpers = new Helpers();
    }

    public function index()
    {
        return Register::all();
    }

    public function store(Request $request)
    {
        $url = $request->url;

        if ($this->helpers->validateUrl($url) != null) {
            return $this->helpers->validateUrl($url);
        }

        $url_parts = parse_url($url);
        if (isset($url_parts['query'])) {
            $params = explode('&', $url_parts['query']);
            foreach ($params as $param) {
                $item = explode('=', $param);
                if($item[1] == "") {
                    return response()->json('ERROR: invalid url, query param empty', 400);
                }
            }
        }

        if ($this->helpers->siteStatus($url) != null) {
            return $this->helpers->siteStatus($url);
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

        if ($this->helpers->validateUrl($url) != null) {
            return $this->helpers->validateUrl($url);
        }

        if ($this->helpers->siteStatus($url) != null) {
            return $this->helpers->siteStatus($url);
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


}
