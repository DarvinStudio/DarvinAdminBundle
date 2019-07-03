(() => {
    const SELECTOR = '.name_enabled.type_boolean input[type="checkbox"]:first';

    $(document).on(App.events.ajax.html, (e, args) => {
        args.$html.find('.js-property-forms table').find('tr[data-level]').each((i, row) => {
            let $row = $(row);

            let $checkbox = $row.find(SELECTOR),
                level     = $row.data('level');

            if (!$checkbox.length) {
                return;
            }
            if ($row.next('tr[data-level="' + (level + 1) + '"]').length) {
                $checkbox.data('reload-page', 1);
            }
            if (level < 2 || $checkbox.is(':checked')) {
                return;
            }

            let $parent = $row.prevAll('tr[data-level="' + (level - 1) + '"]:first').find(SELECTOR);

            $checkbox.removeAttr('disabled');

            if (!$parent.is(':checked') || $parent.is(':disabled')) {
                $checkbox.attr('disabled', 'disabled');
            }
        });
    });
})();
