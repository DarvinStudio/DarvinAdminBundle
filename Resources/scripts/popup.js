(() => {
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

    $(document).on('app.html', (e, args) => {
        let options = {
            maxWidth:  '90%',
            opacity:   0.7,
            scrolling: false,
            trapFocus: false
        };

        for (let i in TRANSLATABLE) {
            options[TRANSLATABLE[i]] = Translator.trans('colorbox.' + TRANSLATABLE[i]);
        }

        args.$html.find('.js-popup').colorbox(options);
    });
})();
