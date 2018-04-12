$(document).ready(function () {
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

            if (reloadPage && !$formReplacement.closest('.property_forms').parent('.searchable_results').length) {
                $.ajax().done(function (html) {
                    $formReplacement.closest('.section_table').replaceWith($(html).find('.section_table:first'));
                }).fail(onAjaxFail);
            }
        }).fail(onAjaxFail);
    };

    var toggleButtons = function ($field) {
        if ('undefined' === typeof $field.data('original-value')) {
            return;
        }

        var $form = $field.closest('.property_form');
        $form.attr('data-modified', $field.val().toString() !== $field.data('original-value').toString() ? 1 : 0);

        var $forms = $form.closest('.property_forms');

        if (1 != $form.attr('data-modified') && !$forms.find('form.property_form[data-modified="1"]').length) {
            $forms.find('.property_forms_submit').hide();
        } else {
            $forms.find('.property_forms_submit').show();
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

        $context.find('.property_form .field[type!="checkbox"]').each(function () {
            toggleButtons($(this));
        });

        $context
            .on('change', '.property_form .field[type!="checkbox"]', function () {
                toggleButtons($(this));
            })
            .on('keyup', '.property_form input', function () {
                toggleButtons($(this));
            })
            .on('change', '.property_form input[type="checkbox"]', function () {
                var $checkbox = $(this);

                submitForm($checkbox.closest('form.property_form'), $checkbox.data('reload-page'));
            })
            .on('click', '.property_form [type="reset"]', function (e) {
                e.preventDefault();

                var $field = $(this).siblings('.field');

                $field
                    .val($field.data('original-value'))
                    .trigger('change');
            })
            .on('click', '.property_forms .property_forms_submit', function () {
                var $forms     = $(this).closest('.property_forms').find('form.property_form[data-modified="1"]'),
                    reloadPage = false;

                $forms.each(function (i) {
                    var $form = $(this);

                    if (!reloadPage && $form.find('input, select').data('reload-page')) {
                        reloadPage = true;
                    }

                    submitForm($form, i === $forms.length - 1 ? reloadPage : false);
                });
            })
            .on('submit', 'form.property_form', function (e) {
                e.preventDefault();

                var $form = $(this);

                submitForm($form, $form.find('input, select').data('reload-page'));
            });
    })();

    $(document).on('searchComplete', function (e, results) {
        init(results);
    });
});
