<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MainController extends Controller
{
    public function index()
    {
        return Inertia::render('Welcome', [
            'laravelVersion' => app()->version(),
            'phpVersion' => phpversion(),
            'data' => [
                1,
                2,
                3,
                4,
            ],
        ]);
    }
}
