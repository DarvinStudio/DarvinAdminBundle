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

        $.ajax({
            type: 'get',
            url:  $(this).attr('href')
        }).done(function (html) {
            $.fancybox({
                content: html
            });
        }).error(onAjaxError);
    });
});
