$(document).ready(function () {
    var getLocale = function ($tab) {
        return $tab.attr('class').replace(/(a2lix_translationsFields-|tab-pane|active)/g, '').trim();
    };

    $('body')
        .on('change', 'form .a2lix_translationsFields .tab-pane.active input[type="text"][required="required"]', function () {
            var $sourceInput = $(this);

            $sourceInput.removeData('synced');

            var $row = $sourceInput.parents('.row').first();
            var $sourceTab = $row.parents('.tab-pane').first();

            var rowIndex = $row.index();

            var sourceText = $sourceInput.val();
            var sourceLocale = getLocale($sourceTab);

            $sourceTab.siblings('.tab-pane').each(function () {
                var $targetTab = $(this);

                var $targetInput = $targetTab.find('.row').eq(rowIndex).find('input[type="text"]');

                if ('' !== $targetInput.val() && !$targetInput.data('synced')) {
                    return;
                }

                var translated = YandexTranslator.translate(sourceText, sourceLocale, getLocale($targetTab));

                $targetInput.val(translated).data('synced', true);
            });
        })
        .on('click', 'form .a2lix_translationsLocales.nav.nav-tabs a', function () {
            $(this).parents('.a2lix_translations').first().find('input[type="text"][required="required"]').removeData('synced');
        });
});
