// Dependencies
const gulp         = require('gulp'),
      gulpSequence = require('gulp-sequence');

// Build directories
const dir = {
    dev:  'Resources/public/build-dev',
    prod: 'Resources/public/build'
};

// Assets
const scripts = [
        {
            target: 'app.js',
            src:    [
                'Resources/public/node_modules/bootstrap/dist/js/bootstrap.js',
                'Resources/public/node_modules/chosen-js/chosen.jquery.js',
                'Resources/public/node_modules/dropzone/dist/dropzone.js',
                'Resources/public/node_modules/jquery-colorbox/jquery.colorbox.js',
                'Resources/public/node_modules/jquery-mousewheel/jquery.mousewheel.js',
                'Resources/public/node_modules/jquery-text-counter/textcounter.js',
                'Resources/public/node_modules/components-jqueryui/jquery-ui.js',
                'Resources/public/node_modules/components-jqueryui/ui/i18n/datepicker-ru.js',
                'Resources/public/node_modules/jquery.cookie/jquery.cookie.js',
                'Resources/public/node_modules/jquery-ui-timepicker-addon/dist/jquery-ui-timepicker-addon.js',
                'Resources/public/node_modules/jquery-ui-timepicker-addon/dist/i18n/jquery-ui-timepicker-ru.js',
                'Resources/public/node_modules/noty/lib/noty.js',
                'Resources/public/node_modules/sly-scrolling/dist/sly.js',

                '../../../vendor/a2lix/translation-form-bundle/Resources/public/js/a2lix_translation_default.js',
                '../../../vendor/willdurand/js-translation-bundle/Resources/public/js/translator.min.js',

                'Resources/scripts/globals.js',
                'Resources/scripts/yandex-translator.js',

                'Resources/scripts/ajax-form.js',
                'Resources/scripts/batch-delete.js',
                'Resources/scripts/chosen.js',
                'Resources/scripts/colorbox.js',
                'Resources/scripts/configuration.js',
                'Resources/scripts/date-time-picker.js',
                'Resources/scripts/disable-descendants.js',
                'Resources/scripts/dropzone.js',
                'Resources/scripts/form-collection.js',
                'Resources/scripts/image.js',
                'Resources/scripts/master-slave.js',
                'Resources/scripts/property-form.js',
                'Resources/scripts/search.js',
                'Resources/scripts/slug-suffix.js',
                'Resources/scripts/table-head-clone.js',
                'Resources/scripts/textcounter.js',
                'Resources/scripts/translation-sync.js',
                'Resources/scripts/tri-state-checkbox.js',

                'Resources/scripts/app.js'
            ]
        }
    ],
    styles = [
        {
            target: 'app.css',
            src:    [
                'Resources/public/node_modules/bootstrap/dist/css/bootstrap.css',
                'Resources/public/node_modules/bootstrap-chosen/bootstrap-chosen.css',
                'Resources/public/node_modules/dropzone/dist/dropzone.css',
                'Resources/public/node_modules/jquery-colorbox/example4/colorbox.css',
                'Resources/public/node_modules/components-jqueryui/themes/smoothness/jquery-ui.css',
                'Resources/public/node_modules/jquery-ui-timepicker-addon/dist/jquery-ui-timepicker-addon.css',
                'Resources/public/node_modules/noty/lib/noty.css',
                'Resources/public/node_modules/noty/lib/themes/bootstrap-v3.css'
            ]
        }
    ];

// Tasks
gulp
    // Common
    .task('vendor', require('./gulp/tasks/vendor')(gulp))
    // Dev
    .task('scripts', require('./gulp/tasks/scripts')(gulp, dir, scripts))
    .task('styles', require('./gulp/tasks/styles')(gulp, dir, styles))
    .task('build', gulpSequence('vendor', ['scripts', 'styles']))
    .task('watch', ['build'], require('./gulp/tasks/watch')(gulp, scripts, styles))
    // Prod
    .task('scripts-prod', ['scripts'], require('./gulp/tasks/scripts-prod')(gulp, dir, scripts))
    .task('styles-prod', ['styles'], require('./gulp/tasks/styles-prod')(gulp, dir, styles))
    .task('build-prod', gulpSequence('vendor', ['scripts-prod', 'styles-prod']));
