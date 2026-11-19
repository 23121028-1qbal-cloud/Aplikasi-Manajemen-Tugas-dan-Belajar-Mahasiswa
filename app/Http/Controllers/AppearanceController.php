<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AppearanceController extends Controller
{
    public function edit(Request $request): View
    {
        $this->authorize('view', $request->user());

        return view('pages.account.appearance', [
            'theme' => $request->cookie('theme') === 'dark' ? 'dark' : 'light',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('view', $request->user());

        $validated = $request->validate([
            'theme' => ['required', Rule::in(['light', 'dark'])],
        ]);

        return redirect()
            ->route('account.appearance.edit')
            ->withCookie(cookie('theme', $validated['theme'], 60 * 24 * 365, '/', null, false, false, false, 'lax'))
            ->with('status', 'Preferensi tampilan disimpan.');
    }
}
