<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Region;
use Illuminate\Http\JsonResponse;

class CompanyInfoController extends Controller
{
    public function show(Region $region, Company $company, string $type): JsonResponse
    {
        abort_unless(in_array($type, ['schedule', 'payment', 'delivery'], true), 404);

        $company->load(['schedules', 'scheduleExceptions']);

        if ($type === 'schedule') {
            abort_if($company->schedules->isEmpty(), 404);

            return response()->json([
                'title' => __('График работы'),
                'html' => view('region.partials.company-info.schedule', ['company' => $company])->render(),
            ]);
        }

        $html = $type === 'payment' ? $company->payment : $company->delivery;

        abort_if(blank($html), 404);

        return response()->json([
            'title' => $type === 'payment' ? __('Оплата') : __('Доставка'),
            'html' => $html,
        ]);
    }
}
