@php
    $accessibilityPreferences = $accessibilityPreferences
        ?? \App\Support\AccessibilityPreferences::normalize(auth()->user()?->accessibility_preferences);
@endphp

<script>
    (function () {
        var levels = [18, 20, 22];

        function normalize(preferences) {
            preferences = preferences || {};
            var fontLevel = parseInt(preferences.fontLevel == null ? 0 : preferences.fontLevel, 10);

            if (!Number.isInteger(fontLevel) || fontLevel < 0 || fontLevel > 2) {
                fontLevel = 0;
            }

            return {
                fontLevel: fontLevel,
                highContrast: Boolean(preferences.highContrast),
                reducedMotion: Boolean(preferences.reducedMotion)
            };
        }

        window.AlumcoAccessibility = {
            apply: function (preferences, persist = true) {
                preferences = normalize(preferences);

                document.documentElement.style.setProperty('--font-base', levels[preferences.fontLevel] + 'px');
                document.documentElement.dataset.font = String(preferences.fontLevel);
                document.documentElement.dataset.contrast = preferences.highContrast ? 'high' : 'default';
                document.documentElement.dataset.motion = preferences.reducedMotion ? 'reduced' : 'default';
                delete document.documentElement.dataset.background;
                delete document.documentElement.dataset.cards;

                if (persist) {
                    try {
                        localStorage.setItem('alumco-accessibility', JSON.stringify(preferences));
                    } catch (e) {
                        console.warn('No se pudo guardar la configuración de accesibilidad en localStorage');
                    }
                }

                // Dispatch a native event for non-Livewire scripts
                window.dispatchEvent(new CustomEvent('alumco-motion-changed', { 
                    detail: { reducedMotion: preferences.reducedMotion } 
                }));
            },
            isReducedMotion: function () {
                return document.documentElement.dataset.motion === 'reduced';
            },
            _cooldowns: {
                font: 0,
                highContrast: 0,
                reducedMotion: 0,
            },
            beginCooldown: function (control, milliseconds) {
                var now = Date.now();
                var current = this._cooldowns[control] || 0;

                if (now < current) {
                    return false;
                }

                this._cooldowns[control] = now + milliseconds;

                return true;
            },
            current: function () {
                return normalize({
                    fontLevel: document.documentElement.dataset.font,
                    highContrast: document.documentElement.dataset.contrast === 'high',
                    reducedMotion: document.documentElement.dataset.motion === 'reduced'
                });
            },
            fromEvent: function (event) {
                var detail = event.detail || {};

                return normalize(detail.preferences || (Array.isArray(detail) && detail[0] ? detail[0].preferences : null) || detail);
            }
        };

        // Prioridad 1: LocalStorage (para inmediatez en navegaciones)
        // Prioridad 2: Valores del servidor (fallback o primer acceso)
        var serverPrefs = @js($accessibilityPreferences);
        try {
            var localPrefs = JSON.parse(localStorage.getItem('alumco-accessibility'));
            if (localPrefs) {
                window.AlumcoAccessibility.apply(localPrefs, false);
            } else {
                window.AlumcoAccessibility.apply(serverPrefs);
            }
        } catch (e) {
            window.AlumcoAccessibility.apply(serverPrefs);
        }

        window.addEventListener('accessibility-preferences-updated', function (event) {
            var detail = event.detail || {};
            var preferences = detail.preferences || (Array.isArray(detail) && detail[0] ? detail[0].preferences : null) || detail;

            window.AlumcoAccessibility.apply(preferences);
        });

        // Asegurar que en cada navegación de Livewire se re-apliquen las preferencias
        // Esto soluciona el problema de que Livewire no actualiza los atributos de <html>
        document.addEventListener('livewire:navigated', function () {
            var prefs = window.AlumcoAccessibility.current();
            window.AlumcoAccessibility.apply(prefs, false);
        });
    })();
</script>
