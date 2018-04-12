$(document).ready(function () {
    var getLocale = function ($tab) {
        var matches = $tab.attr('class').match(/a2lix_translationsfields-([a-z]+)\s*/i);

        return matches[1];
    };

    $('body')
        .on('change', 'form .a2lix_translationsFields .tab-pane.active input[type="text"][required="required"]', function () {
            var $sourceInput = $(this);

            $sourceInput.removeData('synced');

            var $row = $sourceInput.parents('.table_row').first();
            var $sourceTab = $row.parents('.tab-pane').first();

            var rowIndex = $row.index();

            var sourceText = $sourceInput.val();
            var sourceLocale = getLocale($sourceTab);

            $sourceTab.siblings('.tab-pane').each(function () {
                var $targetTab = $(this);

                var $targetInput = $targetTab.find('.table_row').eq(rowIndex).find('input[type="text"]');

                if ('' !== $targetInput.val() && !$targetInput.data('synced')) {
                    return;
                }
                if ('' === sourceText) {
                    $targetInput.val(sourceText);

                    return;
                }

                YandexTranslator.translate(sourceText, sourceLocale, getLocale($targetTab), function (translated) {
                    $targetInput.val(translated).data('synced', true);
                });
            });
        })
        .on('click', 'form .a2lix_translationsLocales.nav.nav-tabs a', function () {
            $(this).parents('.a2lix_translations').first().find('input[type="text"][required="required"]').removeData('synced');
        });
});
