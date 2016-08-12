$(document).ready(function () {
    var CLASSES = [
        '',
        'checked',
        'unchecked'
    ];

    function checkInput($checkbox, index) {
        if (index < 0 || index > 2) {
            index = 0;
        }

        $checkbox
            .data('index', index).addClass(CLASSES[index])
            .find('input')[index].checked = true;
    }

    function uncheckInput($checkbox, index) {
        $checkbox.removeClass(CLASSES[index])
            .find('input')[index].checked = false;
    }

    (function init() {
        $('.tri_state_checkbox').each(function () {
            var $checkbox = $(this);
            $checkbox.addClass('ready').find(':not(input)').remove();

            checkInput($checkbox, $checkbox.find('input:checked').index());
        });
    })();

    $('body').on('click', '.tri_state_checkbox', function () {
        var $checkbox = $(this);
        var index = $checkbox.data('index');

        uncheckInput($checkbox, index);

        index++;

        checkInput($checkbox, index);
    });
});
