<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TrustedProxySchemeTest extends TestCase
{
    public function test_forwarded_https_scheme_is_trusted_for_generated_urls(): void
    {
        Route::get('/proxy-scheme-check', function (Request $request) {
            return [
                'secure' => $request->isSecure(),
                'livewire_update_url' => url('/livewire/update'),
            ];
        });

        $this->withServerVariables([
            'REMOTE_ADDR' => '10.0.0.10',
            'HTTP_HOST' => 'noseprogramar.cl',
            'HTTP_X_FORWARDED_HOST' => 'noseprogramar.cl',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_PORT' => '443',
        ])
            ->get('/proxy-scheme-check')
            ->assertOk()
            ->assertJson([
                'secure' => true,
                'livewire_update_url' => 'https://noseprogramar.cl/livewire/update',
            ]);
    }
}
