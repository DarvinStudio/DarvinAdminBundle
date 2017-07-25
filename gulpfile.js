var scripts = [
    {
        target: {
            filename:  'app.js',
            directory: {
                dev:  'Resources/public/build-dev',
                prod: 'Resources/public/build'
            }
        },
        src: [
            'Resources/public/vendor/jquery-ui/jquery-ui.js',
            'Resources/public/vendor/jquery-ui/ui/i18n/datepicker-ru.js',
            'Resources/public/vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.js',
            'Resources/public/vendor/jqueryui-timepicker-addon/dist/i18n/jquery-ui-timepicker-ru.js',

            'Resources/public/vendor/dropzone/dist/dropzone.js',
            'Resources/public/vendor/bootstrap/dist/js/bootstrap.js',
            'Resources/public/vendor/chosen/chosen.jquery.js',
            'Resources/public/vendor/jquery-colorbox/jquery.colorbox.js',
            'Resources/public/vendor/jquery.cookie/jquery.cookie.js',
            'Resources/public/vendor/jquery-mousewheel/jquery.mousewheel.js',
            'Resources/public/vendor/noty/lib/noty.js',
            'Resources/public/vendor/sly/dist/sly.js',
            'Resources/public/vendor/jquery-word-and-character-counter-plugin/jquery.word-and-character-counter.js',

            '../../../vendor/a2lix/translation-form-bundle/A2lix/TranslationFormBundle/Resources/public/js/a2lix_translation_default.js',
            '../../../vendor/willdurand/js-translation-bundle/Resources/public/js/translator.min.js',

            'Resources/public/scripts/globals.js',
            'Resources/public/scripts/yandex-translator.js',

            'Resources/public/scripts/ajax-forms.js',
            'Resources/public/scripts/batch-delete.js',
            'Resources/public/scripts/chosen-init.js',
            'Resources/public/scripts/colorbox-init.js',
            'Resources/public/scripts/configuration.js',
            'Resources/public/scripts/counter-init.js',
            'Resources/public/scripts/date-time-pickers-init.js',
            'Resources/public/scripts/dropzone-init.js',
            'Resources/public/scripts/form-collections.js',
            'Resources/public/scripts/images.js',
            'Resources/public/scripts/jscrollpane.js',
            'Resources/public/scripts/master-slave-inputs.js',
            'Resources/public/scripts/noty-init.js',
            'Resources/public/scripts/property-forms.js',
            'Resources/public/scripts/search.js',
            'Resources/public/scripts/slug-suffix.js',
            'Resources/public/scripts/translations-sync.js',
            'Resources/public/scripts/tri-state-checkboxes.js',

            'Resources/public/scripts/app.js'
        ]
    }
];
var styles = [
    {
        target: {
            filename:  'app.css',
            directory: {
                dev:  'Resources/public/build-dev',
                prod: 'Resources/public/build'
            }
        },
        src: [
            'Resources/public/styles/bootstrap.css',
            'Resources/public/styles/bootstrap-chosen.css',
            'Resources/public/styles/jquery-jscrollpane.css',
            'Resources/public/styles/jquery-ui.css',
            'Resources/public/styles/jquery-ui-timepicker-addon.css',

            'Resources/public/styles/colorbox.css',
            'Resources/public/styles/dropzone.css'
        ]
    }
];
var config = {
    imageEmbed: {
        asset: 'web/assets'
    }
};

var gulp       = require('gulp');
var concat     = require('gulp-concat');
var expect     = require('gulp-expect-file');
var imageEmbed = require('gulp-image-embed');
var uglify     = require('gulp-uglify');
var uglifyCSS  = require('gulp-uglifycss');

gulp
    .task('scripts', function () {
        scripts.forEach(function (scripts) {
            gulp.src(scripts.src)
                .pipe(expect(scripts.src))
                .pipe(concat(scripts.target.filename))
                .pipe(gulp.dest(scripts.target.directory.dev));
        });
    })
    .task('scripts-prod', function () {
        scripts.forEach(function (scripts) {
            gulp.src(scripts.src)
                .pipe(expect(scripts.src))
                .pipe(concat(scripts.target.filename))
                .pipe(uglify())
                .pipe(gulp.dest(scripts.target.directory.prod));
        });
    })
    .task('styles', function () {
        styles.forEach(function (styles) {
            gulp.src(styles.src)
                .pipe(expect(styles.src))
                .pipe(concat(styles.target.filename))
                .pipe(gulp.dest(styles.target.directory.dev));
        });
    })
    .task('styles-prod', function () {
        styles.forEach(function (styles) {
            gulp.src(styles.src)
                .pipe(expect(styles.src))
                .pipe(imageEmbed(config.imageEmbed))
                .pipe(uglifyCSS({
                    uglyComments: true
                }))
                .pipe(concat(styles.target.filename))
                .pipe(gulp.dest(styles.target.directory.prod));
        });
    })
    .task('build', ['scripts', 'styles'])
    .task('build-prod', ['scripts-prod', 'styles-prod'])
    .task('watch', function () {
        scripts.forEach(function (scripts) {
            gulp.watch(scripts.src, ['scripts']);
        });
        styles.forEach(function (styles) {
            gulp.watch(styles.src, ['styles']);
        });
    });
