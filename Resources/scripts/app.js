const App = (() => {
    const NOTIFICATION_TIMEOUT = 1500;
    const PRELOADER            = '<div class="preloader">';

    const notify = (text, type = 'success') => {
        if (text) {
            new Noty({
                text:    Translator.trans(text),
                type:    type,
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
            if ('undefined' !== typeof url && null !== url) {
                setTimeout(() => {
                    document.location.href = url;
                }, NOTIFICATION_TIMEOUT);

                return true;
            }

            return false;
        },
        startPreloading: (tag = 'app') => {
            $('body').append($(PRELOADER).data('tag', tag));
        },
        stopPreloading: (tag = 'app') => {
            $('.preloader').each((i, preloader) => {
                let $preloader = $(preloader);

                if ($preloader.data('tag') === tag) {
                    $preloader.remove();
                }
            });
        }
    };
})();

$(() => {
    $(document).trigger('app.html', {
        $html: $('body')
    });
});

$(document).on('ajaxComplete', () => {
    App.stopPreloading();

    $('a, form').removeData('submitted');
});
