$(() => {
    $('body').on('submit', 'form.js-ajax[action][method]', (e) => {
        e.preventDefault();

        let $form = $(e.currentTarget);

        let options = $form.data();

        if (options.submitted) {
            return;
        }

        $form.data('submitted', true);

        App.preload();

        $.ajax({
            url:         $form.attr('action'),
            type:        $form.attr('method'),
            data:        new FormData($form[0]),
            contentType: false,
            processData: false
        }).done((data) => {
            App.notify(data.message, data.success ? 'success' : 'error');
            App.redirect(data.redirectUrl);

            if (options.reloadPage) {
                $.ajax({
                    cache: false
                }).done((html) => {
                    $form.closest('.section_table').replaceWith($(html).find('.section_table:first'));
                }).fail(App.onAjaxFail);

                return;
            }
            if (data.html) {
                $form.replaceWith(data.html);
            }
        }).always(() => {
            $form.removeData('submitted');
        }).fail(App.onAjaxFail);
    });
});
