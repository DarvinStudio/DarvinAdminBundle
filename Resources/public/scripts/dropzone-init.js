$(document).ready(function () {
    Dropzone.autoDiscover = false;

    $('.dropzone[data-files][data-url]').each(function () {
        var $dropzone = $(this);

        var $files = $dropzone.parents('form').first().find($dropzone.data('files') + '[data-prototype]');
        var filePrototype = $files.data('prototype');

        $dropzone.dropzone({
            success: function (file, response) {
                var $file = $(filePrototype.replace(/__name__/g, $files.children().length));

                $file.find('.filename').val(response[0]);
                $file.find('.original_filename').val(file.name);

                $files.append($file);
            },
            url: $dropzone.data('url')
        });
    });
});
