$(() => {
    const CLASSES = {
        'add':    'js-collection-add',
        'delete': 'js-collection-delete'
    };

    let BUTTONS   = {},
        SELECTORS = {
            collection: '.js-collection[data-prototype]'
        };

    for (let name in CLASSES) {
        BUTTONS[name] = '<button class="' + CLASSES[name] + '" type="button">' + Translator.trans('collection.' + name) + '</button>';

        SELECTORS[name] = SELECTORS.collection + ' .' + CLASSES[name];
    }

    const updateLabels = ($collection) => {
        if ($collection.data('allow-add')) {
            $collection.children().each((i, item) => {
                $(item).children('label:first').text(i + 1);
            });
        }
    };

    $(document).on('app.html', (e, args) => {
        args.$html.find(SELECTORS.collection + ':not([data-autoinit="0"])').each((i, collection) => {
            let $collection = $(collection);

            if ($collection.data('allow-delete')) {
                $collection.children().each((i, item) => {
                    $(item).append(buttons.delete);
                });
            }
            if ($collection.data('allow-add')) {
                updateLabels($collection);

                $collection.append(BUTTONS.add);
            }
        });
    });

    $('body')
        .on('click', 'form ' + SELECTORS.delete, (e) => {
            let $button = $(e.currentTarget);

            // Fetch collection node before (!) item removal
            let $collection = $button.closest(SELECTORS.collection);

            $button.closest('div').remove();

            updateLabels($collection);
        })
        .on('click', 'form ' + SELECTORS.add, (e) => {
            let $button = $(e.currentTarget);

            let $collection = $button.closest(SELECTORS.collection);

            let index = $collection.data('index'),
                name  = $collection.data('name') || '';

            let item = $collection.data('prototype')
                .replace(new RegExp(name + '___name__', 'g'), name + '_' + index)
                .replace(new RegExp('\\[' + name + '\\]\\[__name__\\]', 'g'), '[' + name + '][' + index + ']');

            let $item = $(item);

            if ($collection.data('allow-delete')) {
                $item.append(BUTTONS.delete);
            }

            $button.before($item);

            updateLabels($collection);

            $(document).trigger('app.html', {
                $html: $item
            });

            $collection.data('index', index + 1);
        });
});
