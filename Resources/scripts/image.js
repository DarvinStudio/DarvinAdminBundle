(() => {
    const CLASSES = {
        disable: 'js-image-disable',
        enable:  'js-image-enable'
    };

    const SELECTORS = {
        'delete':   '.js-image-delete[data-url]',
        'image':    '.js-image[data-id]',
        'sortable': '.js-images[data-sort-url]',
        'toggle':   '.js-image-toggle[data-disable-title][data-disable-url][data-enable-title][data-enable-url]'
    };

    $(document).on(App.events.ajax.html, (e, args) => {
        let $sortable = args.$html.find(SELECTORS.sortable);

        if (!$sortable.length) {
            return;
        }

        $sortable.sortable({
            placeholder: 'ui-state-highlight',
            update:      () => {
                $.ajax({
                    url:  $sortable.data('sort-url'),
                    type: 'post',
                    data: {
                        ids: $sortable.find(SELECTORS.image).map((i, image) => {
                            return $(image).data('id');
                        }).get()
                    }
                }).fail(App.onAjaxFail);
            }
        });
        $sortable.disableSelection();
    });

    $('body')
        .on('click', SELECTORS.delete, (e) => {
            e.preventDefault();

            let $link = $(e.currentTarget);

            if ($link.data('submitted') || !confirm(Translator.trans('image.action.delete.confirm'))) {
                return;
            }

            $link.data('submitted', true);

            let $image = $link.closest(SELECTORS.image);

            $.ajax({
                url:  $link.data('url'),
                type: 'post'
            }).done(() => {
                $image.remove();

                App.notify('image.action.delete.success');
            }).fail(App.onAjaxFail);
        })
        .on('click', SELECTORS.toggle, (e) => {
            e.preventDefault();

            let $link = $(e.currentTarget);

            if ($link.hasClass(CLASSES.disable)) {
                $.ajax({
                    url:  $link.data('disable-url'),
                    type: 'post'
                }).done(() => {
                    $link
                        .removeClass(CLASSES.disable)
                        .addClass(CLASSES.enable)
                        .attr('title', $link.data('enable-title'));

                    App.notify('image.action.disable.success');
                }).fail(App.onAjaxFail);

                return;
            }

            $.ajax({
                url:  $link.data('enable-url'),
                type: 'post'
            }).done(() => {
                $link
                    .removeClass(CLASSES.enable)
                    .addClass(CLASSES.disable)
                    .attr('title', $link.data('disable-title'));

                App.notify('image.action.enable.success');
            }).fail(App.onAjaxFail);
        });
})();
