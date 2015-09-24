$(document).ready(function () {
    var locale = 'en' !== LOCALE ? LOCALE : '';

    var init;
    (init = function () {
        $('input[type="text"].datetime').each(function () {
            var $input = $(this);
            $input.datetimepicker($.extend({}, $.datepicker.regional[locale], $.timepicker.regional[locale]));
        });
    })();

    $(document).bind('ajaxSuccess', init);
});
