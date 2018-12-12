$(() => {
    const SCROLL_SPEED = -1 !== navigator.userAgent.indexOf('Firefox') ? 10 : 1;
    const OPTIONS      = {
        allow_single_deselect:     true,
        no_results_text:           Translator.trans('chosen.no_results_text'),
        placeholder_text_multiple: Translator.trans('chosen.placeholder_text_multiple'),
        placeholder_text_single:   Translator.trans('chosen.placeholder_text_single'),
        search_contains:           true
    };

    // It must be the "function" to get proper "this"!
    const searchResultsMousewheel = function (e) {
        let delta;

        if (e.originalEvent) {
            delta = e.originalEvent.deltaY || -e.originalEvent.wheelDelta || e.originalEvent.detail;
        }
        if (delta == null) {
            return null;
        }

        e.preventDefault();

        return this.search_results.scrollTop(delta * (e.type === 'DOMMouseScroll' ? 40 : SCROLL_SPEED) + this.search_results.scrollTop());
    };

    const init = (context) => {
        $(context).find('select:visible').each((i, select) => {
            $(select)
                .chosen(OPTIONS)
                .change((e) => {
                    $(e.currentTarget).trigger('chosen:updated');
                })
                .data('chosen').search_results_mousewheel = searchResultsMousewheel;
        });
    };

    $(document)
        .on('app.html', (e, args) => {
            init(args.$html);
        })
        .on('app.spoiler.open', (e, args) => {
            init(args.$spoiler);
        });
});
