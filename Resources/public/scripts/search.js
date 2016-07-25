$(document).ready(function () {
    var $results = $('.search_results[data-source]');

    if (!$results.length) {
        return;
    }

    var pending = false;

    $results.each(function () {
        var $results = $(this);

        var interval = setInterval(function () {
            if (pending) {
                return;
            }

            pending = true;
            $results.html(AJAX_LOADER);

            $.ajax({
                url: $results.data('source')
            }).done(function (html) {
                $results.html(html);
                $(document).trigger('searchComplete', $results);
            }).complete(function () {
                clearInterval(interval);
                pending = false;
            }).error(onAjaxError);
        }, 100);
    });
});
