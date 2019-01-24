(() => {
    const SELECTORS = {
        form:  'form.js-form',
        reset: '.js-form-reset'
    };

    const replaceUrl = (url) => {
        if (url && 'undefined' !== typeof history) {
            history.pushState(null, null, url);
        }
    };
    const replaceContent = (html) => {
        let $content = $('#js-content');

        if (!$content.length) {
            return false;
        }

        let $html = $(html);

        let $replacement = $html.find('#js-content');

        if (!$replacement.length) {
            $replacement = $html;
        }

        $content.html($replacement);

        $(document).trigger('app.html', {
            $html: $content
        });

        return true;
    };
    const submit = (form, reset = false) => {
        let $form = $(form);

        let options = $form.data();

        if (options.submitted || options.redirecting) {
            return;
        }

        let url  = $form.attr('action') || options.url || '',
            type = ($form.attr('method') || 'get').toLowerCase(),
            xhr  = new XMLHttpRequest();

        let params = {
            data: $form.serialize(),
            url:  url,
            type: type,
            xhr:  () => {
                return xhr;
            }
        };

        if ('get' === type) {
            if (reset) {
                params.data = '';
            }
            if (location.search === params.data || location.search === '?' + params.data) {
                return;
            }
        } else if ('undefined' !== typeof FormData) {
            $.extend(params, {
                data:        new FormData($form[0]),
                contentType: false,
                processData: false
            });
        }

        $form.data('submitted', true);

        App.startPreloading();

        $.ajax(params).done((data) => {
            if ('get' === type) {
                let parts = [location.pathname];

                if (params.data) {
                    parts.push('?', params.data);
                }

                replaceUrl(parts.join(''));
            }
            if (!$.isPlainObject(data)) {
                if (replaceContent(data)) {
                    return;
                }

                document.open();
                document.write(data);
                document.close();

                replaceUrl(xhr.responseURL);

                return;
            }

            App.notify(data.message, data.success ? 'success' : 'error');

            if (data.success && options.reloadPage && !$('#js-content').data('not-reloadable')) {
                App.startPreloading('form');

                let pageUrl = data.redirectUrl || '';

                $.ajax({
                    url:   pageUrl,
                    cache: false
                }).done(replaceContent).always(() => {
                    replaceUrl(pageUrl);
                }).always(() => {
                    App.stopPreloading('form');
                }).fail(App.onAjaxFail);

                return;
            }
            if (App.redirect(data.redirectUrl, !data.message)) {
                $form.data('redirecting', true);
            }
            if (data.html) {
                let $html   = $(data.html),
                    $target = $form;

                if (options.target) {
                    $target = $form.closest(options.target);

                    if (!$target.length) {
                        $target = $(options.target + ':first');
                    }
                }
                if ($target.length) {
                    $target.replaceWith($html);

                    $(document).trigger('app.html', {
                        $html: $html.parent()
                    });
                }
            }
        }).always(() => {
            $form.removeData('submitted');
        }).fail(App.onAjaxFail);
    };

    $('body')
        .on('click', SELECTORS.form + ' [type="submit"][name]', (e) => {
            let $submit = $(e.currentTarget);

            $submit.closest(SELECTORS.form).append('<input name="' + $submit.attr('name') + '" type="hidden">');
        })
        .on('click', [SELECTORS.form, SELECTORS.reset].join(' '), (e) => {
            e.preventDefault();

            submit($(e.currentTarget).closest(SELECTORS.form), true);
        })
        .on('submit', SELECTORS.form, (e) => {
            e.preventDefault();

            submit(e.currentTarget);
        });
})();
