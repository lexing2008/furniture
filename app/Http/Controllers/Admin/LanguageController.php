<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLanguageRequest;
use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LanguageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('languages/IndexLanguages', [
            'languages' => Language::query()
                ->orderBy('code')
                ->paginate(20, ['id', 'code', 'name', 'name_en', 'created_at']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('languages/CreateLanguage');
    }

    public function store(StoreLanguageRequest $request): RedirectResponse
    {
        Language::create($request->validated());

        return redirect()
            ->route('admin.languages.index')
            ->with('success', 'Язык добавлен.');
    }
}
