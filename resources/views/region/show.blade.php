@extends('layouts.site')

@section('title', $seo['seo_title'])
@section('description', $seo['seo_description'])


@section('content')

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-3 font-bold">
        <div class="h-60 border-2 border-green-first/10 rounded-2xl flex justify-center items-center">
            <img src="https://web.archive.org/web/20170325003756im_/http://mebelgid.by/images/mebel/0/w325/a9648_0.jpg" alt="">
        </div>
        <div class="h-60 border-2 border-green-first/10 rounded-2xl flex justify-center items-center">
            <img src="https://web.archive.org/web/20161105125326im_/http://mebelgid.by/images/mebel/0/w325/a9616_0.jpg" alt="">
        </div>
        <div class="h-60 border-2 border-green-first/10 rounded-2xl flex justify-center items-center">
            <img src="https://web.archive.org/web/20161105125326im_/http://mebelgid.by/images/mebel/0/w325/a7550_0.jpg" alt="">
        </div>
        <div class="h-60 border-2 border-green-first/10 rounded-2xl flex justify-center items-center">
            <img src="https://web.archive.org/web/20161105125326im_/http://mebelgid.by/images/mebel/0/w325/a11253_0.jpg" alt=""/>
        </div>
        <div class="h-60 border-2 border-green-first/10 rounded-2xl flex justify-center items-center">
            <img src="https://web.archive.org/web/20161105125326im_/http://mebelgid.by/images/mebel/0/w325/a11621_0.png" alt=""/>
        </div>


        <div class="h-60 border-2 border-green-first/10 rounded-2xl flex justify-center items-center relative">
            <img src="https://web.archive.org/web/20170325003756im_/http://mebelgid.by/images/mebel/0/w325/a9648_0.jpg" alt="">

            <div class="absolute right-0 bottom-5 bg-green-first text-white p-3 px-4 rounded-l-2xl text-sm">Стулья</div>
        </div>
        <div class="h-60 border-2 border-green-first/10 rounded-2xl flex justify-center items-center relative">
            <img src="https://web.archive.org/web/20161105125326im_/http://mebelgid.by/images/mebel/0/w325/a9616_0.jpg" alt="">

            <div class="absolute right-0 bottom-5 bg-green-first text-white p-3 px-4 rounded-l-2xl text-sm">Кресла и стулья</div>
        </div>
        <div class="h-60 border-2 border-green-first/10 rounded-2xl flex justify-center items-center relative">
            <img src="https://web.archive.org/web/20161105125326im_/http://mebelgid.by/images/mebel/0/w325/a7550_0.jpg" alt="">
            <div class="absolute right-0 bottom-5 bg-green-first/80 text-white p-3 px-4 rounded-l-2xl text-sm font-bold">Корпусная мебель</div>
        </div>
        <div class="h-60 border-2 border-green-first/10 rounded-2xl flex justify-center items-center relative">
            <img src="https://web.archive.org/web/20161105125326im_/http://mebelgid.by/images/mebel/0/w325/a11253_0.jpg" alt=""/>
            <div class="absolute right-0 bottom-5 bg-green-first/80 text-white p-3 px-4 rounded-l-2xl text-sm font-bold">Мягкая мебель</div>
        </div>
        <div class="h-60 border-2 border-green-first/10 rounded-2xl flex justify-center items-center">
            <img src="https://web.archive.org/web/20161105125326im_/http://mebelgid.by/images/mebel/0/w325/a11621_0.png" alt=""/>
        </div>


        <div class="h-60 border-2 border-green-first/10 rounded-2xl flex justify-center items-center relative hover:opacity-80 transition-opacity duration-200 cursor-pointer">
            <img src="https://web.archive.org/web/20170325003756im_/http://mebelgid.by/images/mebel/0/w325/a9648_0.jpg" alt="">

            <div class="absolute right-0 bottom-5 bg-green-first text-white p-3 px-4 rounded-l-2xl text-sm">Стулья</div>
        </div>
        <div class="h-60 border-2 border-green-first/10 rounded-2xl flex justify-center items-center relative hover:opacity-80 transition-opacity duration-200 cursor-pointer">
            <img src="https://web.archive.org/web/20161105125326im_/http://mebelgid.by/images/mebel/0/w325/a9616_0.jpg" alt="">

            <div class="absolute right-0 bottom-5 bg-green-first text-white p-3 px-4 rounded-l-2xl text-sm">Кресла и стулья</div>
        </div>
        <div class="h-60 border-2 border-green-first/10 rounded-2xl flex justify-center items-center relative hover:opacity-80 transition-opacity duration-200 cursor-pointer">
            <img src="https://web.archive.org/web/20161105125326im_/http://mebelgid.by/images/mebel/0/w325/a7550_0.jpg" alt="">
            <div class="absolute right-0 bottom-5 bg-green-first/80 text-white p-3 px-4 rounded-l-2xl text-sm font-bold">Корпусная мебель</div>
        </div>
        <div class="h-60 border-2 border-green-first/10 rounded-2xl flex justify-center items-center relative hover:opacity-80 transition-opacity duration-200 cursor-pointer">
            <img src="https://web.archive.org/web/20161105125326im_/http://mebelgid.by/images/mebel/0/w325/a11253_0.jpg" alt=""/>
            <div class="absolute right-0 bottom-5 bg-green-first text-white p-3 px-4 rounded-l-2xl text-sm font-bold">Мягкая мебель</div>
        </div>
        <div class="h-60 border-2 border-green-first/10 rounded-2xl flex justify-center items-center relative hover:opacity-80 transition-opacity duration-200 cursor-pointer">
            <img src="https://web.archive.org/web/20161105125326im_/http://mebelgid.by/images/mebel/0/w325/a11621_0.png" alt=""/>
        </div>


{{--        <div class="h-60 border border-green-first/10 rounded-2xl flex justify-center items-center"></div>--}}
{{--        <div class="h-60 border border-green-first/10 rounded-2xl flex justify-center items-center"></div>--}}
{{--        <div class="h-60 border border-green-first/10 rounded-2xl flex justify-center items-center"></div>--}}
{{--        <div class="h-60 border border-green-first/10 rounded-2xl flex justify-center items-center"></div>--}}
{{--        <div class="h-60 border border-green-first/10 rounded-2xl flex justify-center items-center"></div>--}}
    </div>

    <div class="mt-10 h-10 border-2 border-green-first/10 rounded-2xl flex justify-center items-center">
        <a href="/catalog" class="text-green-first ">Весь каталог мебели</a>
    </div>

{{--    <h1 class="mt-10">{{ $seo['seo_title'] ?? $region->name }}</h1>--}}

{{--    @if (filled($seo['seo_block']))--}}
{{--        <div class="seo-block">{!! nl2br(e($seo['seo_block'])) !!}</div>--}}
{{--    @endif--}}
@endsection
