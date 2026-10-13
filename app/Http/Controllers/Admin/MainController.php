<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MainController extends Controller
{
    public function index()
    {
        return Inertia::render('Index', [
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
