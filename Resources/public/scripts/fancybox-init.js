$(document).ready(function () {
    $.fancybox.defaults.locales = $.extend({}, $.fancybox.defaults.locales, {
        ru: {
            CLOSE:      Translator.trans('fancybox.close'),
            NEXT:       Translator.trans('fancybox.next'),
            PREV:       Translator.trans('fancybox.prev'),
            ERROR:      Translator.trans('fancybox.error'),
            EXPAND:     Translator.trans('fancybox.expand'),
            SHRINK:     Translator.trans('fancybox.shrink'),
            PLAY_START: Translator.trans('fancybox.play_start'),
            PLAY_STOP:  Translator.trans('fancybox.play_stop')
        }
    });
    $.fancybox.defaults.tpl = $.extend({}, $.fancybox.defaults.tpl, {
        closeBtn: '<a title="{{CLOSE}}" class="overlay_close" href="javascript:;"></a>'
    });

    var init;
    (init = function (context) {
        if ('undefined' === typeof context) {
            context = 'body';
        }

        $(context).find('.fancybox').fancybox();

        $(context).on('click', 'a.fancybox_ajax', function (e) {
            e.preventDefault();

            var $link = $(this);

            if ($link.data('submitted')) {
                return;
            }

            $link
                .append(AJAX_LOADER)
                .data('submitted', true);

            $.ajax({
                type: 'get',
                url:  $link.attr('href')
            }).done(function (html) {
                $.fancybox({
                    content: html,
                    title:   $link.attr('title')
                });
            }).error(onAjaxError);
        });
    })();

    $(document).on('searchComplete', function (e, results) {
        init(results);
    });
});
