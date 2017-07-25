var AJAX_LOADER  = '<span class="ajax_loader"></span>';
var NOTY_TIMEOUT = 2000;

var notify = function (text, type) {
    if (!text) {
        return;
    }

    new Noty({
        text:    Translator.trans(text),
        type:    type || 'success',
        timeout: NOTY_TIMEOUT
    }).show();
};

var onAjaxFail = function (jqXHR) {
    var message = 'exception.' + jqXHR.status;
    var translated = Translator.trans(message);

    if (translated === message) {
        translated = Translator.trans('exception.500');
    }

    notify(translated, 'error');
};

$(document).bind('ajaxComplete', function () {
    $('.ajax_loader').remove();

    $('a, form').removeData('submitted');
});
