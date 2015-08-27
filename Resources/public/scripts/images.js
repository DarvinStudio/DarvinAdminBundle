$(document).ready(function () {
    $('body').on('click', '.image_delete[data-url]', function (e) {
        e.preventDefault();

        var $deleteLink = $(this);

        if ($deleteLink.data('submitted') || !confirm(Translator.trans('image.action.delete.confirm'))) {
            return;
        }

        $deleteLink.data('submitted', true);

        var $image = $deleteLink.closest('.image');

        $.ajax({
            type: 'post',
            url:  $deleteLink.data('url')
        }).done(function () {
            $image.remove();

            noty({
                text: Translator.trans('image.action.delete.success'),
                type: 'success'
            });
        }).error(onAjaxError);
    });
});
