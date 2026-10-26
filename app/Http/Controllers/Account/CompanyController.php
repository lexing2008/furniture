<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The main page of the account. It is empty for now: the company is edited in the sections.
 */
class CompanyController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Index');
    }
}
