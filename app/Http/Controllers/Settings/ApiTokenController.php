<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiTokenController extends Controller
{
    /**
     * Genereeri kasutajale uus API võti.
     * Plaintext token saadetakse tagasi ühe korra flash-sõnumina.
     */
    public function generate(Request $request): RedirectResponse
    {
        $token = Str::random(64);

        $request->user()->update([
            'api_token' => $token,
        ]);

        return redirect()->route('music.index')
            ->with('newToken', $token);
    }

    /**
     * Tühista (kustuta) kasutaja API võti.
     */
    public function revoke(Request $request): RedirectResponse
    {
        $request->user()->update([
            'api_token' => null,
        ]);

        return redirect()->route('music.index');
    }
}