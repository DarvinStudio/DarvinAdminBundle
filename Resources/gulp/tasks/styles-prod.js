const autoprefixer = require('gulp-autoprefixer'),
      concat       = require('gulp-concat'),
      csso         = require('gulp-csso'),
      filesExist   = require('files-exist'),
      gutil        = require('gulp-util'),
      merge        = require('gulp-merge'),
      rewriteCSS   = require('gulp-rewrite-css'),
      uglifyCSS    = require('gulp-uglifycss');

module.exports = (gulp, dir, styles) => {
    return () => {
        let stream = merge();

        styles.forEach((styles) => {
            stream.add(gulp.src(filesExist(styles.src))
                .pipe(autoprefixer({
                    browsers: ['last 2 versions', '>3%', 'ie 10'],
                    cascade:  false
                }))
                .pipe(csso())
                .pipe(rewriteCSS({
                    destination: dir.prod
                }))
                .pipe(uglifyCSS({
                    uglyComments: true
                }))
                .pipe(concat(styles.target))
                .pipe(gulp.dest(dir.prod)));

            for (let key in dir) {
                if (dir.hasOwnProperty(key)) {
                    console.log(gutil.colors.yellow(
                        'Do not forget to commit the "' + [dir[key], styles.target].join('/') + '" file :)'
                    ));
                }
            }
        });

        return stream;
    };
};
