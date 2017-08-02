// Dependencies
var gulp       = require('gulp'),

    gutil      = require('gulp-util'),
    concat     = require('gulp-concat'),
    expect     = require('gulp-expect-file'),
    imageEmbed = require('gulp-image-embed'),
    merge      = require('gulp-merge'),
    rewriteCSS = require('gulp-rewrite-css'),
    stripDebug = require('gulp-strip-debug'),
    uglify     = require('gulp-uglify'),
    uglifyCSS  = require('gulp-uglifycss');

// Build directories
var dir = {
    dev:  'Resources/public/build-dev',
    prod: 'Resources/public/build'
};

// Assets
var scripts = [
        {
            target: 'app.js',
            src:    [
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
    ],
    styles = [
        {
            target: 'app.css',
            src:    [
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

// Tasks
gulp
    .task('scripts', function () {
        var stream = merge();

        scripts.forEach(function (scripts) {
            stream.add(gulp.src(scripts.src)
                .pipe(expect(scripts.src))
                .pipe(concat(scripts.target))
                .pipe(gulp.dest(dir.dev)));
        });

        return stream;
    })
    .task('scripts-prod', ['scripts'], function () {
        var stream = merge();

        scripts.forEach(function (scripts) {
            stream.add(gulp.src(scripts.src)
                .pipe(expect(scripts.src))
                .pipe(stripDebug())
                .pipe(concat(scripts.target))
                .pipe(uglify())
                .pipe(gulp.dest(dir.prod)));

            for (var key in dir) {
                if (dir.hasOwnProperty(key)) {
                    console.log(gutil.colors.yellow(
                        'Do not forget to commit the "' + [dir[key], scripts.target].join('/') + '" file :)'
                    ));
                }
            }
        });

        return stream;
    })
    .task('styles', function () {
        var stream = merge();

        styles.forEach(function (styles) {
            stream.add(gulp.src(styles.src)
                .pipe(expect(styles.src))
                .pipe(rewriteCSS({
                    destination: dir.dev
                }))
                .pipe(concat(styles.target))
                .pipe(gulp.dest(dir.dev)));
        });

        return stream;
    })
    .task('styles-prod', ['styles'], function () {
        var stream = merge();

        styles.forEach(function (styles) {
            stream.add(gulp.src(styles.src)
                .pipe(expect(styles.src))
                .pipe(rewriteCSS({
                    destination: dir.prod
                }))
                .pipe(imageEmbed({
                    asset: 'web/assets'
                }))
                .pipe(uglifyCSS({
                    uglyComments: true
                }))
                .pipe(concat(styles.target))
                .pipe(gulp.dest(dir.prod)));

            for (var key in dir) {
                if (dir.hasOwnProperty(key)) {
                    console.log(gutil.colors.yellow(
                        'Do not forget to commit the "' + [dir[key], styles.target].join('/') + '" file :)'
                    ));
                }
            }
        });

        return stream;
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
