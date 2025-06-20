<?php

namespace App\Http\Controllers\Api\AppMobile;

use App\Http\Controllers\Controller;
use App\Models\GeneralInfo;

class GeneralInfoController extends Controller
{
    public function index()
    {
        return response()->json(GeneralInfo::all());
    }
}
