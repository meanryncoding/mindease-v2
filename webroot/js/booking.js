/**
 * MindEase booking page.
 * Builds the calendar and time slots from the JSON in #bookingData and keeps the
 * summary panel, step indicator and submit button in sync.
 */
(function () {
    'use strict';

    var dataElement = document.getElementById('bookingData');
    if (!dataElement) {
        return;
    }

    var config = JSON.parse(dataElement.textContent);
    var slots = config.slots || {};
    var text = config.i18n || {};
    var locale = document.documentElement.lang === 'ms' ? 'ms-MY' : 'en-GB';

    var radios = Array.prototype.slice.call(document.querySelectorAll('input[name="counselor_id"]'));
    var startInput = document.getElementById('requestedStart');
    var calGrid = document.getElementById('calGrid');
    var calTitle = document.getElementById('calTitle');
    var calPrev = document.getElementById('calPrev');
    var calNext = document.getElementById('calNext');
    var slotGrid = document.getElementById('slotGrid');
    var slotHeading = document.getElementById('slotHeading');
    var sumCounselor = document.getElementById('sumCounselor');
    var sumWhen = document.getElementById('sumWhen');
    var summaryStatus = document.getElementById('summaryStatus');
    var modeRadios = Array.prototype.slice.call(document.querySelectorAll('input[name="mode"]'));
    var sumWhere = document.getElementById('sumWhere');
    var modeChip = document.getElementById('modeChip');
    var noteOnline = document.getElementById('noteOnline');
    var noteInPerson = document.getElementById('noteInPerson');
    var consent = document.getElementById('consent');
    var confirmButton = document.getElementById('confirmBtn');
    var steps = Array.prototype.slice.call(document.querySelectorAll('[data-step]'));

    var monthFormat = new Intl.DateTimeFormat(locale, { month: 'long', year: 'numeric' });
    var longDateFormat = new Intl.DateTimeFormat(locale, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    var dayHeadFormat = new Intl.DateTimeFormat(locale, { weekday: 'narrow' });

    var state = { counselorId: null, month: null, date: null, slot: null };

    function pad(number) {
        return number < 10 ? '0' + number : String(number);
    }

    function dateKey(year, monthIndex, day) {
        return year + '-' + pad(monthIndex + 1) + '-' + pad(day);
    }

    function parseKey(key) {
        var parts = key.split('-');
        return new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
    }

    function daysFor(counselorId) {
        return slots[counselorId] || {};
    }

    function sortedKeys(counselorId) {
        return Object.keys(daysFor(counselorId)).sort();
    }

    function firstOfMonth(date) {
        return new Date(date.getFullYear(), date.getMonth(), 1);
    }

    function monthIndexOf(date) {
        return date.getFullYear() * 12 + date.getMonth();
    }

    function clear(element) {
        while (element.firstChild) {
            element.removeChild(element.firstChild);
        }
    }

    function selectedRadio() {
        return radios.filter(function (radio) { return radio.checked; })[0] || null;
    }

    function renderCalendar() {
        clear(calGrid);
        var month = state.month;
        calTitle.textContent = monthFormat.format(month);

        // Weekday headers, Monday first (2024-01-01 was a Monday).
        for (var i = 0; i < 7; i++) {
            var head = document.createElement('div');
            head.className = 'cal-head';
            head.textContent = dayHeadFormat.format(new Date(2024, 0, 1 + i));
            calGrid.appendChild(head);
        }

        var offset = (month.getDay() + 6) % 7;
        for (var blank = 0; blank < offset; blank++) {
            calGrid.appendChild(document.createElement('div'));
        }

        var days = daysFor(state.counselorId);
        var count = new Date(month.getFullYear(), month.getMonth() + 1, 0).getDate();
        for (var day = 1; day <= count; day++) {
            var key = dateKey(month.getFullYear(), month.getMonth(), day);
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'cal-day';
            button.textContent = String(day);
            button.setAttribute('aria-label', longDateFormat.format(parseKey(key)));
            if (days[key]) {
                button.classList.add('is-available');
                button.dataset.key = key;
                if (state.date === key) {
                    button.classList.add('is-selected');
                    button.setAttribute('aria-pressed', 'true');
                }
            } else {
                button.disabled = true;
            }
            calGrid.appendChild(button);
        }

        var keys = sortedKeys(state.counselorId);
        var firstMonth = keys.length ? monthIndexOf(firstOfMonth(parseKey(keys[0]))) : monthIndexOf(month);
        var lastMonth = keys.length ? monthIndexOf(firstOfMonth(parseKey(keys[keys.length - 1]))) : monthIndexOf(month);
        calPrev.disabled = monthIndexOf(month) <= firstMonth;
        calNext.disabled = monthIndexOf(month) >= lastMonth;
    }

    function renderSlots() {
        clear(slotGrid);
        var days = daysFor(state.counselorId);

        if (!sortedKeys(state.counselorId).length) {
            slotHeading.textContent = text.noSlots || '';
            return;
        }
        if (!state.date) {
            slotHeading.textContent = text.pickDate || '';
            return;
        }

        var list = days[state.date] || [];
        slotHeading.textContent = (text.timesOn || '{0}').replace('{0}', longDateFormat.format(parseKey(state.date)));
        if (!list.length) {
            var empty = document.createElement('p');
            empty.className = 'small text-muted';
            empty.textContent = text.noTimes || '';
            slotGrid.appendChild(empty);
            return;
        }

        list.forEach(function (slot) {
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'slot-btn';
            button.textContent = slot.label;
            button.dataset.value = slot.value;
            button.setAttribute('aria-pressed', state.slot && state.slot.value === slot.value ? 'true' : 'false');
            slotGrid.appendChild(button);
        });
    }

    function renderSummary() {
        var radio = selectedRadio();
        sumCounselor.textContent = radio ? radio.getAttribute('data-name') : '-';

        if (state.slot) {
            sumWhen.textContent = longDateFormat.format(parseKey(state.date)) + ' · ' +
                state.slot.label + ' - ' + state.slot.end + ' MYT';
            summaryStatus.textContent = text.ready || '';
        } else {
            sumWhen.textContent = '-';
            summaryStatus.textContent = text.needTime || '';
        }
        startInput.value = state.slot ? state.slot.value : '';

        // Step indicator: 1 counselor chosen, 2 time, 3 confirm.
        var active = state.slot ? 3 : 2;
        steps.forEach(function (step) {
            var number = Number(step.getAttribute('data-step'));
            step.classList.toggle('is-active', number === active);
            step.classList.toggle('is-done', number < active);
            if (number === active) {
                step.setAttribute('aria-current', 'step');
            } else {
                step.removeAttribute('aria-current');
            }
        });

        radios.forEach(function (item) {
            item.parentNode.classList.toggle('is-selected', item.checked);
        });

        confirmButton.disabled = !(state.counselorId && state.slot && consent.checked);
    }

    function applyMode() {
        var checked = modeRadios.filter(function (radio) { return radio.checked; })[0];
        var inPerson = !!checked && checked.value === 'in_person';
        sumWhere.textContent = inPerson ? text.whereInPerson : text.whereOnline;
        modeChip.textContent = inPerson ? text.chipInPerson : text.chipOnline;
        noteOnline.hidden = inPerson;
        noteInPerson.hidden = !inPerson;
    }

    function renderAll() {
        applyMode();
        renderCalendar();
        renderSlots();
        renderSummary();
    }

    function chooseCounselor(id, keepSelection) {
        state.counselorId = id;
        if (!keepSelection) {
            state.date = null;
            state.slot = null;
        }
        var keys = sortedKeys(id);
        var anchor = state.date ? parseKey(state.date) : (keys.length ? parseKey(keys[0]) : new Date());
        state.month = firstOfMonth(anchor);
        renderAll();
    }

    calGrid.addEventListener('click', function (event) {
        var button = event.target.closest('.cal-day.is-available');
        if (!button) {
            return;
        }
        state.date = button.dataset.key;
        state.slot = null;
        renderAll();
    });

    slotGrid.addEventListener('click', function (event) {
        var button = event.target.closest('.slot-btn');
        if (!button) {
            return;
        }
        var list = daysFor(state.counselorId)[state.date] || [];
        state.slot = list.filter(function (slot) { return slot.value === button.dataset.value; })[0] || null;
        renderSlots();
        renderSummary();
    });

    calPrev.addEventListener('click', function () {
        state.month = new Date(state.month.getFullYear(), state.month.getMonth() - 1, 1);
        renderCalendar();
    });

    calNext.addEventListener('click', function () {
        state.month = new Date(state.month.getFullYear(), state.month.getMonth() + 1, 1);
        renderCalendar();
    });

    radios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            chooseCounselor(Number(radio.value), false);
        });
    });

    modeRadios.forEach(function (radio) {
        radio.addEventListener('change', applyMode);
    });

    consent.addEventListener('change', renderSummary);

    // Initial state, restoring the previous choice after a failed submit.
    var initial = selectedRadio() || radios[0];
    if (initial) {
        initial.checked = true;
        var selected = config.selected || {};
        var restoreStart = selected.start && Number(selected.counselorId) === Number(initial.value) ? selected.start : '';
        if (restoreStart) {
            var key = restoreStart.slice(0, 10);
            var list = daysFor(initial.value)[key] || [];
            var match = list.filter(function (slot) { return slot.value === restoreStart; })[0];
            if (match) {
                state.date = key;
                state.slot = match;
            }
        }
        chooseCounselor(Number(initial.value), !!state.slot);
    }
}());