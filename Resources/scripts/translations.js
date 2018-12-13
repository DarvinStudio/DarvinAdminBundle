$(() => {
    const SELECTORS = {
        error:        '.js-form-error',
        input:        'input[type="text"]',
        tab:          '.js-translation-tab[data-locale]',
        toggle:       '.js-translation-toggle[data-target]',
        translations: '.js-translations'
    };

    $(document).on('app.html', (e, args) => {
        let clicked = false;

        args.$html.find(SELECTORS.toggle).each((i, toggle) => {
            if (clicked) {
                return;
            }

            let $toggle = $(toggle);

            if ($toggle.closest(SELECTORS.translations).find($toggle.data('target') + ' ' + SELECTORS.error + ':first').length) {
                $toggle.trigger('click');

                clicked = true;
            }
        });
    });

    let $submittedForm = null;

    $('body')
        .on('click', 'form [type="submit"]', (e) => {
            $submittedForm = $(e.currentTarget).closest('form');
        })
        .on('click', 'form ' + SELECTORS.toggle, (e) => {
            $(e.currentTarget).closest(SELECTORS.translations).find(SELECTORS.input).removeData('synced');
        })
        .on('change', 'form ' + SELECTORS.tab + '.active ' + SELECTORS.input, (e) => {
            let $sourceInput = $(e.currentTarget);

            $sourceInput.removeData('synced');

            let $row = $sourceInput.closest('.table_row');

            let $sourceTab = $row.closest(SELECTORS.tab),
                rowIndex   = $row.index(),
                sourceText = $sourceInput.val();

            let $form       = $sourceTab.closest('form'),
                $targetTabs = $sourceTab.siblings(SELECTORS.tab);

            $targetTabs.each((i, targetTab) => {
                let $targetTab = $(targetTab);

                let $targetInput = $targetTab.find('.table_row').eq(rowIndex).find(SELECTORS.input);

                if ('' !== $targetInput.val() && !$targetInput.data('synced')) {
                    return;
                }

                YandexTranslator.translate(sourceText, $sourceTab.data('locale'), $targetTab.data('locale'), (translated) => {
                    $targetInput.val(translated).data('synced', true);

                    if (i === $targetTabs.length - 1 && $form.is($submittedForm)) {
                        $form.trigger('submit');
                    }
                });
            });
        });
});
