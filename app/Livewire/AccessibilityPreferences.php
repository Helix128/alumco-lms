<?php

namespace App\Livewire;

use App\Models\User;
use App\Support\AccessibilityPreferences as AccessibilityPreferencesSupport;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class AccessibilityPreferences extends Component
{
    public string $title = 'Preferencias de accesibilidad';

    public ?string $description = null;

    public int $fontLevel = 0;

    public bool $highContrast = false;

    public bool $reducedMotion = false;

    public function mount(?string $title = null, ?string $description = null): void
    {
        $this->title = $title ?? $this->title;
        $this->description = $description;

        $preferences = AccessibilityPreferencesSupport::normalize(
            Auth::user()?->accessibility_preferences
        );

        $this->fontLevel = $preferences['fontLevel'];
        $this->highContrast = $preferences['highContrast'];
        $this->reducedMotion = $preferences['reducedMotion'];
    }

    public function setFontLevel(int $level): void
    {
        $this->fontLevel = max(0, min(2, $level));
        $this->save();
    }

    public function increaseFont(): void
    {
        $this->fontLevel = min(2, $this->fontLevel + 1);
        $this->save();
    }

    public function decreaseFont(): void
    {
        $this->fontLevel = max(0, $this->fontLevel - 1);
        $this->save();
    }

    public function save(): void
    {
        $this->validate([
            'fontLevel' => ['required', 'integer', 'min:0', 'max:2'],
            'highContrast' => ['boolean'],
            'reducedMotion' => ['boolean'],
        ]);

        $preferences = $this->preferences();
        $user = Auth::user();

        abort_unless($user instanceof User, 403);

        $user->forceFill([
            'accessibility_preferences' => $preferences,
        ])->save();

        $this->dispatch('accessibility-preferences-updated', preferences: $preferences);
    }

    /**
     * @param  array<string, mixed>  $preferences
     */
    #[On('accessibility-preferences-updated')]
    public function applyPreferences(array $preferences): void
    {
        $preferences = AccessibilityPreferencesSupport::normalize($preferences);

        $this->fontLevel = $preferences['fontLevel'];
        $this->highContrast = $preferences['highContrast'];
        $this->reducedMotion = $preferences['reducedMotion'];
    }

    public function fontLabel(): string
    {
        return AccessibilityPreferencesSupport::fontSizeFor($this->fontLevel).' px';
    }

    /**
     * @return array{fontLevel: int, highContrast: bool, reducedMotion: bool}
     */
    private function preferences(): array
    {
        return AccessibilityPreferencesSupport::normalize([
            'fontLevel' => $this->fontLevel,
            'highContrast' => $this->highContrast,
            'reducedMotion' => $this->reducedMotion,
        ]);
    }

    public function render(): View
    {
        return view('livewire.accessibility-preferences');
    }
}
