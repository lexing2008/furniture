<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LanguageController extends Controller
{
    public function index()
    {
        // todo need to implement
        return Inertia::render('languages/IndexLanguages', [
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

    public function create()
    {
        // todo need to implement
        return Inertia::render('languages/CreateLanguage', [

        ]);
    }
}
