const gnf = require('gulp-npm-files');

module.exports = (gulp) => {
    return () => {
        return gulp.src(gnf(), {
                base: './'
            })
            .pipe(gulp.dest('Resources/public'));
    };
};
