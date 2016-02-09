$(document).ready(function () {
    var buttons = {
        add:      '<button class="collection_add" type="button">' + Translator.trans('form_collections.add') + '</button>',
        'delete': '<button class="collection_delete" type="button">' + Translator.trans('form_collections.delete') + '</button>'
    };

    var $collections = $('form .collection:not([data-autoinit="0"])');

    $collections.each(function () {
        var $collection = $(this);
        $collection.data('index', $collection.children().length);

        if ('undefined' !== typeof $collection.attr('data-allow-delete')) {
            $collection.children().each(function () {
                $(this).append(buttons.delete);
            });
        }
        if ('undefined' !== typeof $collection.attr('data-allow-add')) {
            $collection.append(buttons.add);
        }
    });

    $('body')
        .on('click', 'form .collection[data-prototype] .collection_add', function () {
            var $addButton = $(this);
            var $collection = $addButton.parents('.collection[data-prototype]').first();
            var newElement = $collection.data('prototype').replace(/__name__label__|__name__/g, $collection.data('index'));
            var $newElement = $(newElement);

            if ('undefined' !== typeof $collection.attr('data-allow-delete')) {
                $newElement.append(buttons.delete);
            }

            $addButton.before($newElement);

            $(document).trigger('formCollectionAdd', $newElement);

            $collection.data('index', $collection.data('index') + 1);
        })
        .on('click', 'form .collection .collection_delete', function () {
            $(this).parents('.table_row:first').remove();
        });
});
