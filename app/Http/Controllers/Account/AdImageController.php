<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\TmpFile;
use App\Services\AdImageService;
use App\Support\AdImagePath;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdImageController extends Controller
{
    public function store(Request $request, AdImageService $imageService): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
        ], [
            'image.required' => __('Выберите изображение.'),
            'image.image' => __('Файл должен быть изображением.'),
            'image.mimes' => __('Поддерживаются JPG, PNG, WebP и GIF.'),
            'image.max' => __('Файл не должен быть больше 10 МБ.'),
        ]);

        $tmp = $imageService->upload($request->file('image'), $request->user()->id);

        return response()->json([
            'id' => $tmp->id,
            'url' => url('/storage/'.ltrim($tmp->path, '/')),
            'thumb_630_url' => url('/storage/'.ltrim(AdImagePath::medium($tmp->path), '/')),
            'thumb_100_url' => url('/storage/'.ltrim(AdImagePath::small($tmp->path), '/')),
        ], 201);
    }

    public function destroy(Request $request, AdImageService $imageService): JsonResponse
    {
        $file = TmpFile::query()
            ->whereKey((int) $request->route('tmpFile'))
            ->where('user_id', $request->user()->id)
            ->where('type', TmpFile::TYPE_AD_IMAGE)
            ->first();

        if ($file === null) {
            return response()->json([
                'message' => __('Временный файл не найден.'),
            ], 404);
        }

        $imageService->deleteTemporary($file);

        return response()->json(null, 204);
    }
}
