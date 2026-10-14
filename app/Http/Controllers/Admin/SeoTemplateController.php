<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SeoTemplateRequest;
use App\Models\Language;
use App\Models\SeoTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SeoTemplateController extends Controller
{
    private const PER_PAGE = 20;

    public function index(): Response
    {
        $templates = SeoTemplate::query()
            ->with('translations:id,seo_template_id,lang')
            ->withCount('categoryTranslations')
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->through(fn (SeoTemplate $template) => [
                'id' => $template->id,
                'name' => $template->name,
                'languages' => $template->translations->pluck('lang')->sort()->values(),
                'categories_count' => $template->category_translations_count,
            ]);

        return Inertia::render('seo-templates/IndexSeoTemplates', [
            'templates' => $templates,
            'languagesCount' => Language::query()->count(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('seo-templates/CreateSeoTemplate', [
            'languages' => $this->languages(),
        ]);
    }

    public function store(SeoTemplateRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $template = SeoTemplate::create($request->safe()->only(['name']));

            $template->translations()->createMany($request->filledTranslations());
        });

        return redirect()
            ->route('admin.seo-templates.index')
            ->with('success', 'SEO-шаблон добавлен.');
    }

    public function edit(SeoTemplate $seoTemplate): Response
    {
        $seoTemplate->load('translations');

        return Inertia::render('seo-templates/EditSeoTemplate', [
            'template' => [
                'id' => $seoTemplate->id,
                'name' => $seoTemplate->name,
                'translations' => $seoTemplate->translations->keyBy('lang'),
            ],
            'languages' => $this->languages(),
        ]);
    }

    public function update(SeoTemplateRequest $request, SeoTemplate $seoTemplate): RedirectResponse
    {
        DB::transaction(function () use ($request, $seoTemplate) {
            $seoTemplate->update($request->safe()->only(['name']));

            foreach ($request->filledTranslations() as $translation) {
                $seoTemplate->translations()->updateOrCreate(
                    ['lang' => $translation['lang']],
                    $translation,
                );
            }

            $cleared = $request->clearedLanguageCodes();

            $seoTemplate->translations()->whereIn('lang', $cleared)->delete();

            // Without a translation in their language the template no longer applies to these categories
            $seoTemplate->categoryTranslations()->whereIn('lang', $cleared)->update(['seo_template_id' => null]);
        });

        return redirect()
            ->route('admin.seo-templates.index')
            ->with('success', 'SEO-шаблон сохранён.');
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{id: int, code: string, name: string}>
     */
    private function languages()
    {
        return Language::query()->orderBy('id')->get(['id', 'code', 'name']);
    }
}
