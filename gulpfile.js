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
            'Resources/public/vendor/bootstrap/dist/js/bootstrap.js',
            'Resources/public/vendor/chosen/chosen.jquery.js',
            'Resources/public/vendor/dropzone/dist/dropzone.js',
            'Resources/public/vendor/jquery-colorbox/jquery.colorbox.js',
            'Resources/public/vendor/jquery-mousewheel/jquery.mousewheel.js',
            'Resources/public/vendor/jquery-ui/jquery-ui.js',
            'Resources/public/vendor/jquery-ui/ui/i18n/datepicker-ru.js',
            'Resources/public/vendor/jquery-word-and-character-counter-plugin/jquery.word-and-character-counter.js',
            'Resources/public/vendor/jquery.cookie/jquery.cookie.js',
            'Resources/public/vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.js',
            'Resources/public/vendor/jqueryui-timepicker-addon/dist/i18n/jquery-ui-timepicker-ru.js',
            'Resources/public/vendor/noty/lib/noty.js',
            'Resources/public/vendor/sly/dist/sly.js',

            '../../../vendor/a2lix/translation-form-bundle/A2lix/TranslationFormBundle/Resources/public/js/a2lix_translation_default.js',
            '../../../vendor/willdurand/js-translation-bundle/Resources/public/js/translator.min.js',

            'Resources/scripts/globals.js',
            'Resources/scripts/yandex-translator.js',

            'Resources/scripts/ajax-form.js',
            'Resources/scripts/batch-delete.js',
            'Resources/scripts/chosen-init.js',
            'Resources/scripts/colorbox-init.js',
            'Resources/scripts/configuration.js',
            'Resources/scripts/counter-init.js',
            'Resources/scripts/date-time-pickers-init.js',
            'Resources/scripts/dropzone-init.js',
            'Resources/scripts/form-collections.js',
            'Resources/scripts/images.js',
            'Resources/scripts/master-slave-inputs.js',
            'Resources/scripts/property-forms.js',
            'Resources/scripts/search.js',
            'Resources/scripts/slug-suffix.js',
            'Resources/scripts/translations-sync.js',
            'Resources/scripts/tri-state-checkboxes.js',

            'Resources/scripts/app.js'
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
            'Resources/public/vendor/bootstrap/dist/css/bootstrap.css',
            'Resources/public/vendor/dropzone/dist/dropzone.css',
            'Resources/public/vendor/jquery-colorbox/example4/colorbox.css',
            'Resources/public/vendor/jquery-ui/themes/smoothness/jquery-ui.css',
            'Resources/public/vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.css',
            'Resources/public/vendor/noty/lib/noty.css',

            'Resources/public/styles/bootstrap-chosen.css'
        ]
    }
];
var config = {
    imageEmbed: {
        asset: 'web/assets'
    }
};

var gulp       = require('gulp');
var gutil      = require('gulp-util');
var concat     = require('gulp-concat');
var expect     = require('gulp-expect-file');
var imageEmbed = require('gulp-image-embed');
var stripDebug = require('gulp-strip-debug');
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
    .task('scripts-prod', ['scripts'], function () {
        scripts.forEach(function (scripts) {
            gulp.src(scripts.src)
                .pipe(expect(scripts.src))
                .pipe(stripDebug())
                .pipe(concat(scripts.target.filename))
                .pipe(uglify())
                .pipe(gulp.dest(scripts.target.directory.prod));

            for (var key in scripts.target.directory) {
                if (scripts.target.directory.hasOwnProperty(key)) {
                    console.log(gutil.colors.yellow(
                        'Do not forget to commit the "' + [scripts.target.directory[key], scripts.target.filename].join('/') + '" file :)'
                    ));
                }
            }
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
    .task('styles-prod', ['styles'], function () {
        styles.forEach(function (styles) {
            gulp.src(styles.src)
                .pipe(expect(styles.src))
                .pipe(imageEmbed(config.imageEmbed))
                .pipe(uglifyCSS({
                    uglyComments: true
                }))
                .pipe(concat(styles.target.filename))
                .pipe(gulp.dest(styles.target.directory.prod));

            for (var key in styles.target.directory) {
                if (styles.target.directory.hasOwnProperty(key)) {
                    console.log(gutil.colors.yellow(
                        'Do not forget to commit the "' + [styles.target.directory[key], styles.target.filename].join('/') + '" file :)'
                    ));
                }
            }
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
