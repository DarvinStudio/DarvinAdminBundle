$(function () {
    var selector = '[data-master][data-show-on]';

    var isSlaveVisible = function ($master, showOn) {
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
    var toggleSlave = function ($slaveContainer, $master, showOn) {
        $master.val() && isSlaveVisible($master, showOn) ? $slaveContainer.show() : $slaveContainer.hide();

        if ($slaveContainer.is('option')) {
            var $options = $slaveContainer.closest('select').find('option' + selector);

            if ($options.index($slaveContainer) + 1 === $options.length) {
                $slaveContainer.closest('select').trigger('chosen:updated');
            }
        }
    };

    $(selector).each(function () {
        var $slave = $(this);

        var masterSelector  = $slave.data('master') + ':first',
            showOn          = $slave.data('show-on'),
            $slaveContainer = $slave.is('option') ? $slave : $slave.closest('.table_row');

        showOn = $.isArray(showOn)
            ? showOn.map(function (item) {
                return item.toString();
            })
            : showOn.toString();

        var $context = $slave.closest('[class*="_a2lix_translationsFields-"]');
        var $master = $context.find(masterSelector);

        if (!$master.length) {
            $context = $slave.closest('form');
            $master = $context.find(masterSelector);
        }

        toggleSlave($slaveContainer, $master, showOn);

        $context.on('change', masterSelector, function () {
            toggleSlave($slaveContainer, $(this), showOn);
        });
    });
});
