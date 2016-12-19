$(document).ready(function () {
    var getWidget = function ($child) {
        return $child.closest('.slug_suffix');
    };

    var buildUrlPrefix = function ($widget) {
        var parentSlug = $widget.closest('form').find($widget.data('parent-select')).children('option:selected')
            .data($widget.data('parent-option-data-slug'));

        return $widget.data('base-url') + ('undefined' !== typeof parentSlug ? parentSlug + '/' : '');
    };

    var buildUrl = function ($widget) {
        var slugSuffix = $widget.find('.form_widget input').val();

        return buildUrlPrefix($widget) + (slugSuffix ? slugSuffix : '___') + $widget.data('url-suffix');
    };

    var updateWidget = function ($widget) {
        var $input = $widget.find('.form_widget input');
        var $reset = $widget.find('.reset');
        var slugSuffix = $input.val();
        $input.data('default').toString() !== slugSuffix ? $reset.show() : $reset.hide();

        $widget.find('.url_prefix').text(buildUrlPrefix($widget));

        var url = buildUrl($widget);
        $widget.find('.link_widget a').attr('href', url).text(url);

        if ($widget.data('default-url').toString() !== url) {
            $widget.addClass('changed');

            return;
        }
        if (slugSuffix) {
            $widget.removeClass('changed');
        }
    };

    $('.slug_suffix').each(function () {
        var $widget = $(this);
        $widget.data('base-url', $widget.data('url-prefix'));

        var $form = $widget.closest('form');

        if ($widget.data('parent-select')) {
            var $parent = $form.find($widget.data('parent-select'));

            if ($parent.length) {
                var parentSlug = $parent.find('option:selected').data($widget.data('parent-option-data-slug'));

                if ('undefined' !== typeof parentSlug) {
                    var baseUrl = $widget.data('url-prefix');
                    var index = baseUrl.indexOf(parentSlug);

                    if (-1 !== index) {
                        baseUrl = baseUrl.slice(0, index);
                        $widget.data('base-url', baseUrl);
                    }
                }
            }
        }

        updateWidget($widget);

        $widget.find('.form_widget input').counter({
            count:  'up',
            goal:   'sky',
            target: $widget.find('.form_widget .input_value')
        });
        $form.on('change', $widget.data('parent-select'), function () {
            updateWidget($widget);
        });
    });

    $('body')
        .on('click', '.slug_suffix .edit', function () {
            var $widget = getWidget($(this));

            $widget.find('.form_widget').show();
            $widget.find('.link_widget').hide();
        })
        .on('click', '.slug_suffix .reset', function () {
            var $widget = getWidget($(this));

            var $input = $widget.find('.form_widget input');
            $input.val($input.data('default')).trigger('change');

            $widget.find('.update').trigger('click');
        })
        .on('click', '.slug_suffix .update', function () {
            var $widget = getWidget($(this));

            updateWidget($widget);

            $widget.find('.form_widget').hide();
            $widget.find('.link_widget').show();
        })
        .on('click', '.slug_suffix.changed .link_widget a', function (e) {
            e.preventDefault();
        });
});
