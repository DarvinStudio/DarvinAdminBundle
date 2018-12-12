$(function () {
    var locale = $('body').data('locale');

    if ('en' === locale) {
        locale = '';
    }

    var options = $.extend(
        $.datepicker.regional['undefined' !== typeof $.datepicker.regional[locale] ? locale : ''],
        $.timepicker.regional['undefined' !== typeof $.timepicker.regional[locale] ? locale : ''],
        {
            dateFormat: 'dd.mm.yy'
        }
    );

    var init;
    (init = function (context) {
        var $context = $(context || 'body');

        ['date', 'datetime', 'time'].map(function (type) {
            $context.find('input.' + type)[type + 'picker'](options);
        });
    })();

    $(document).on('ajaxSuccess', init);
});
