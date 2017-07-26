$(document).ready(function () {
    var submitForm = function ($form) {
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
        }).fail(onAjaxFail);
    };

    var toggleButtons = function ($field) {
        if ('undefined' === typeof $field.data('original-value')) {
            return;
        }

        var $form = $field.parents('.property_form').first();
        $form.attr('data-modified', $field.val().toString() !== $field.data('original-value').toString() ? 1 : 0);

        var $forms = $form.parents('.property_forms').first();

        if (1 != $form.attr('data-modified') && !$forms.find('form.property_form[data-modified="1"]').length) {
            $forms.find('.property_forms_submit').hide();
        } else {
            $forms.find('.property_forms_submit').show();
        }
        if (1 != $form.attr('data-modified')) {
            $form.find('.errors, [type="submit"], [type="reset"]').remove();

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
        if ('undefined' === typeof context) {
            context = 'body';
        }

        var $context = $(context);

        $context.find('.property_form .field[type!="checkbox"]').each(function () {
            toggleButtons($(this));
        });

        $context
            .on('change', '.property_form input[type="checkbox"]', function () {
                submitForm($(this).parents('form.property_form').first());
            })
            .on('change', '.property_form .field[type!="checkbox"]', function () {
                toggleButtons($(this));
            })
            .on('keyup', '.property_form input', function () {
                toggleButtons($(this));
            })
            .on('click', '.property_form [type="reset"]', function (e) {
                e.preventDefault();

                var $field = $(this).siblings('.field');

                $field
                    .val($field.data('original-value'))
                    .trigger('change');
            })
            .on('click', '.property_forms .property_forms_submit', function () {
                $(this).parents('.property_forms').first().find('form.property_form[data-modified="1"]').submit();
            })
            .on('submit', 'form.property_form', function (e) {
                e.preventDefault();
                submitForm($(this));
            });
    })();

    $(document).on('searchComplete', function (e, results) {
        init(results);
    });
});
