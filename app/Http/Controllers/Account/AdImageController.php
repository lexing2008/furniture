<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\TmpFile;
use App\Services\AdImageService;
use App\Support\AdImagePath;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Throwable;

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

        return $this->payload($imageService->upload($request->file('image'), $request->user()->id));
    }

    public function storeUrl(Request $request, AdImageService $imageService): JsonResponse
    {
        $data = $request->validate([
            'url' => ['required', 'string', 'url:http,https', 'max:2048'],
        ], [
            'url.required' => __('Вставьте ссылку на изображение.'),
            'url.url' => __('Укажите корректную ссылку на изображение.'),
        ]);

        $host = parse_url($data['url'], PHP_URL_HOST);

        if (is_string($host)
            && filter_var($host, FILTER_VALIDATE_IP)
            && ! filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            throw ValidationException::withMessages([
                'url' => __('Этот адрес недоступен для загрузки.'),
            ]);
        }

        try {
            $response = Http::timeout(15)
                ->withoutRedirecting()
                ->withHeaders(['User-Agent' => 'FurnitureWorld image loader'])
                ->get($data['url']);
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'url' => __('Не удалось загрузить изображение по ссылке.'),
            ]);
        }

        if (! $response->successful()) {
            throw ValidationException::withMessages([
                'url' => __('Не удалось загрузить изображение по ссылке.'),
            ]);
        }

        $body = $response->body();

        if (strlen($body) > 10 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'url' => __('Файл не должен быть больше 10 МБ.'),
            ]);
        }

        $info = @getimagesizefromstring($body);
        $mime = is_array($info) ? ($info['mime'] ?? null) : null;

        if ($mime === null || ! in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
            throw ValidationException::withMessages([
                'url' => __('Файл по ссылке не является изображением.'),
            ]);
        }

        $temporaryPath = tempnam(sys_get_temp_dir(), 'ad-image-url-');

        if ($temporaryPath === false) {
            throw ValidationException::withMessages([
                'url' => __('Не удалось загрузить изображение по ссылке.'),
            ]);
        }

        try {
            if (file_put_contents($temporaryPath, $body) === false) {
                throw ValidationException::withMessages([
                    'url' => __('Не удалось загрузить изображение по ссылке.'),
                ]);
            }

            $tmp = $imageService->uploadFromPath($temporaryPath, $request->user()->id);
        } finally {
            @unlink($temporaryPath);
        }

        return $this->payload($tmp);
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

    private function payload(TmpFile $tmp): JsonResponse
    {
        return response()->json([
            'id' => $tmp->id,
            'url' => url('/storage/'.ltrim($tmp->path, '/')),
            'thumb_630_url' => url('/storage/'.ltrim(AdImagePath::medium($tmp->path), '/')),
            'thumb_325_url' => url('/storage/'.ltrim(AdImagePath::listing($tmp->path), '/')),
            'thumb_100_url' => url('/storage/'.ltrim(AdImagePath::small($tmp->path), '/')),
        ], 201);
    }
}
