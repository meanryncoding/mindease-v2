/**
 * MindEase accessibility settings.
 * Settings are stored in this browser (localStorage) and applied as a font size
 * and CSS classes on the <html> element.
 */
(function () {
    var STORAGE_KEY = 'mindease-a11y';
    var SIZES = [87.5, 100, 112.5, 125, 150];
    var DEFAULTS = { size: 100, contrast: false, motion: false, spacing: false, font: false };
    var TOGGLES = {
        a11yContrast: 'contrast',
        a11yMotion: 'motion',
        a11ySpacing: 'spacing',
        a11yFont: 'font'
    };

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

    function apply(state) {
        var root = document.documentElement;
        root.style.fontSize = state.size + '%';
        root.classList.toggle('a11y-contrast', !!state.contrast);
        root.classList.toggle('a11y-reduce-motion', !!state.motion);
        root.classList.toggle('a11y-spacing', !!state.spacing);
        root.classList.toggle('a11y-readable-font', !!state.font);
    }

    var state = load();
    if (SIZES.indexOf(state.size) === -1) {
        state.size = 100;
    }
    // Apply immediately (script runs in <head>) so the page does not flash before settings load.
    apply(state);

    document.addEventListener('DOMContentLoaded', function () {
        var label = document.getElementById('a11ySizeLabel');

        function sync() {
            if (label) {
                label.textContent = state.size + '%';
            }
            Object.keys(TOGGLES).forEach(function (id) {
                var input = document.getElementById(id);
                if (input) {
                    input.checked = !!state[TOGGLES[id]];
                }
            });
        }

        function update() {
            apply(state);
            save(state);
            sync();
        }

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

        var reset = document.getElementById('a11yReset');
        if (reset) {
            reset.addEventListener('click', function () {
                state = Object.assign({}, DEFAULTS);
                update();
            });
        }

        sync();
    });
})();