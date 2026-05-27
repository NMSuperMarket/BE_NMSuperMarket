<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images', 'public');
            $url = asset('storage/' . $path);
            return response()->json([
                'success' => true,
                'url' => $url,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Lỗi upload ảnh'
        ], 400);
    }
}
