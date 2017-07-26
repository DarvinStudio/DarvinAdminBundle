$(document).ready(function () {
    $('input[data-max-chars]').each(function () {
        var $input = $(this);

        $input
            .counter({
                goal: $input.data('max-chars'),
                msg:  Translator.trans('interface.characters_left')
            })
            .siblings('[id$="_counter"]:first').wrap('<div class="input_note"><p></p></div>');
    });
});
