<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The main page of the account: ad statistics, profile completeness and recent ads.
 */
class CompanyController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $request->user()->company()
            ->with('region')
            ->withCount([
                'schedules as schedules_count',
                'scheduleExceptions as schedule_exceptions_count',
                'phones as phones_count',
            ])
            ->firstOrFail();

        $counts = Ad::countsForCompany($company->id);

        $recentAds = $company->ads()
            ->with(['images', 'category'])
            ->latest('id')
            ->take(9)
            ->get()
            ->map(fn (Ad $ad) => [
                'id' => $ad->id,
                'title' => $ad->title,
                'price' => (float) $ad->price,
                'currency' => $ad->currency,
                'thumb_url' => $ad->images->first()?->thumbnailUrl(100),
                'published_at' => $ad->published_at?->format('d.m.Y'),
                'is_active' => $ad->is_active,
                'is_moderated' => $ad->is_moderated,
                'is_banned' => $ad->is_banned,
            ])
            ->values();

        $tasks = [
            [
                'key' => 'schedule',
                'title' => 'График работы',
                'description' => 'Укажите часы работы компании.',
                'href' => route('account.schedule'),
                'done' => $company->schedules_count > 0,
            ],
            [
                'key' => 'exceptions',
                'title' => 'Нерабочие и праздничные дни',
                'description' => 'Добавьте праздники и нерабочие дни.',
                'href' => route('account.schedule').'#exceptions',
                'done' => $company->schedule_exceptions_count > 0,
            ],
            [
                'key' => 'delivery',
                'title' => 'Условия доставки',
                'description' => 'Опишите способы и стоимость доставки.',
                'href' => route('account.delivery'),
                'done' => $this->hasText($company->delivery),
            ],
            [
                'key' => 'payment',
                'title' => 'Способы оплаты',
                'description' => 'Опишите доступные способы оплаты.',
                'href' => route('account.payment'),
                'done' => $this->hasText($company->payment),
            ],
            [
                'key' => 'socials',
                'title' => 'Соцсети',
                'description' => 'Добавьте ссылки на страницы компании.',
                'href' => route('account.socials.edit'),
                'done' => collect($company->socials ?? [])->contains(
                    fn ($url) => is_string($url) && trim($url) !== ''
                ),
            ],
            [
                'key' => 'contacts',
                'title' => 'Контактная информация',
                'description' => 'Укажите телефон и email компании.',
                'href' => route('account.contacts.edit'),
                'done' => $company->phones_count > 0 || trim((string) $company->email) !== '',
            ],
            [
                'key' => 'description',
                'title' => 'О компании',
                'description' => 'Добавьте описание компании для посетителей.',
                'href' => route('account.general'),
                'done' => $this->hasText($company->description),
            ],
        ];

        $completed = collect($tasks)->where('done', true)->count();
        $progress = $tasks === [] ? 100 : (int) round($completed / count($tasks) * 100);

        return Inertia::render('Index', [
            'company' => [
                'name' => $company->display_name ?: $company->legal_name,
                'region' => $company->region?->name,
                'site_url' => $company->region?->url(),
            ],
            'counts' => $counts,
            'tasks' => $tasks,
            'completed_tasks' => $completed,
            'progress' => $progress,
            'recent_ads' => $recentAds,
        ]);
    }

    private function hasText(?string $html): bool
    {
        $text = html_entity_decode(strip_tags($html ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\u{00A0}", ' ', $text);

        return trim(preg_replace('/\s+/u', ' ', $text) ?? '') !== '';
    }
}
