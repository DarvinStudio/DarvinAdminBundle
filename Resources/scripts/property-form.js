$(document).ready(function () {
    var SELECTOR = {
        container: '.js-property-forms',
        form:      'form.js-property',
        submit:    '.js-property-submit'
    };

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

            notify(data.message, data.success ? 'success' : 'error');

            if (reloadPage && !$formReplacement.closest(SELECTOR.container).parent('.searchable_results').length) {
                $.ajax({
                    cache: false
                }).done(function (html) {
                    $formReplacement.closest('.section_table').replaceWith($(html).find('.section_table:first'));
                }).fail(onAjaxFail);
            }
        }).fail(onAjaxFail);
    };

    var toggleButtons = function ($field) {
        if ('undefined' === typeof $field.data('original-value')) {
            return;
        }

        var $form = $field.closest(SELECTOR.form);
        $form.attr('data-modified', $field.val().toString() !== $field.data('original-value').toString() ? 1 : 0);

        var $forms = $form.closest(SELECTOR.container);

        if (1 != $form.attr('data-modified') && !$forms.find(SELECTOR.form + '[data-modified="1"]').length) {
            $forms.find(SELECTOR.submit).hide();
        } else {
            $forms.find(SELECTOR.submit).show();
        }
        if (1 != $form.attr('data-modified')) {
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

    var init;
    (init = function (context) {
        var $context = $(context || 'body');

        $context.find(SELECTOR.form + ' .field[type!="checkbox"]').each(function () {
            toggleButtons($(this));
        });

        $context
            .on('change', SELECTOR.form + ' .field[type!="checkbox"]', function () {
                toggleButtons($(this));
            })
            .on('keyup', SELECTOR.form + ' input', function () {
                toggleButtons($(this));
            })
            .on('change', SELECTOR.form + ' input[type="checkbox"]', function () {
                var $checkbox = $(this);

                submitForm($checkbox.closest(SELECTOR.form), $checkbox.data('reload-page'));
            })
            .on('click', SELECTOR.form + ' [type="reset"]', function (e) {
                e.preventDefault();

                var $field = $(this).siblings('.field');

                $field
                    .val($field.data('original-value'))
                    .trigger('change');
            })
            .on('click', [SELECTOR.container, SELECTOR.submit].join(' '), function () {
                var $forms     = $(this).closest(SELECTOR.container).find(SELECTOR.form + '[data-modified="1"]'),
                    reloadPage = false;

                $forms.each(function (i) {
                    var $form = $(this);

                    if (!reloadPage && $form.find('input, select').data('reload-page')) {
                        reloadPage = true;
                    }

                    submitForm($form, i === $forms.length - 1 ? reloadPage : false);
                });
            })
            .on('submit', SELECTOR.form, function (e) {
                e.preventDefault();

                var $form = $(this);

                submitForm($form, $form.find('input, select').data('reload-page'));
            });
    })();

    $(document).on('searchComplete', function (e, results) {
        init(results);
    });
});
