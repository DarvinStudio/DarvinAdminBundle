const concat     = require('gulp-concat'),
      filesExist = require('files-exist'),
      include    = require('gulp-include'),
      merge      = require('gulp-merge'),
      sourcemaps = require('gulp-sourcemaps');

module.exports = (gulp, dir, scripts) => {
    return () => {
        let stream = merge();

        scripts.forEach((scripts) => {
            stream.add(gulp.src(filesExist(scripts.src))
                .pipe(sourcemaps.init())
                .pipe(include())
                .on('error', console.log)
                .pipe(concat(scripts.target))
                .pipe(sourcemaps.write())
                .pipe(gulp.dest(dir.dev)));
        });

        return stream;
    };
};
