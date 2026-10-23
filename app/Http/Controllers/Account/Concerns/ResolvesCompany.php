<?php

namespace App\Http\Controllers\Account\Concerns;

use App\Models\Company;
use Illuminate\Http\Request;

/**
 * The company of the logged in user.
 */
trait ResolvesCompany
{
    /**
     * @param  array<int, string>  $relations
     */
    protected function company(Request $request, array $relations = []): Company
    {
        return $request->user()->company()->with($relations)->firstOrFail();
    }
}
