<ul>
    @forelse ($areas as $item)
        <li @class([
            'area',
            'is-open' => $item['region']->id === $region->id || $item['cities']->contains('id', $region->id),
        ])>
            @if ($item['cities']->isEmpty())
                <a href="{{ $item['region']->url() }}" class="link">{{ app()->getLocale() === 'en' ? $item['region']->name_en : $item['region']->name }}</a>
            @else
                <button type="button" class="area-button" data-area-toggle>
                    <span>{{ app()->getLocale() === 'en' ? $item['region']->name_en : $item['region']->name }}</span>
                    <span class="arrow mr-1.5" aria-hidden="true">▸</span>
                </button>

                <div class="cities hidden mt-1 mb-2 ml-4">
                    <a href="{{ $item['region']->url() }}"
                        @class([
                            'link text-sm',
                            'font-bold' => $item['region']->id === $region->id,
                        ])
                    >{{ app()->getLocale() === 'en' ? $item['region']->name_en : $item['region']->name }}</a>

                    @foreach ($item['cities'] as $city)
                        <div class="py-1">
                            <a href="{{ $city->url() }}"
                                @class([
                                    'link text-sm',
                                    'font-bold text-black border-b-transparent!' => $city->id === $region->id,
                                ])
                            >{{ app()->getLocale() === 'en' ? $city->name_en : $city->name }}</a>
                        </div>
                    @endforeach
                </div>
            @endif
        </li>
    @empty
        <li class="py-3 text-sm text-gray-500">{{ __('Нет доступных регионов.') }}</li>
    @endforelse
</ul>
