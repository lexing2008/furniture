@extends('layouts.site')

@php
    $region = request()->route('region')
@endphp

@section('title', $seo['seo_title'])
@section('description', $seo['seo_description'])

@section('content')
    <h1>{{ $region->name }}</h1>


    <div class="flex flex-wrap gap-y-5 gap-x-10 justify-left">
        @foreach($areas as $area)
            @if($area['region']['id'] == $region->id)
            <div>
{{--                <a href="{{ route('region.show', ['region' =>  $area['region']['subdomain']]) }}" class="font-bold">--}}
{{--                    {{$area['region']['name']}}--}}
{{--                </a>--}}
                <div class="flex flex-wrap gap-3 mt-2">
                    @foreach($area['cities'] as $city)

                        <a href="{{ route('region.show', ['region' =>  $city['subdomain']]) }}" class="link text-xs">
                            Мебель в {{$city['name_in']}}
                        </a>

                    @endforeach
                </div>
            </div>
            @endif
        @endforeach
    </div>


    @if (filled($seo['seo_block']))
        <div class="seo-block">{!! nl2br(e($seo['seo_block'])) !!}</div>
    @endif
@endsection
