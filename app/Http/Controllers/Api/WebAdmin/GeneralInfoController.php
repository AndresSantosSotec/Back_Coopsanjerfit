<?php

namespace App\Http\Controllers\Api\WebAdmin;

use App\Http\Controllers\Controller;
use App\Models\GeneralInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GeneralInfoController extends Controller
{
    public function index()
    {
        return response()->json(GeneralInfo::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([

            'title'       => 'required|string',
            'content'     => 'required|string',
            'category'    => 'nullable|string',
            'image'       => 'sometimes|file|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_path'  => 'sometimes|file|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video'       => 'sometimes|file|mimes:mp4,mov,avi,wmv|max:20480',
            'video_path'  => 'sometimes|file|mimes:mp4,mov,avi,wmv|max:20480',

        ]);

        $data = collect($validated)->only(['title', 'content', 'category'])->all();


        $imageFile = $request->file('image') ?? $request->file('image_path');
        if ($imageFile) {
            $data['image_path'] = $imageFile->store('general_images', 'public');
        }

        $videoFile = $request->file('video') ?? $request->file('video_path');
        if ($videoFile) {
            $data['video_path'] = $videoFile->store('general_videos', 'public');

        }

        $info = GeneralInfo::create($data);

        return response()->json($info, 201);
    }

    public function show(GeneralInfo $info)
    {
        return response()->json($info);
    }

    public function update(Request $request, GeneralInfo $info)
    {
        $validated = $request->validate([

            'title'       => 'sometimes|required|string',
            'content'     => 'sometimes|required|string',
            'category'    => 'nullable|string',
            'image'       => 'sometimes|file|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_path'  => 'sometimes|file|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video'       => 'sometimes|file|mimes:mp4,mov,avi,wmv|max:20480',
            'video_path'  => 'sometimes|file|mimes:mp4,mov,avi,wmv|max:20480',

        ]);

        $data = collect($validated)->only(['title', 'content', 'category'])->all();


        $imageFile = $request->file('image') ?? $request->file('image_path');
        if ($imageFile) {
            if ($info->image_path) {
                Storage::disk('public')->delete($info->image_path);
            }
            $data['image_path'] = $imageFile->store('general_images', 'public');
        }

        $videoFile = $request->file('video') ?? $request->file('video_path');
        if ($videoFile) {
            if ($info->video_path) {
                Storage::disk('public')->delete($info->video_path);
            }
            $data['video_path'] = $videoFile->store('general_videos', 'public');

        }

        $info->update($data);

        return response()->json($info);
    }

    public function destroy(GeneralInfo $info)
    {
        if ($info->image_path) {
            Storage::disk('public')->delete($info->image_path);
        }

        if ($info->video_path) {
            Storage::disk('public')->delete($info->video_path);
        }

        $info->delete();

        return response()->json(null, 204);
    }
}
