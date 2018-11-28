$(() => {
    $('body').on('submit', 'form.js-ajax[action][method]', (e) => {
        e.preventDefault();

        let $form = $(e.currentTarget);

        let options = $form.data();

        if (options.submitted) {
            return;
        }

        $form
            .data('submitted', true)
            .find('[type="submit"]').append(AJAX_LOADER);

        $.ajax({
            url:         $form.attr('action'),
            type:        $form.attr('method'),
            data:        new FormData($form[0]),
            contentType: false,
            processData: false
        }).done((data) => {
            notify(data.message, data.success ? 'success' : 'error');

            if (options.reloadPage) {
                $.ajax({
                    cache: false
                }).done(function (html) {
                    $form.closest('.section_table').replaceWith($(html).find('.section_table:first'));
                }).fail(onAjaxFail);
            } else if (data.html) {
                $form.replaceWith(data.html);
            }
            if (data.redirectUrl) {
                setTimeout(() => {
                    document.location.href = data.redirectUrl;
                }, NOTY_TIMEOUT);
            }
        }).always(() => {
            $form.removeData('submitted');
        }).fail(onAjaxFail);
    });
});
