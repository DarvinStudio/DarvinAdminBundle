const App = (() => {
    const NOTIFICATION_TIMEOUT = 1500;
    const PRELOADER            = '<div class="preloader">';

    const notify = (text, type) => {
        if (text) {
            new Noty({
                text:    Translator.trans(text),
                type:    type || 'success',
                theme:   'bootstrap-v3',
                timeout: NOTIFICATION_TIMEOUT
            }).show();
        }
    };

    return {
        notify:     notify,
        onAjaxFail: (jqXHR) => {
            let message = 'exception.' + jqXHR.status;

            let translated = Translator.trans(message);

            if (translated === message) {
                translated = Translator.trans('exception.500');
            }

            notify(translated, 'error');
        },
        preload: () => {
            $('body').append(PRELOADER);
        },
        redirect: (url) => {
            if (url) {
                setTimeout(() => {
                    document.location.href = url;
                }, NOTIFICATION_TIMEOUT);
            }
        }
    };
})();

$(document).on('ajaxComplete', () => {
    $('.preloader').remove();

    $('a, form').removeData('submitted');
});
