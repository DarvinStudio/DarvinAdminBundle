Dropzone.autoDiscover = false;

$(document).on('app.html', (e, args) => {
    args.$html.find('.dropzone[data-files][data-url]').each((i, dropzone) => {
        let $dropzone = $(dropzone);

        let options = $dropzone.data();

        let $files = $dropzone.closest('form').find('#' + options.files + '[data-prototype]');

        let filePrototype = $files.data('prototype');

        let defaultMessage = Translator.trans('dropzone.default_message');

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
            accept:              (file, done) => {
                file.acceptDimensions = done;
                file.rejectDimensions = () => {
                    done(Translator.trans('dropzone.reject_dimensions', {
                        max_width:  options.constraintMaxWidth,
                        max_height: options.constraintMaxHeight
                    }));
                };
            },
            // Must be "function"!
            init: function () {
                this
                    .on('removedfile', (file) => {
                        $files.find('.original_filename[value="' + file.name + '"]').parents('.table_row:first').remove();
                    })
                    .on('success', (file, response) => {
                        let $file = $(filePrototype.replace(/__name__/g, $files.children().length));

                        $file.find('.filename').val(response[0]);
                        $file.find('.original_filename').val(file.name);

                        $files.append($file);
                    })
                    .on('thumbnail', (file) => {
                        let interval = setInterval(() => {
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
