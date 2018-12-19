(() => {
    class Slave {
        constructor(slave) {
            this.$slave = $(slave);

            this.$slaveContainer = this.$slave.is('option') ? this.$slave : this.$slave.closest('.table_row');
            this.masterSelector  = this.$slave.data('master') + ':first';
            this.showOn          = this.$slave.data('show-on');

            if ($.isArray(this.showOn) && this.showOn.length > 0) {
                this.showOn = this.showOn.map(function (item) {
                    return item.toString();
                });
            } else {
                this.showOn = this.showOn.toString();
            }

            let $context = this.$slave.closest('.js-translation-tab');

            let $master = $context.find(this.masterSelector);

            if (!$master.length) {
                $context = this.$slave.closest('form');

                $master = $context.find(this.masterSelector);
            }

            this.toggle($master);

            $context.on('change', this.masterSelector, (e) => {
                this.toggle(e.currentTarget);
            });
        }

        toggle(master) {
            let $master = $(master);

            $master.val() && this.isVisible($master) ? this.$slaveContainer.show() : this.$slaveContainer.hide();

            if (this.$slaveContainer.is('option')) {
                let $options = this.$slaveContainer.closest('select').find('option' + selector);

                if ($options.index(this.$slaveContainer) + 1 === $options.length) {
                    this.$slaveContainer.closest('select').trigger('chosen:updated');
                }
            }
        }

        isVisible($master) {
            if ($master.is(':checkbox')) {
                return (+$master.is(':checked')).toString() === this.showOn;
            }

            let value = $master.val().toString();

            if ($master.is('select') && $master.prop('multiple')) {
                if (!$.isArray(this.showOn)) {
                    return value.indexOf(this.showOn) >= 0;
                }
                for (let i in this.showOn) {
                    if (value.indexOf(this.showOn[i]) >= 0) {
                        return true;
                    }
                }

                return false;
            }

            return $.isArray(this.showOn) ? this.showOn.indexOf(value) >= 0 : value === this.showOn;
        }
    }

    $(document).on('app.html', (e, args) => {
        args.$html.find('[data-master][data-show-on]').each((i, slave) => {
            new Slave(slave);
        });
    });
})();
