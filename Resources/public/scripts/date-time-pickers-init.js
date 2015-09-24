$(document).ready(function () {
    var locale = 'en' !== LOCALE ? LOCALE : '';
    var options = $.extend({}, $.datepicker.regional[locale], $.timepicker.regional[locale], {
        dateFormat: 'dd.mm.yy'
    });

    var init;
    (init = function () {
        $('input.date').datepicker(options);
        $('input.datetime').datetimepicker(options);
        $('input.time').timepicker(options);
    })();

    $(document).bind('ajaxSuccess', init);
});
