<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\Favorite;
use App\Models\Region;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class FavoriteService
{
    private const COOKIE = 'favorite_visitor';

    /**
     * IDs of the ads favorited by the current visitor.
     *
     * @return Collection<int, int>
     */
    public function adIds(): Collection
    {
        return $this->query()
            ->pluck('ad_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }

    /**
     * Favorites of the current visitor with the ad, saved region and company.
     */
    public function query(): Builder
    {
        $userId = auth()->id();

        if ($userId !== null) {
            return Favorite::query()->where('user_id', $userId);
        }

        $token = $this->guestToken();

        return Favorite::query()->where('visitor_token', $token ?? '__no_favorite_visitor__');
    }

    /**
     * Toggle an ad in favorites. The region is stored only when the ad is added.
     */
    public function toggle(Ad $ad, Region $region): bool
    {
        $favorite = $this->query()->where('ad_id', $ad->id)->first();

        if ($favorite !== null) {
            $favorite->delete();

            return false;
        }

        $userId = auth()->id();
        $token = null;

        if ($userId === null) {
            $token = $this->guestToken() ?? (string) Str::uuid();

            Cookie::queue(Cookie::make(
                self::COOKIE,
                $token,
                60 * 24 * 365 * 5,
                '/',
                '.'.config('app.domain'),
                request()->isSecure(),
                true,
                false,
                'lax',
            ));
        }

        Favorite::create([
            'ad_id' => $ad->id,
            'region_id' => $region->id,
            'user_id' => $userId,
            'visitor_token' => $token,
        ]);

        return true;
    }

    private function guestToken(): ?string
    {
        $token = request()->cookie(self::COOKIE);

        return is_string($token) && $token !== '' ? $token : null;
    }
}
