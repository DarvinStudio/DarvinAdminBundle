$(document).on(App.events.ajax.html, (e, args) => {
    let locale = $('body').data('locale');

    if ('en' === locale) {
        locale = '';
    }

    let options = $.extend(
        $.datepicker.regional['undefined' !== typeof $.datepicker.regional[locale] ? locale : ''],
        $.timepicker.regional['undefined' !== typeof $.timepicker.regional[locale] ? locale : ''],
        {
            dateFormat: 'dd.mm.yy'
        }
    );

    ['date', 'datetime', 'time'].map((type) => {
        let $inputs = args.$html.find('input.js-' + type + 'picker');

        if ($inputs.length) {
            $inputs[type + 'picker'](options);
        }
    });
});
