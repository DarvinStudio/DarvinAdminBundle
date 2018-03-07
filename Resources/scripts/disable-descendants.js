$(function () {
    var selector = '.type_boolean.name_enabled input[type="checkbox"]:first';

    var init;
    (init = function () {
        $('.property_forms .section_table table').find('tr[data-level]').each(function () {
            var $row = $(this);

            var $checkbox = $row.find(selector),
                level     = $row.data('level');

            if (!$checkbox.length) {
                return;
            }
            if ($row.next('tr[data-level="' + (level + 1) + '"]').length) {
                $checkbox.data('reload-page', 1);
            }
            if (level < 2 || $checkbox.is(':checked')) {
                return;
            }

            var $parent = $row.prevAll('tr[data-level="' + (level - 1) + '"]:first').find(selector);

            $checkbox.removeAttr('disabled');

            if (!$parent.is(':checked') || $parent.is(':disabled')) {
                $checkbox.attr('disabled', 'disabled');
            }
        });
    })();
    $(document).on('ajaxSuccess', init);
});
