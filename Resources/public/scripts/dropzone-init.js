$(document).ready(function () {
    Dropzone.autoDiscover = false;

    $('.dropzone[data-files][data-url]').each(function () {
        var $dropzone = $(this);

        var $files = $dropzone.parents('form').first().find($dropzone.data('files') + '[data-prototype]');
        var filePrototype = $files.data('prototype');

        $dropzone.dropzone({
            url:     $dropzone.data('url'),
            success: function (file, response) {
                var $file = $(filePrototype.replace(/__name__/g, $files.children().length));

                $file.find('.filename').val(response[0]);
                $file.find('.original_filename').val(file.name);

                $files.append($file);
            },
            acceptedFiles:      $dropzone.data('accepted-files'),
            dictDefaultMessage: Translator.trans('dropzone.default_message'),
            dictFileTooBig:     Translator.trans('dropzone.file_too_big'),
            filesizeBase:       1024,
            maxFilesize:        $dropzone.data('max-filesize')
        });
    });
});
