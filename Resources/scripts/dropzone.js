Dropzone.autoDiscover = false;

$(function () {
    $('.dropzone[data-files][data-url]').each(function () {
        var $dropzone = $(this);

        var options = $dropzone.data();

        var $files = $dropzone.parents('form').first().find(options.files + '[data-prototype]');
        var filePrototype = $files.data('prototype');

        var defaultMessage = Translator.trans('dropzone.default_message');

        if (options.description) {
            defaultMessage += '<br><br><b>' + Translator.trans(options.description) + '</b>';
        }

        new Dropzone('#' + $dropzone.attr('id'), {
            acceptedFiles:       options.acceptedFiles,
            addRemoveLinks:      true,
            dictDefaultMessage:  defaultMessage,
            dictFileTooBig:      Translator.trans('dropzone.file_too_big'),
            dictInvalidFileType: Translator.trans('dropzone.invalid_file_type'),
            dictRemoveFile:      Translator.trans('dropzone.remove_file'),
            filesizeBase:        1024,
            maxFilesize:         options.maxFilesize,
            url:                 options.url,
            accept:              function (file, done) {
                file.acceptDimensions = done;
                file.rejectDimensions = function () {
                    done(Translator.trans('dropzone.reject_dimensions', {
                        max_width:  options.constraintMaxWidth,
                        max_height: options.constraintMaxHeight
                    }));
                };
            },
            init: function () {
                this
                    .on('removedfile', function (file) {
                        $files.find('.original_filename[value="' + file.name + '"]').parents('.table_row:first').remove();
                    })
                    .on('success', function (file, response) {
                        var $file = $(filePrototype.replace(/__name__/g, $files.children().length));

                        $file.find('.filename').val(response[0]);
                        $file.find('.original_filename').val(file.name);

                        $files.append($file);
                    })
                    .on('thumbnail', function (file) {
                        var interval = setInterval(function () {
                            if ('undefined' === typeof file.acceptDimensions || 'undefined' === typeof file.rejectDimensions) {
                                return;
                            }

                            clearInterval(interval);

                            if (file.width > options.constraintMaxWidth || file.height > options.constraintMaxHeight) {
                                file.rejectDimensions();

                                return;
                            }

                            file.acceptDimensions();
                        }, 100);
                    });
            }
        });
    });
});
