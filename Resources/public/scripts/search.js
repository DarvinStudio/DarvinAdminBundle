$(document).ready(function () {
    var $searchables = $('.searchable[data-source]');

    if (!$searchables.length) {
        return;
    }

    var pending = false;

    $searchables.each(function () {
        var $searchable = $(this);
        var $results = $searchable.find('.searchable_results');

        var interval = setInterval(function () {
            if (pending) {
                return;
            }

            pending = true;
            $searchable.show().find('.searchable_title').append(AJAX_LOADER);

            $.ajax({
                url: $searchable.data('source')
            }).done(function (html) {
                var $html = $(html);

                if (!$html.find('tr').length) {
                    $searchable.remove();

                    return;
                }

                $results.html(html);
                $(document).trigger('searchComplete', $results);
            }).complete(function () {
                clearInterval(interval);
                pending = false;
            }).error(onAjaxError);
        }, 100);
    });
});
