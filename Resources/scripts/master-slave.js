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

        if (!$slaveContainer.is('option')) {
            return;
        }

        var $select = $slaveContainer.closest('select');

        var selectCustom = $select.data('custom');

        if ('undefined' !== typeof selectCustom && !selectCustom) {
            return;
        }

        var $options = $select.find('option' + selector);

        if ($options.index($slaveContainer) + 1 === $options.length) {
            $select.trigger('chosen:updated');
        }
    };
    var init;
    (init = function (context) {
        var slavesByMasters = {};

        $(context || 'body').find(selector).each(function () {
            var $slave = $(this);

            var masterSelector  = $slave.data('master') + '[id]:first',
                showOn          = $slave.data('show-on'),
                $slaveContainer = $slave.is('option') ? $slave : $slave.closest('.table_row');

            showOn = $.isArray(showOn)
                ? showOn.map(function (item) {
                    return item.toString();
                })
                : showOn.toString();

            var $master = $slave.closest('[class*="_a2lix_translationsFields-"]').find(masterSelector);

            if (!$master.length) {
                $master = $slave.closest('form').find(masterSelector);
            }
            if (!$master.length) {
                return;
            }

            var masterId = $master.attr('id');

            if ('undefined' === typeof slavesByMasters[masterId]) {
                slavesByMasters[masterId] = [];
            }

            slavesByMasters[masterId].push({
                $container: $slaveContainer,
                showOn:     showOn
            });
        });

        for (var masterId in slavesByMasters) {
            var $master = $('#' + masterId),
                slaves  = slavesByMasters[masterId];

            for (var i = 0; i < slaves.length; i++) {
                toggleSlave(slaves[i].$container, $master, slaves[i].showOn);
            }

            $master.change(function () {
                for (var i = 0; i < slaves.length; i++) {
                    toggleSlave(slaves[i].$container, $master, slaves[i].showOn);
                }
            });
        }
    })();

    $(document)
        .on('app.html', function (e, args) {
            init(args.$html);
        })
        .on('formCollectionAdd', function (e, $item) {
            init($item);
        });
});
