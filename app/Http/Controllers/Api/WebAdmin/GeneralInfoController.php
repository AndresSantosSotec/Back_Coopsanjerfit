<?php

namespace App\Http\Controllers\Api\WebAdmin;

use App\Http\Controllers\Controller;
use App\Models\GeneralInfo;
use Illuminate\Http\Request;

class GeneralInfoController extends Controller
{
    public function index()
    {
        return response()->json(GeneralInfo::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'category' => 'nullable|string',
            'image_path' => 'nullable|string',
        ]);

        $info = GeneralInfo::create($data);
        return response()->json($info, 201);
    }

    public function show(GeneralInfo $info)
    {
        return response()->json($info);
    }

    public function update(Request $request, GeneralInfo $info)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string',
            'content' => 'sometimes|required|string',
            'category' => 'nullable|string',
            'image_path' => 'nullable|string',
        ]);

        $info->update($data);
        return response()->json($info);
    }

    public function destroy(GeneralInfo $info)
    {
        $info->delete();
        return response()->json(null, 204);
    }
}
