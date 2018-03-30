$(function () {
    var buttons = {
        'add':    '<button class="collection_add" type="button">' + Translator.trans('form_collections.add') + '</button>',
        'delete': '<button class="collection_delete" type="button">' + Translator.trans('form_collections.delete') + '</button>'
    };

    var updateLabels = function ($collection) {
        if ($collection.data('allow-add')) {
            $collection.children().each(function (index) {
                $(this).children('label:first').text(index + 1);
            });
        }
    };

    var init;
    (init = function (context) {
        $(context || 'body').find('.collection[data-prototype]:not([data-autoinit="0"])').each(function () {
            var $collection = $(this);

            if ($collection.data('allow-delete')) {
                $collection.children().each(function () {
                    $(this).append(buttons.delete);
                });
            }
            if ($collection.data('allow-add')) {
                updateLabels($collection);

                $collection.append(buttons.add);
            }
        });
    })();
    $(document).on('formCollectionAdd', function (e, item) {
        init(item);
    });

    $('body')
        .on('click', 'form .collection .collection_delete', function () {
            var $button = $(this);

            // Fetch collection node before (!) item removal
            var $collection = $button.closest('.collection[data-prototype]');

            $button.closest('.collection_item').remove();

            updateLabels($collection);
        })
        .on('click', 'form .collection[data-prototype] .collection_add', function () {
            var $button = $(this);

            var $collection = $button.closest('.collection[data-prototype]');

            var index = $collection.data('index'),
                name  = $collection.data('name') || '';

            var item = $collection.data('prototype')
                .replace(new RegExp(name + '___name__', 'g'), name + '_' + index)
                .replace(new RegExp('\\[' + name + '\\]\\[__name__\\]', 'g'), '[' + name + '][' + index + ']');

            var $item = $(item).addClass('collection_item');

            if ($collection.data('allow-delete')) {
                $item.append(buttons.delete);
            }

            $button.before($item);

            updateLabels($collection);

            $(document).trigger('formCollectionAdd', $item);

            $collection.data('index', index + 1);
        });
});
