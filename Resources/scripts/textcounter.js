$(function () {
    $('input[data-max-chars]').textcounter({
        autoCustomAttr:      'data-max-chars',
        countContainerClass: 'input_note',
        countDown:           true,
        countDownText:       Translator.trans('textcounter.count_down_text'),
        countOverflow:       true,
        countOverflowText:   Translator.trans('textcounter.count_overflow_text'),
        countSpaces:         true,
        displayErrorText:    false,
        max:                 'autocustom',
        stopInputAtMaximum:  false
    });
});
