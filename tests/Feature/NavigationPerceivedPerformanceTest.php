<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class NavigationPerceivedPerformanceTest extends TestCase
{
    public function test_admin_navigation_component_uses_hover_prefetch(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-nav-link-admin href="/demo" :active="false" title="Demo">
                <x-slot name="icon">
                    <span>i</span>
                </x-slot>
                Demo
            </x-nav-link-admin>
        BLADE);

        $this->assertStringContainsString('wire:navigate.hover', $html);
    }

    public function test_bottom_navigation_uses_hover_prefetch(): void
    {
        $html = view('partials.bottom-nav')->render();

        $this->assertSame(3, substr_count($html, 'wire:navigate.hover'));
    }
}
