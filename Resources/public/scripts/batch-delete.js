$(document).ready(function () {
    $('body')
        .on('change', 'input[type="checkbox"].batch_delete_check[data-id]', function () {
            var $checkAll = $('input[type="checkbox"].batch_delete_check_all');
            var $form = $('form.batch_delete_form');
            var $submit = $form.find('[type="submit"]:first');

            $form.find('input[type="checkbox"][value="' + $(this).data('id') + '"]')[0].checked = this.checked;

            if (!this.checked) {
                $checkAll[0].checked = false;

                if ($submit.is(':visible') && 0 === $('input[type="checkbox"].batch_delete_check[data-id]:checked').length) {
                    $submit.hide();
                }

                return;
            }

            var $checks = $('input[type="checkbox"].batch_delete_check[data-id]');

            $checkAll[0].checked = $checks.length === $checks.filter(':checked').length;
            $submit.show();
        })
        .on('change', 'input[type="checkbox"].batch_delete_check_all', function () {
            var checked = this.checked;

            $('input[type="checkbox"].batch_delete_check').each(function () {
                this.checked = checked;

                $(this).trigger('change');
            });
        });
});
