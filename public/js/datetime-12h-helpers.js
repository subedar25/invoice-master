/**
 * Shared datetime helpers for mm-dd-yyyy and 12-hour AM/PM.
 * Used by: timesheets index, time_off_requests index.
 * Requires: jQuery (for setFormHiddenTimes).
 */
(function () {
    'use strict';

    window.toMMDDYYYY = function toMMDDYYYY(ymdStr) {
        if (!ymdStr) return '';
        var p = ymdStr.split('-');
        if (p.length !== 3) return ymdStr;
        return (p[1].length === 1 ? '0' + p[1] : p[1]) + '-' + (p[2].length === 1 ? '0' + p[2] : p[2]) + '-' + p[0];
    };

    window.parseMMDDYYYY = function parseMMDDYYYY(str) {
        if (!str || !str.trim()) return '';
        var s = str.trim();
        var match = s.match(/^(\d{1,2})-(\d{1,2})-(\d{4})$/);
        if (match) return match[3] + '-' + match[1].padStart(2, '0') + '-' + match[2].padStart(2, '0');
        if (s.match(/^\d{4}-\d{2}-\d{2}/)) return s.substring(0, 10);
        return '';
    };

    window.formatTableCellDateTime = function formatTableCellDateTime(raw) {
        if (!raw) return '';
        var parts = String(raw).replace('T', ' ').split(/[\s:-]/);
        if (parts.length < 5) return raw;
        var y = parts[0], m = parts[1], d = parts[2], h24 = parseInt(parts[3] || 0, 10), min = parts[4] || '00';
        var dateStr = (m.length === 1 ? '0' + m : m) + '-' + (d.length === 1 ? '0' + d : d) + '-' + y;
        var h12 = h24 === 0 ? 12 : h24 > 12 ? h24 - 12 : h24;
        var ampm = h24 < 12 ? 'AM' : 'PM';
        return dateStr + ' ' + h12 + ':' + (min.length === 1 ? '0' + min : min) + ' ' + ampm;
    };

    window.hour12to24 = function hour12to24(hour, ampm) {
        hour = parseInt(hour, 10);
        if (ampm === 'AM') return hour === 12 ? 0 : hour;
        return hour === 12 ? 12 : hour + 12;
    };

    window.buildDateTimeFrom12h = function buildDateTimeFrom12h(dateStr, hour, minute, ampm) {
        if (!dateStr) return '';
        var ymd = window.parseMMDDYYYY(dateStr) || dateStr;
        var h24 = window.hour12to24(hour, ampm);
        return ymd + ' ' + (h24 < 10 ? '0' : '') + h24 + ':' + (minute.length === 1 ? '0' + minute : minute) + ':00';
    };

    window.parseDateTimeTo12h = function parseDateTimeTo12h(dtStr) {
        if (!dtStr) return { date: '', hour: '12', minute: '00', ampm: 'AM' };
        var parts = String(dtStr).replace('T', ' ').split(/[\s:-]/);
        var y = parts[0], m = parts[1], d = parts[2], h24 = parseInt(parts[3] || 0, 10), min = parts[4] || '00';
        var date = y + '-' + m + '-' + d;
        var h12, ampm;
        if (h24 === 0) { h12 = 12; ampm = 'AM'; }
        else if (h24 < 12) { h12 = h24; ampm = 'AM'; }
        else if (h24 === 12) { h12 = 12; ampm = 'PM'; }
        else { h12 = h24 - 12; ampm = 'PM'; }
        return { date: date, hour: String(h12), minute: min.length === 1 ? '0' + min : min, ampm: ampm };
    };

    /**
     * Set hidden start_time/end_time from date + hour + minute + ampm fields.
     * @param {string} prefix - 'add' or 'edit' (ids: #add_start_date, #edit_start_date, etc.)
     */
    window.setFormHiddenTimes = function setFormHiddenTimes(prefix) {
        if (typeof window.jQuery === 'undefined') return;
        var $ = window.jQuery;
        var sd = $('#' + prefix + '_start_date').val(), sh = $('#' + prefix + '_start_hour').val(), sm = $('#' + prefix + '_start_minute').val(), sa = $('#' + prefix + '_start_ampm').val();
        var ed = $('#' + prefix + '_end_date').val(), eh = $('#' + prefix + '_end_hour').val(), em = $('#' + prefix + '_end_minute').val(), ea = $('#' + prefix + '_end_ampm').val();
        $('#' + prefix + '_start_time').val(window.buildDateTimeFrom12h(sd, sh, sm, sa));
        $('#' + prefix + '_end_time').val(ed ? window.buildDateTimeFrom12h(ed, eh, em, ea) : '');
    };
})();
