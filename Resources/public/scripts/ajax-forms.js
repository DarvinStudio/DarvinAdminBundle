$(document).ready(function () {
    $('body').on('submit', 'form.ajax', function (e) {
        e.preventDefault();

        var $form = $(this);

        $.ajax({
            data: $form.serialize(),
            type: $form.attr('method'),
            url:  $form.attr('action')
        }).done(function (data) {
            noty({
                text: Translator.trans(data.message),
                type: data.success ? 'success' : 'error'
            });

            if (!data.success) {
                $form.replaceWith(data.html);

                return;
            }

            setTimeout(function () {
                document.location.href = '';
            }, $.noty.defaults.timeout);
        }).error(onAjaxError);
    });
});
