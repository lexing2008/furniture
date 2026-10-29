@extends('layouts.site')

@php
    $pageNumber = $items->currentPage();
    $pageSuffix = $pageNumber > 1 ? __(' — Страница :page', ['page' => $pageNumber]) : '';
@endphp

@section('title', __('Избранное').$pageSuffix)
@section('description', __('Товары и услуги, которые вы сохранили.').$pageSuffix)

@section('content')
    <div class="space-y-6 pb-10">
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-green-first to-green-second px-5 py-7 text-white shadow-lg shadow-green-first/15 sm:px-7">
            <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-white/10 blur-3xl"></div>
            <div class="relative">
                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-white/70">{{ __('Личный список') }}</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">{{ __('Избранное') }}{{ $pageSuffix }}</h1>
                <p class="mt-2 text-sm text-white/80">{{ __('Товары и услуги, которые вы сохранили.') }}</p>
            </div>
        </section>

        @if($items->total() > 0)
            <p class="text-sm text-gray-500">{{ __('Найдено: :count', ['count' => $items->total()]) }}</p>

            <x-ads-list :items="$items" :region="$region" removable />

            @if($items->hasPages())
                <div class="mt-6">
                    {{ $items->links('region.category.pagination') }}
                </div>
            @endif
        @else
            <div class="relative overflow-hidden rounded-3xl border border-green-first/15 bg-gradient-to-br from-green-first/[0.04] via-white to-green-second/[0.08] px-6 py-16 text-center shadow-sm">
                <span class="relative mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-green-first shadow-sm ring-1 ring-green-first/15">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-7 w-7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                    </svg>
                </span>
                <h2 class="relative mt-4 text-lg font-bold tracking-tight text-gray-900">{{ __('В избранном пока пусто.') }}</h2>
                <p class="relative mx-auto mt-2 max-w-md text-sm leading-6 text-gray-500">{{ __('Нажимайте на сердечко у товара или услуги, чтобы сохранить их здесь.') }}</p>
                <a href="{{ $region->url() }}" class="relative mt-6 inline-flex items-center gap-2 rounded-full bg-green-first px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-green-first/20 transition hover:bg-green-second">
                    {{ __('Вернуться в каталог') }}
                </a>
            </div>
        @endif
    </div>
@endsection
