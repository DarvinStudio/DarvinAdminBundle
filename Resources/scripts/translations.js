(() => {
    const SELECTORS = {
        errors:       '.js-form-errors',
        input:        'input[type="text"]',
        tab:          '.js-translation-tab[data-locale]',
        toggle:       '.js-translation-toggle[data-target]',
        translations: '.js-translations'
    };

    $(document).on(App.events.ajax.html, (e, args) => {
        let clicked = false;

        args.$html.find(SELECTORS.toggle).each((i, toggle) => {
            if (clicked) {
                return;
            }

            let $toggle = $(toggle);

            if ($toggle.closest(SELECTORS.translations).find($toggle.data('target') + ' ' + SELECTORS.errors + ':first').length) {
                $toggle.trigger('click');

                clicked = true;
            }
        });
    });

    $('body')
        .on('click', 'form ' + SELECTORS.toggle, (e) => {
            $(e.currentTarget).closest(SELECTORS.translations).find(SELECTORS.input).removeData('synced');
        })
        .on('change', 'form ' + SELECTORS.tab + '.is-active ' + SELECTORS.input, (e) => {
            let $sourceInput = $(e.currentTarget);

            $sourceInput.removeData('synced');

            let $row = $sourceInput.closest('.form-item');

            let $sourceTab = $row.closest(SELECTORS.tab),
                rowIndex   = $row.index(),
                sourceText = $sourceInput.val();

            $sourceTab.siblings(SELECTORS.tab).each((i, targetTab) => {
                let $targetTab = $(targetTab);

                let $targetInput = $targetTab.find('.form-item').eq(rowIndex).find(SELECTORS.input);

                if ('' !== $targetInput.val() && !$targetInput.data('synced')) {
                    return;
                }

                YandexTranslator.translate(sourceText, $sourceTab.data('locale'), $targetTab.data('locale'), (translated) => {
                    $targetInput.val(translated).data('synced', true);
                });
            });
        });
})();
