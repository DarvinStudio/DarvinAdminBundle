$(document).ready(function () {
    var buttons = {
        add:      '<button class="collection_add" type="button">' + Translator.trans('form_collections.add') + '</button>',
        'delete': '<button class="collection_delete" type="button">' + Translator.trans('form_collections.delete') + '</button>'
    };

    var $collections = $('form .collection:not([data-autoinit="0"])');

    $collections.each(function () {
        var $collection = $(this);

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
            var $collection = $addButton.parent('.collection[data-prototype]');
            var newElement = $collection.data('prototype').replace(/__name__label__|__name__/g, $collection.children().length);
            var $newElement = $(newElement);

            if ('undefined' !== typeof $collection.attr('data-allow-delete')) {
                $newElement.append(buttons.delete);
            }

            $addButton.before($newElement);
        })
        .on('click', 'form .collection .collection_delete', function () {
            $(this).parents('.row:first').remove();
        });
});
