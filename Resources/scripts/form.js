$(() => {
    $('body').on('submit', 'form.js-ajax[action][method]', (e) => {
        e.preventDefault();

        let $form = $(e.currentTarget);

        if ($form.data('submitted')) {
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

            if (data.html) {
                $form.replaceWith(data.html);
            }
            if (null !== data.redirectUrl) {
                setTimeout(() => {
                    document.location.href = data.redirectUrl;
                }, NOTY_TIMEOUT);
            }
        }).always(() => {
            $form.removeData('submitted');
        }).fail(onAjaxFail);
    });
});
