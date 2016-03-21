$(document).ready(function () {
    var getWidget = function ($child) {
        return $child.parents('.slug_suffix').first();
    };

    var updateLink = function ($widget) {
        var $input = $widget.find('.form_widget input');
        var $link = $widget.find('.link_widget a');

        var slugSuffix = $input.val();
        var url = $widget.find('.url_prefix').text() + slugSuffix + $widget.find('.url_suffix').text();
        $link.attr('href', url).text(url);

        if (slugSuffix !== $input.data('default')) {
            $link.addClass('changed');
            $widget.find('.reset').show();

            return;
        }

        $link.removeClass('changed');
        $widget.find('.reset').hide();
    };
    $('.slug_suffix').each(function () {
        updateLink($(this));
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
            $input.val($input.data('default'));

            $widget.find('.update').trigger('click');
        })
        .on('click', '.slug_suffix .update', function () {
            var $widget = getWidget($(this));

            updateLink($widget);

            $widget.find('.form_widget').hide();
            $widget.find('.link_widget').show();
        })
        .on('click', '.slug_suffix .link_widget a.changed', function (e) {
            e.preventDefault();
        });
});
