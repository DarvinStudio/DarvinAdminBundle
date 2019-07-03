$(document).on(App.events.ajax.html, (e, args) => {
    let options = {
        countContainerClass: 'input_note',
        countDown:           true,
        countDownText:       Translator.trans('textcounter.count_down_text'),
        countOverflow:       true,
        countOverflowText:   Translator.trans('textcounter.count_overflow_text'),
        countSpaces:         true,
        displayErrorText:    false,
        stopInputAtMaximum:  false
    };

    args.$html.find('input[data-max-chars]').each((i, input) => {
        let $input = $(input);

        $input.textcounter($.extend(options, {
            max: $input.data('max-chars')
        }));
    });
});
