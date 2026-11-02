<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserLocaleController extends Controller
{
    /**
     * Update the authenticated user's preferred locale.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(User::LOCALES)],
        ]);

        $request->user()->update($validated);

        return back();
    }
}
