$(document).ready(function () {
    $.extend($.colorbox.settings, {
        maxWidth:  '90%',
        opacity:   0.7,
        scrolling: false,
        trapFocus: false
    });

    var translatable = [
        'current',
        'previous',
        'next',
        'close',
        'xhrError',
        'imgError',
        'slideshowStart',
        'slideshowStop'
    ];
    var locale = {};

    for (var i = 0; i < translatable.length; i++) {
        locale[translatable[i]] = Translator.trans('colorbox.' + translatable[i]);
    }

    var init;
    (init = function (context) {
        $(context)
            .on('click', 'a.colorbox_ajax[href]', function (e) {
                e.preventDefault();

                var $link = $(this);

                var url = $link.attr('href');

                if (!url || '#' === url) {
                    return;
                }

                $.colorbox($.extend({}, locale, {
                    href:  url,
                    title: $link.attr('title') || $link.data('title')
                }));
            })
            .find('.colorbox').colorbox(locale);
    })('body');
});
