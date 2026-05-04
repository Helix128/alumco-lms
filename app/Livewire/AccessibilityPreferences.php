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
    private const INTERACTION_COOLDOWN_MS = 300;

    public string $title = 'Preferencias de accesibilidad';

    public ?string $description = null;

    public int $fontLevel = 0;

    public bool $highContrast = false;

    public bool $reducedMotion = false;

    /** @var array{font: int, highContrast: int, reducedMotion: int} */
    public array $cooldownUntilMs = [
        'font' => 0,
        'highContrast' => 0,
        'reducedMotion' => 0,
    ];

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
        if ($this->isCoolingDown('font')) {
            $this->dispatchConfirmedPreferences();

            return;
        }

        $this->startCooldown('font');
        $this->fontLevel = max(0, min(2, $level));
        $this->save();
    }

    public function increaseFont(): void
    {
        if ($this->isCoolingDown('font')) {
            $this->dispatchConfirmedPreferences();

            return;
        }

        $this->startCooldown('font');
        $this->fontLevel = min(2, $this->fontLevel + 1);
        $this->save();
    }

    public function decreaseFont(): void
    {
        if ($this->isCoolingDown('font')) {
            $this->dispatchConfirmedPreferences();

            return;
        }

        $this->startCooldown('font');
        $this->fontLevel = max(0, $this->fontLevel - 1);
        $this->save();
    }

    public function toggleHighContrast(): void
    {
        if ($this->isCoolingDown('highContrast')) {
            $this->dispatchConfirmedPreferences();

            return;
        }

        $this->startCooldown('highContrast');
        $this->highContrast = ! $this->highContrast;
        $this->save();
    }

    public function toggleReducedMotion(): void
    {
        if ($this->isCoolingDown('reducedMotion')) {
            $this->dispatchConfirmedPreferences();

            return;
        }

        $this->startCooldown('reducedMotion');
        $this->reducedMotion = ! $this->reducedMotion;
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
        $this->persistPreferences((int) $user->id, $preferences);
        $confirmedPreferences = AccessibilityPreferencesSupport::normalize(
            $user->refresh()->accessibility_preferences
        );

        $this->fontLevel = $confirmedPreferences['fontLevel'];
        $this->highContrast = $confirmedPreferences['highContrast'];
        $this->reducedMotion = $confirmedPreferences['reducedMotion'];

        $this->dispatch('accessibility-preferences-updated', preferences: $confirmedPreferences);
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

    private function persistPreferences(int $userId, array $preferences): bool
    {
        return User::query()
            ->whereKey($userId)
            ->update(['accessibility_preferences' => $preferences]) > 0;
    }

    private function isCoolingDown(string $control): bool
    {
        return $this->currentTimeMs() < ($this->cooldownUntilMs[$control] ?? 0);
    }

    private function startCooldown(string $control): void
    {
        $this->cooldownUntilMs[$control] = $this->currentTimeMs() + self::INTERACTION_COOLDOWN_MS;
    }

    private function dispatchConfirmedPreferences(): void
    {
        $preferences = AccessibilityPreferencesSupport::normalize(
            Auth::user()?->accessibility_preferences
        );

        $this->fontLevel = $preferences['fontLevel'];
        $this->highContrast = $preferences['highContrast'];
        $this->reducedMotion = $preferences['reducedMotion'];

        $this->dispatch('accessibility-preferences-updated', preferences: $preferences);
    }

    private function currentTimeMs(): int
    {
        return (int) floor(microtime(true) * 1000);
    }

    public function render(): View
    {
        return view('livewire.accessibility-preferences');
    }
}
