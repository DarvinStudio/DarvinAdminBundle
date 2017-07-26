$(document).ready(function () {
    $('body').on('submit', 'form.ajax[action][method]', function (e) {
        e.preventDefault();

        var $form = $(this);

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
        }).done(function (data) {
            notify(data.message, data.success ? 'success' : 'error');

            if (data.html) {
                $form.replaceWith(data.html);
            }
            if (data.redirectUrl) {
                setTimeout(function () {
                    document.location.href = data.redirectUrl;
                }, NOTY_TIMEOUT);
            }
        }).fail(onAjaxFail);
    });
});
