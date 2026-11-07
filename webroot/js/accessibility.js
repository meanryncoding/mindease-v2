/**
 * MindEase display and accessibility settings.
 * Settings are stored in this browser (localStorage) and applied as a font size,
 * a theme attribute and CSS classes on the <html> element.
 */
(function () {
    var STORAGE_KEY = 'mindease-a11y';
    var SIZES = [87.5, 100, 112.5, 125, 150];
    var THEMES = ['system', 'light', 'dark'];
    var DEFAULTS = { size: 100, contrast: false, motion: false, spacing: false, font: false, theme: 'system' };
    var TOGGLES = {
        a11yContrast: 'contrast',
        a11yMotion: 'motion',
        a11ySpacing: 'spacing',
        a11yFont: 'font'
    };
    var darkQuery = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

    function load() {
        try {
            var saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
            return Object.assign({}, DEFAULTS, saved);
        } catch (e) {
            return Object.assign({}, DEFAULTS);
        }
    }

    function save(state) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
        } catch (e) {
            // Storage can be blocked (private mode); settings then last for this page only.
        }
    }

    // High contrast always uses the light palette (black on white).
    function resolveTheme(state) {
        if (state.contrast) {
            return 'light';
        }
        if (state.theme === 'dark' || state.theme === 'light') {
            return state.theme;
        }
        return darkQuery && darkQuery.matches ? 'dark' : 'light';
    }

    function apply(state) {
        var root = document.documentElement;
        root.style.fontSize = state.size + '%';
        root.setAttribute('data-bs-theme', resolveTheme(state));
        root.classList.toggle('a11y-contrast', !!state.contrast);
        root.classList.toggle('a11y-reduce-motion', !!state.motion);
        root.classList.toggle('a11y-spacing', !!state.spacing);
        root.classList.toggle('a11y-readable-font', !!state.font);
    }

    var state = load();
    if (SIZES.indexOf(state.size) === -1) {
        state.size = 100;
    }
    if (THEMES.indexOf(state.theme) === -1) {
        state.theme = 'system';
    }
    // Apply immediately (script runs in <head>) so the page does not flash before settings load.
    apply(state);

    function syncControls() {
        var label = document.getElementById('a11ySizeLabel');
        if (label) {
            label.textContent = state.size + '%';
        }

        Object.keys(TOGGLES).forEach(function (id) {
            var input = document.getElementById(id);
            if (input) {
                input.checked = !!state[TOGGLES[id]];
            }
        });

        Array.prototype.forEach.call(document.querySelectorAll('input[name="a11yTheme"]'), function (input) {
            input.checked = input.value === state.theme;
        });

        var isDark = resolveTheme(state) === 'dark';
        Array.prototype.forEach.call(document.querySelectorAll('[data-theme-toggle]'), function (button) {
            button.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        });
    }

    function update() {
        apply(state);
        save(state);
        syncControls();
    }

    // Follow the device theme while the setting is "system".
    if (darkQuery) {
        var onSystemChange = function () {
            if (state.theme === 'system') {
                apply(state);
                syncControls();
            }
        };
        if (darkQuery.addEventListener) {
            darkQuery.addEventListener('change', onSystemChange);
        } else if (darkQuery.addListener) {
            darkQuery.addListener(onSystemChange);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        Array.prototype.forEach.call(document.querySelectorAll('[data-a11y-size]'), function (button) {
            button.addEventListener('click', function () {
                var index = SIZES.indexOf(state.size);
                index += button.getAttribute('data-a11y-size') === 'up' ? 1 : -1;
                index = Math.max(0, Math.min(SIZES.length - 1, index));
                state.size = SIZES[index];
                update();
            });
        });

        Object.keys(TOGGLES).forEach(function (id) {
            var input = document.getElementById(id);
            if (!input) {
                return;
            }
            input.addEventListener('change', function () {
                state[TOGGLES[id]] = input.checked;
                update();
            });
        });

        Array.prototype.forEach.call(document.querySelectorAll('input[name="a11yTheme"]'), function (input) {
            input.addEventListener('change', function () {
                if (input.checked) {
                    state.theme = input.value;
                    update();
                }
            });
        });

        Array.prototype.forEach.call(document.querySelectorAll('[data-theme-toggle]'), function (button) {
            button.addEventListener('click', function () {
                state.theme = resolveTheme(state) === 'dark' ? 'light' : 'dark';
                update();
            });
        });

        var reset = document.getElementById('a11yReset');
        if (reset) {
            reset.addEventListener('click', function () {
                state = Object.assign({}, DEFAULTS);
                update();
            });
        }

        syncControls();
    });
})();