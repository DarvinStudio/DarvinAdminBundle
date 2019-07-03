$(() => {
    let $searchables = $('.js-searchable[data-source]');

    if (!$searchables.length) {
        return;
    }

    App.startPreloading('search');

    let last    = $searchables.length - 1,
        pending = false;

    $searchables.each((i, searchable) => {
        let $searchable = $(searchable);

        let $results = $searchable.find('.js-searchable-results');

        let interval = setInterval(() => {
            if (pending) {
                return;
            }

            pending = true;

            $searchable.show();

            $.ajax({
                url: $searchable.data('source')
            }).done((html) => {
                let $html = $(html);

                if (!$html.find('tr').length) {
                    $searchable.remove();

                    return;
                }

                $results.html(html);

                $(document).trigger(App.events.ajax.html, {
                    $html: $results
                });
            }).always(() => {
                clearInterval(interval);

                pending = false;

                if (i === last) {
                    App.stopPreloading('search');
                }
            }).fail(App.onAjaxFail);
        }, 100);
    });
});
