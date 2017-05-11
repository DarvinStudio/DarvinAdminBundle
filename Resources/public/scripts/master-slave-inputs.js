$(document).ready(function () {
    var showSlave = function ($master, showOn) {
        if ($master.is(':checkbox')) {
            return (+$master.is(':checked')).toString() === showOn;
        }

        var value = $master.val().toString();

        if ($master.is('select') && $master.prop('multiple')) {
            if (!$.isArray(showOn)) {
                return value.indexOf(showOn) >= 0;
            }
            for (var i = 0; i < showOn.length; i++) {
                if (value.indexOf(showOn[i]) >= 0) {
                    return true;
                }
            }

            return false;
        }

        return $.isArray(showOn) ? showOn.indexOf(value) >= 0 : value === showOn;
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

        var showOn = $slave.data('show-on');
        showOn = $.isArray(showOn)
            ? showOn.map(function (item) {
                return item.toString();
            })
            : showOn.toString();

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
