(() => {
    const SELECTORS = {
        container: '.js-property-forms:first',
        errors:    '.js-form-errors',
        form:      'form.js-property',
        submit:    '.js-property-submit'
    };

    const toggle = (field) => {
        let $field = $(field);

        if ('undefined' === typeof $field.data('original-value')) {
            return;
        }

        let $form = $field.closest(SELECTORS.form);

        let $container = $form.closest(SELECTORS.container);

        let $submit = $container.find(SELECTORS.submit);

        $form.attr('data-modified', $field.val().toString() !== $field.data('original-value').toString() ? 1 : 0);

        $submit.show();

        if (1 !== parseInt($form.attr('data-modified')) && !$container.find(SELECTORS.form + '[data-modified="1"]').length) {
            $submit.hide();
        }
        if (1 !== parseInt($form.attr('data-modified'))) {
            $form.find('[type="submit"], [type="reset"]').remove();

            return;
        }
        if (!$form.find('[type="submit"]').length) {
            $form.append('<button type="submit">o</button>');
        }
        if (!$form.find('[type="reset"]').length) {
            $form.append('<button type="reset">x</button>');
        }
    };

    $(document).on('app.html', (e, args) => {
        args.$html.find(SELECTORS.form + ' .js-property-field[type!="checkbox"]').each((i, field) => {
            toggle(field);
        });
    });

    $('body')
        .on('change', SELECTORS.form + ' .js-property-field[type!="checkbox"]', (e) => {
            toggle(e.currentTarget);
        })
        .on('keyup', SELECTORS.form + ' input', (e) => {
            toggle(e.currentTarget);
        })
        .on('submit', SELECTORS.form, (e) => {
            let $form = $(e.currentTarget);

            $form.data('reload-page', $form.find('.js-property-field:first').data('reload-page'));
        })
        .on('change', SELECTORS.form + ' input[type="checkbox"]', (e) => {
            let $checkbox = $(e.currentTarget);

            $checkbox.closest(SELECTORS.form)
                .data('reload-page', $checkbox.data('reload-page'))
                .trigger('submit');
        })
        .on('click', SELECTORS.form + ' [type="reset"]', (e) => {
            e.preventDefault();

            let $field = $(e.currentTarget).siblings('.js-property-field:first');

            $field
                .val($field.data('original-value'))
                .trigger('change');

            $field.closest(SELECTORS.form).find(SELECTORS.errors).remove();
        })
        .on('click', SELECTORS.container + ' ' + SELECTORS.submit, (e) => {
            let $forms     = $(e.currentTarget).closest(SELECTORS.container).find(SELECTORS.form + '[data-modified="1"]'),
                reloadPage = false;

            $forms.each((i, form) => {
                let $form = $(form);

                if (!reloadPage && $form.find('input, select').data('reload-page')) {
                    reloadPage = true;
                }

                $form
                    .data('reload-page', i === $forms.length - 1 ? reloadPage : false)
                    .trigger('submit');
            });
        });
})();
