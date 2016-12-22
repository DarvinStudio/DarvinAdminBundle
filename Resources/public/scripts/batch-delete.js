$(document).ready(function () {
    $('body')
        .on('change', 'input[type="checkbox"].batch_delete_check[data-id]', function () {
            $('form.batch_delete_form input[type="checkbox"][value="' + $(this).data('id') + '"]')[0].checked = this.checked;

            var $checkAll = $('input[type="checkbox"].batch_delete_check_all');

            if (!this.checked) {
                $checkAll[0].checked = false;

                return;
            }

            var $checks = $('input[type="checkbox"].batch_delete_check[data-id]');
            $checkAll[0].checked = $checks.length === $checks.filter(':checked').length;
        })
        .on('change', 'input[type="checkbox"].batch_delete_check_all', function () {
            var checked = this.checked;

            $('input[type="checkbox"].batch_delete_check').each(function () {
                this.checked = checked;

                $(this).trigger('change');
            });
        });
});
