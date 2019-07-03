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
            vendor: [
                'Resources/public/node_modules/ace-builds/src-min-noconflict/ace.js',
                'Resources/public/node_modules/dropzone/dist/dropzone.js',
                'Resources/public/node_modules/jquery-text-counter/textcounter.js',
                'Resources/public/node_modules/components-jqueryui/jquery-ui.js',
                'Resources/public/node_modules/components-jqueryui/ui/i18n/datepicker-ru.js',
                'Resources/public/node_modules/jquery-ui-timepicker-addon/dist/jquery-ui-timepicker-addon.js',
                'Resources/public/node_modules/jquery-ui-timepicker-addon/dist/i18n/jquery-ui-timepicker-ru.js'
            ],
            src: [
                'Resources/scripts/date-time-picker.js',
                'Resources/scripts/dropzone.js',
                'Resources/scripts/textcounter.js'
            ]
        }
    ],
    styles = [
        {
            target: 'app.css',
            src:    [
                'Resources/public/node_modules/dropzone/dist/dropzone.css',
                'Resources/public/node_modules/components-jqueryui/themes/flick/jquery-ui.css',
                'Resources/public/node_modules/jquery-ui-timepicker-addon/dist/jquery-ui-timepicker-addon.css'
            ]
        }
    ],
    vendorFilter = {
        'ace-builds': 'ace-builds/src-min-noconflict/*'
    };

// Tasks
gulp
    // Common
    .task('vendor', require('./Resources/gulp/tasks/vendor')(gulp, vendorFilter))
    // Dev
    .task('scripts', require('./Resources/gulp/tasks/scripts')(gulp, dir, scripts))
    .task('styles', require('./Resources/gulp/tasks/styles')(gulp, dir, styles))
    .task('build', gulpSequence('vendor', ['scripts', 'styles']))
    .task('watch', ['build'], require('./Resources/gulp/tasks/watch')(gulp, scripts, styles))
    // Prod
    .task('scripts-prod', ['scripts'], require('./Resources/gulp/tasks/scripts-prod')(gulp, dir, scripts))
    .task('styles-prod', ['styles'], require('./Resources/gulp/tasks/styles-prod')(gulp, dir, styles))
    .task('build-prod', gulpSequence('vendor', ['scripts-prod', 'styles-prod']));
