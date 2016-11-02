$(document).ready(function () {
    var showSlave = function ($master, showOn) {
        var value = $master.val().toString();

        if ($master.is('select') && $master.prop('multiple')) {
            return value.indexOf(showOn) >= 0;
        }

        return value === showOn;
    };
    var toggleSlaveContainer = function ($slaveContainer, $master, showOn) {
        $master.val() && showSlave($master, showOn) ? $slaveContainer.show() : $slaveContainer.hide();
    };

    $('.slave_input').each(function () {
        var $slave = $(this);
        var $slaveContainer = $slave.parents('.table_row').first();
        var masterSelector = $slave.data('master');
        var showOn = $slave.data('show-on').toString();

        var $form = $slave.parents('form').first();

        toggleSlaveContainer($slaveContainer, $form.find(masterSelector), showOn);

        $form.on('change', masterSelector, function () {
            toggleSlaveContainer($slaveContainer, $(this), showOn);
        });
    });
});
