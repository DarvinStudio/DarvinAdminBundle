$(document).ready(function () {
    var locale = 'en' !== LOCALE ? LOCALE : '';
    var options = $.extend({}, $.datepicker.regional[locale], $.timepicker.regional[locale], {
        dateFormat: 'dd.mm.yy'
    });

    var init;
    (init = function () {
        ['date', 'datetime', 'time'].map(function (type) {
            $('input.' + type)[type + 'picker'](options);
        });
    })();

    $(document).bind('ajaxSuccess', init);
});
