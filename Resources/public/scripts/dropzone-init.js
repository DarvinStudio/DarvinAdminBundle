$(document).ready(function () {
    Dropzone.autoDiscover = false;

    $('.dropzone[data-filenames][data-url]').each(function () {
        var $dropzone = $(this);

        var $filenames = $dropzone.parents('form').first().find($dropzone.data('filenames') + '[data-prototype]');
        var filenamePrototype = $filenames.data('prototype');

        $dropzone.dropzone({
            success: function (file, response) {
                $filenames.append($(filenamePrototype.replace(/__name__/g, $filenames.children().length)).val(response[0]));
            },
            url: $dropzone.data('url')
        });
    });
});
