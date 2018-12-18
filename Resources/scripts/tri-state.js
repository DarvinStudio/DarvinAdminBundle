(() => {
    class TriState {
        constructor(checkbox) {
            this.CLASSES = [
                '',
                'js-tri-state-checked',
                'js-tri-state-unchecked'
            ];

            this.$checkbox = $(checkbox);

            this.$checkbox.addClass('js-tri-state-ready')
                .find(':not(input)').remove();

            this.check(this.$checkbox.find('input:checked').index());

            this.$checkbox.click(() => {
                let index = this.$checkbox.data('index');

                this.uncheck(index);

                index++;

                this.check(index);
            });
        }

        check(index) {
            if (index < 0 || index > 2) {
                index = 0;
            }

            this.$checkbox
                .data('index', index).addClass(this.CLASSES[index])
                .find('input')[index].checked = true;
        }

        uncheck(index) {
            this.$checkbox.removeClass(this.CLASSES[index])
                .find('input')[index].checked = false;
        }
    }

    $(document).on('app.html', (e, args) => {
        $(args.$html).find('.js-tri-state').each((i, checkbox) => {
            new TriState(checkbox);
        });
    });
})();
