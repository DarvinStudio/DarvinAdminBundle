$(document).ready(function () {
    if (!$('.property_form').length) {
        return;
    }

    var submitForm = function ($form, redirect) {
        if ($form.data('submitted')) {
            return;
        }
        if ('undefined' === typeof redirect) {
            redirect = true;
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
            toggleButtons($formReplacement.find('.field'));
            $form.replaceWith($formReplacement);

            $(document).trigger('propertyFormSubmit', $formReplacement);

            noty({
                text: Translator.trans(data.message),
                type: data.success ? 'success' : 'error'
            });

            if (!data.success || !redirect) {
                return;
            }

            setTimeout(function () {
                document.location.href = '';
            }, $.noty.defaults.timeout);
        }).error(onAjaxError);
    };

    var toggleButtons = function ($field) {
        if ('undefined' === typeof $field.data('original-value')) {
            return;
        }

        var $form = $field.parents('.property_form').first();
        $form.attr('data-modified', $field.val().toString() !== $field.data('original-value').toString() ? 1 : 0);

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

    $('.property_form .field[type!="checkbox"]').each(function () {
        toggleButtons($(this));
    });

    $('.property_forms').append('<button type="submit">' + Translator.trans('property_forms.submit') + '</button>');

    $('body')
        .on('change', '.property_form input[type="checkbox"]', function () {
            submitForm($(this).parents('form.property_form').first(), false);
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
        .on('click', '.property_forms [type="submit"]', function () {
            $(this).parents('.property_forms').first().find('form.property_form[data-modified="1"]').submit();
        })
        .on('submit', 'form.property_form', function (e) {
            e.preventDefault();
            submitForm($(this));
        });
});
