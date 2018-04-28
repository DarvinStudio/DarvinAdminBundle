const gnf = require('gulp-npm-files');

module.exports = (gulp, filter) => {
    return () => {
        var dir = './node_modules/';

        /**
         * @var array
         */
        var src = gnf();

        for (var package in filter) {
            if (!filter.hasOwnProperty(package)) {
                continue;
            }

            var index = src.indexOf(dir + package + '/**/*');

            if (-1 === index) {
                continue;
            }

            src.splice(index, 1);

            var paths = Array.isArray(filter[package]) ? filter[package] : [filter[package]];

            src = src.concat(paths.map(function (path) {
                return dir + path;
            }));
        }

        return gulp.src(src, {
                base: './'
            })
            .pipe(gulp.dest('Resources/public'));
    };
};
