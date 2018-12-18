$(() => {
    $.extend($.colorbox.settings, {
        maxWidth:  '90%',
        opacity:   0.7,
        scrolling: false,
        trapFocus: false
    });

    const TRANSLATABLE = [
        'current',
        'previous',
        'next',
        'close',
        'xhrError',
        'imgError',
        'slideshowStart',
        'slideshowStop'
    ];

    let LOCALE = {};

    for (let i in TRANSLATABLE) {
        LOCALE[TRANSLATABLE[i]] = Translator.trans('colorbox.' + TRANSLATABLE[i]);
    }

    $('body')
        .on('click', 'a.js-link[href]', (e) => {
            e.preventDefault();

            let $link = $(e.currentTarget);

            let url = $link.attr('href');

            if (!url || '#' === url) {
                return;
            }

            $.colorbox($.extend(LOCALE, {
                href:  url,
                title: $link.attr('title') || $link.data('title')
            }));
        })
        .find('.colorbox').colorbox(LOCALE);
});
