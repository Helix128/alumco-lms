@php
    $accessibilityPreferences = $accessibilityPreferences
        ?? \App\Support\AccessibilityPreferences::normalize(auth()->user()?->accessibility_preferences);
@endphp

<script>
    (function () {
        var levels = [14, 16, 18, 20];

        function normalize(preferences) {
            preferences = preferences || {};
            var fontLevel = parseInt(preferences.fontLevel == null ? 1 : preferences.fontLevel, 10);

            if (!Number.isInteger(fontLevel) || fontLevel < 0 || fontLevel > 3) {
                fontLevel = 1;
            }

            return {
                fontLevel: fontLevel,
                highContrast: Boolean(preferences.highContrast),
                reducedMotion: Boolean(preferences.reducedMotion)
            };
        }

        window.AlumcoAccessibility = {
            apply: function (preferences) {
                preferences = normalize(preferences);

                document.documentElement.style.setProperty('--font-base', levels[preferences.fontLevel] + 'px');
                document.documentElement.dataset.contrast = preferences.highContrast ? 'high' : 'default';
                document.documentElement.dataset.motion = preferences.reducedMotion ? 'reduced' : 'default';
                delete document.documentElement.dataset.background;
                delete document.documentElement.dataset.cards;
            }
        };

        window.AlumcoAccessibility.apply(@js($accessibilityPreferences));

        window.addEventListener('accessibility-preferences-updated', function (event) {
            var detail = event.detail || {};
            var preferences = detail.preferences || (Array.isArray(detail) && detail[0] ? detail[0].preferences : null) || detail;

            window.AlumcoAccessibility.apply(preferences);
        });
    })();
</script>
