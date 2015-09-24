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

    $('body').on('click', 'a.fancybox_ajax', function (e) {
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
                caption: {
                    type: 'inside'
                },
                content: html,
                title:   $link.attr('title')
            });
        }).error(onAjaxError);
    });
});
