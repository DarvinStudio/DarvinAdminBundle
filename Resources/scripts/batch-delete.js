(() => {
    const SELECTORS = {
        check:     'input[type="checkbox"].js-batch-delete-check[data-id]',
        checkAll:  'input[type="checkbox"].js-batch-delete-check-all',
        container: '.js-property-forms',
        form:      'form.js-batch-delete'
    };

    $('body')
        .on('change', SELECTORS.checkAll, (e) => {
            let $checkAll = $(e.currentTarget);

            let checked = $checkAll[0].checked;

            $checkAll.closest(SELECTORS.container).find(SELECTORS.check).each((i, check) => {
                let $check = $(check);

                $check[0].checked = checked;

                $check.trigger('change');
            });
        })
        .on('change', SELECTORS.check, (e) => {
            let $check = $(e.currentTarget);

            let $container = $check.closest(SELECTORS.container);

            let $checkAll = $container.find(SELECTORS.checkAll),
                $form     = $container.find(SELECTORS.form);

            let $submit = $form.find('[type="submit"]:first');

            $form.find('input[type="checkbox"][value="' + $check.data('id') + '"]')[0].checked = $check[0].checked;

            if (!$check[0].checked) {
                $checkAll[0].checked = false;

                if ($submit.is(':visible') && 0 === $(SELECTORS.check + ':checked').length) {
                    $submit.hide();
                }

                return;
            }

            let $checks = $container.find(SELECTORS.check);

            $checkAll[0].checked = $checks.length === $checks.filter(':checked').length;

            $submit.show();
        });
})();
