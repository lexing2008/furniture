<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;

/**
 * The interface language of the account.
 */
class LanguageController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'lang' => ['required', 'string', Rule::in(config('app.supported_locales', ['ru', 'en']))],
        ], [
            'lang.required' => __('Выберите язык интерфейса.'),
            'lang.in' => __('Выберите поддерживаемый язык.'),
        ]);

        $request->user()->update(['lang' => $data['lang']]);

        // The flash message and the next page must already use the new language.
        App::setLocale($data['lang']);

        return back()->with('success', __('Язык интерфейса изменён.'));
    }
}
