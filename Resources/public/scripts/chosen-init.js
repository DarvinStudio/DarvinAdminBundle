$(document).ready(function () {
    var SCROLL_SPEED = -1 !== navigator.userAgent.indexOf('Firefox') ? 10 : 1;

    var searchResultsMousewheel = function (e) {
        var delta;

        if (e.originalEvent) {
            delta = e.originalEvent.deltaY || -e.originalEvent.wheelDelta || e.originalEvent.detail;
        }
        if (delta == null) {
            return null;
        }

        e.preventDefault();

        return this.search_results.scrollTop(delta * (e.type === 'DOMMouseScroll' ? 40 : SCROLL_SPEED) + this.search_results.scrollTop());
    };

    var init;
    (init = function (context) {
        $(context || 'body').find('select:visible').each(function () {
            $(this)
                .chosen({
                    allow_single_deselect:     true,
                    no_results_text:           Translator.trans('chosen.no_results_text'),
                    placeholder_text_multiple: Translator.trans('chosen.placeholder_text_multiple'),
                    placeholder_text_single:   Translator.trans('chosen.placeholder_text_single'),
                    search_contains:           true
                })
                .change(function () {
                    $(this).trigger('chosen:updated');
                })
                .data('chosen').search_results_mousewheel = searchResultsMousewheel;
        });
    })();

    $(document)
        .on('formCollectionAdd', function (e, form) {
            init(form);
        })
        .on('propertyFormSubmit', function (e, form) {
            init(form);
        })
        .on('spoilerOpen', function (e, spoiler) {
            init(spoiler);
        });
});
