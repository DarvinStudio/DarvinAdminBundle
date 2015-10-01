var AJAX_LOADER = '<span class="ajax_loader"></span>';

$(document).bind('ajaxComplete', function () {
    $('.ajax_loader').remove();

    $('a, form').removeData('submitted');
});

var onAjaxError = function (jqXHR) {
    var message = 'exception.' + jqXHR.status;
    var translated = Translator.trans(message);

    if (translated === message) {
        translated = Translator.trans('exception.500');
    }

    noty({
        text: translated,
        type: 'error'
    });
};
