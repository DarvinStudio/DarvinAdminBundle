$(() => {
    $('body').on('submit', 'form.js-ajax[action][method]', (e) => {
        e.preventDefault();

        let $form = $(e.currentTarget);

        let options = $form.data();

        if (options.submitted) {
            return;
        }

        $form.data('submitted', true);

        App.startPreloading();

        $.ajax({
            url:         $form.attr('action'),
            type:        $form.attr('method'),
            data:        new FormData($form[0]),
            contentType: false,
            processData: false
        }).done((data) => {
            App.notify(data.message, data.success ? 'success' : 'error');
            App.redirect(data.redirectUrl);

            if (data.success && options.reloadPage) {
                App.startPreloading('form');

                $.ajax({
                    cache: false
                }).done((html) => {
                    let $html = $(html);

                    let $content = $html.find('#js-content');

                    if (!$content.length) {
                        $content = $html;
                    }

                    $('#js-content').html($content);

                    $(document).trigger('app.html', {
                        $html: $content
                    });
                }).always(() => {
                    App.stopPreloading('form');
                }).fail(App.onAjaxFail);

                return;
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
    });
});
