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
            url:  $deleteLink.data('url'),
            type: 'post'
        }).done(function () {
            $image.remove();

            noty({
                text: Translator.trans('image.action.delete.success'),
                type: 'success'
            });
        }).error(onAjaxError);
    });

    var $sortable = $('.table_row .images[data-sort-url]');

    if ($sortable.length) {
        $sortable.sortable({
            placeholder: 'ui-state-highlight',
            update:      function () {
                $.ajax({
                    url:  $sortable.data('sort-url'),
                    type: 'post',
                    data: {
                        ids: $sortable.find('.image[data-id]').map(function () {
                            return $(this).data('id');
                        }).get()
                    }
                }).error(onAjaxError);
            }
        });
        $sortable.disableSelection();
    }
});
