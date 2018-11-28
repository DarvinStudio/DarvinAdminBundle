$(() => {
    const SELECTOR = {
        container: '.js-property-forms',
        form:      'form.js-property',
        submit:    '.js-property-submit'
    };

    /*
    var submitForm = function ($form, reloadPage) {
        if ($form.data('submitted')) {
            return;
        }

        $form
            .data('submitted', true)
            .append(AJAX_LOADER);

        $.ajax({
            async: false,
            data:  $form.serialize(),
            type:  'post',
            url:   $form.attr('action')
        }).done(function (data) {
            var $formReplacement = $(data.form);
            $form.replaceWith($formReplacement);

            toggleButtons($formReplacement.find('.field'));

            $(document).trigger('propertyFormSubmit', $formReplacement);

            App.notify(data.message, data.success ? 'success' : 'error');

            if (reloadPage && !$formReplacement.closest(SELECTOR.container).parent('.searchable_results').length) {
                $.ajax({
                    cache: false
                }).done(function (html) {
                    $formReplacement.closest('.section_table').replaceWith($(html).find('.section_table:first'));
                }).fail(App.onAjaxFail);
            }
        }).fail(App.onAjaxFail);
    };
    */

    const toggle = (field) => {
        let $field = $(field);

        if ('undefined' === typeof $field.data('original-value')) {
            return;
        }

        let $form = $field.closest(SELECTOR.form);

        let $container = $form.closest(SELECTOR.container);

        let $submit = $container.find(SELECTOR.submit);

        $form.attr('data-modified', $field.val().toString() !== $field.data('original-value').toString() ? 1 : 0);

        $submit.show();

        if (1 !== parseInt($form.attr('data-modified')) && !$container.find(SELECTOR.form + '[data-modified="1"]').length) {
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

    let init;
    (init = (context) => {
        let $context = $(context || 'body');

        $context.find(SELECTOR.form + ' .field[type!="checkbox"]').each((i, field) => {
            toggle(field);
        });

        $context
            .on('change', SELECTOR.form + ' .field[type!="checkbox"]', (e) => {
                toggle(e.currentTarget);
            })
            .on('keyup', SELECTOR.form + ' input', (e) => {
                toggle(e.currentTarget);
            })
            .on('submit', SELECTOR.form, (e) => {
                let $form = $(e.currentTarget);

                $form.data('reload-page', $form.find('.field').data('reload-page'));
            })
            .on('change', SELECTOR.form + ' input[type="checkbox"]', (e) => {
                let $checkbox = $(e.currentTarget);

                $checkbox.closest(SELECTOR.form)
                    .data('reload-page', $checkbox.data('reload-page'))
                    .trigger('submit');
            })
            .on('click', SELECTOR.form + ' [type="reset"]', (e) => {
                e.preventDefault();

                let $field = $(e.currentTarget).siblings('.field');

                $field
                    .val($field.data('original-value'))
                    .trigger('change');
            })
            .on('click', [SELECTOR.container, SELECTOR.submit].join(' '), (e) => {
                let $forms     = $(e.currentTarget).closest(SELECTOR.container).find(SELECTOR.form + '[data-modified="1"]'),
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

    $(document).on('searchComplete', (e, results) => {
        init(results);
    });
});
