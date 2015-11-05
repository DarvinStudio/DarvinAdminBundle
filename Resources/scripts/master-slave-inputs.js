$(document).ready(function () {
    $('.slave_input').each(function () {
        var $slave = $(this);
        var $slaveContainer = $slave.parents('.table_row').first();
        var masterSelector = $slave.data('master');
        var showOn = $slave.data('show-on').toString();

        $slave.parents('form').first()
            .on('change', masterSelector, function () {
                $(this).val().toString() === showOn ? $slaveContainer.show() : $slaveContainer.hide();
            })
            .find(masterSelector).trigger('click');
    });
});
