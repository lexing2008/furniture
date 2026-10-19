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
            <div>
                <a href="{{ route('region.show', ['region' =>  $area['region']['subdomain']]) }}" class="font-bold">
                    {{$area['region']['name']}}
                </a>
                <div>
                    @foreach($area['cities'] as $city)
                        <div>
                            <a href="{{ route('region.show', ['region' =>  $city['subdomain']]) }}" class="link text-xs">
                                Мебель в {{$city['name_in']}}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>


    @if (filled($seo['seo_block']))
        <div class="seo-block">{!! nl2br(e($seo['seo_block'])) !!}</div>
    @endif
@endsection
