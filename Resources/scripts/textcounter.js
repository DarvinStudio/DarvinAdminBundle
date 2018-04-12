$(function () {
    $('input[data-max-chars]').each(function () {
        var $input = $(this);

        $input.textcounter({
            countContainerClass: 'input_note',
            countDown:           true,
            countDownText:       Translator.trans('textcounter.count_down_text'),
            countOverflow:       true,
            countOverflowText:   Translator.trans('textcounter.count_overflow_text'),
            countSpaces:         true,
            displayErrorText:    false,
            max:                 $input.data('max-chars'),
            stopInputAtMaximum:  false
        });
    });
});
