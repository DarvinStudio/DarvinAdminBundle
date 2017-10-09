Dropzone.autoDiscover = false;

$(document).ready(function () {
    $('.dropzone[data-files][data-url]').each(function () {
        var $dropzone = $(this);

        var $files = $dropzone.parents('form').first().find($dropzone.data('files') + '[data-prototype]');
        var filePrototype = $files.data('prototype');

        var defaultMessage = Translator.trans('dropzone.default_message');

        if ($dropzone.data('description')) {
            defaultMessage += '<br><br><b>' + Translator.trans($dropzone.data('description')) + '</b>';
        }

        var dropzone = new Dropzone('#' + $dropzone.attr('id'), {
            acceptedFiles:       $dropzone.data('accepted-files'),
            addRemoveLinks:      true,
            dictDefaultMessage:  defaultMessage,
            dictFileTooBig:      Translator.trans('dropzone.file_too_big'),
            dictInvalidFileType: Translator.trans('dropzone.invalid_file_type'),
            dictRemoveFile:      Translator.trans('dropzone.remove_file'),
            filesizeBase:        1024,
            maxFilesize:         $dropzone.data('max-filesize'),
            url:                 $dropzone.data('url')
        });

        dropzone
            .on('success', function (file, response) {
                var $file = $(filePrototype.replace(/__name__/g, $files.children().length));

                $file.find('.filename').val(response[0]);
                $file.find('.original_filename').val(file.name);

                $files.append($file);
            })
            .on('removedfile', function (file) {
                $files.find('.original_filename[value="' + file.name + '"]').parents('.table_row:first').remove();
            });
    });
});
