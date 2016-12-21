$(document).ready(function () {
    var showSlave = function ($master, showOn) {
        if ($master.is(':checkbox')) {
            return (+$master.is(':checked')).toString() === showOn;
        }

        var value = $master.val().toString();

        if ($master.is('select') && $master.prop('multiple')) {
            return value.indexOf(showOn) >= 0;
        }

        return value === showOn;
    };
    var toggleSlaveContainer = function ($slaveContainer, $master, showOn) {
        $master.val() && showSlave($master, showOn) ? $slaveContainer.show() : $slaveContainer.hide();

        if ($slaveContainer.is('option')) {
            $slaveContainer.closest('select').trigger('chosen:updated');
        }
    };

    $('.slave_input').each(function () {
        var $slave = $(this);

        var $slaveContainer = $slave.is('option') ? $slave : $slave.closest('.table_row');

        var masterSelector = $slave.data('master');
        var showOn = $slave.data('show-on').toString();

        var $context = $slave.closest('[class*="_a2lix_translationsFields-"]');
        var $master = $context.find(masterSelector).first();

        if (!$master.length) {
            $context = $slave.closest('form');
            $master = $context.find(masterSelector).first();
        }

        toggleSlaveContainer($slaveContainer, $master, showOn);

        $context.on('change', masterSelector, function () {
            toggleSlaveContainer($slaveContainer, $(this), showOn);
        });
    });
});
