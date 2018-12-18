(() => {
    class Collection {
        constructor(collection) {
            this.$collection = $(collection);

            let classes = {
                'add':    'js-collection-add',
                'delete': 'js-collection-delete'
            };

            this.BUTTONS = {};

            for (let name in classes) {
                this.BUTTONS[name] = '<button class="' + classes[name] + '" type="button">' + Translator.trans('collection.' + name) + '</button>';
            }

            this
                .createAddButton()
                .createDeleteButtons(this.getItems())
                .updateLabels();
        }

        createAddButton() {
            if (!this.addAllowed()) {
                return this;
            }

            let $button = $(this.BUTTONS.add);

            $button.click(() => {
                let index = this.$collection.data('index'),
                    name  = this.$collection.data('name') || '';

                let $item = $(
                    this.$collection.data('prototype')
                        .replace(new RegExp(name + '___name__', 'g'), name + '_' + index)
                        .replace(new RegExp('\\[' + name + '\\]\\[__name__\\]', 'g'), '[' + name + '][' + index + ']')
                );

                $item.find('.js-collection[data-prototype][data-name]').each((i, collection) => {
                    $(collection).attr('data-name', index);
                });

                $button.before($item);

                this
                    .createDeleteButtons($item)
                    .updateLabels();

                this.$collection.data('index', index + 1);

                $(document).trigger('app.html', {
                    $html: $item
                });
            });

            this.$collection.append($button);

            return this;
        }

        createDeleteButtons(nodes) {
            if (!this.deleteAllowed()) {
                return this;

            }

            $(nodes).each((i, node) => {
                let $button = $(this.BUTTONS.delete);

                $button.click(() => {
                    $button.closest('div').remove();

                    this.updateLabels();
                });

                $(node).append($button);
            });

            return this;
        }

        updateLabels() {
            if (this.addAllowed()) {
                this.getItems().each((i, item) => {
                    $(item).children('label:first').text(i + 1);
                });
            }

            return this;
        }

        addAllowed() {
            return this.$collection.data('allow-add');
        }

        deleteAllowed() {
            return this.$collection.data('allow-delete');
        }

        getItems() {
            return this.$collection.children().filter(':not(button)');
        }
    }

    $(document).on('app.html', (e, args) => {
        args.$html.find('.js-collection[data-prototype]').each((i, node) => {
            let autoInit = $(node).data('autoinit');

            if ('undefined' === typeof autoInit || autoInit) {
                new Collection(node);
            }
        });
    });
})();
