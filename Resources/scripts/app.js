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
        redirect: (url) => {
            if (url) {
                setTimeout(() => {
                    document.location.href = url;
                }, NOTIFICATION_TIMEOUT);
            }
        },
        startPreloading: (tag) => {
            $('body').append($(PRELOADER).data('tag', tag || 'app'));
        },
        stopPreloading: (tag) => {
            tag = tag || 'app';

            $('.preloader').each((i, preloader) => {
                let $preloader = $(preloader);

                if ($preloader.data('tag') === tag) {
                    $preloader.remove();
                }
            });
        }
    };
})();

$(document).on('ajaxComplete', () => {
    App.stopPreloading();

    $('a, form').removeData('submitted');
});
