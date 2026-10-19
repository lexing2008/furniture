@extends('layouts.site')

@section('title', $seo['seo_title'])
@section('description', $seo['seo_description'])


@section('content')
    <h1>{{ $seo['seo_title'] ?? $region->name }}</h1>

    @if (filled($seo['seo_block']))
        <div class="seo-block">{!! nl2br(e($seo['seo_block'])) !!}</div>
    @endif
@endsection
