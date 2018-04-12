module.exports = (gulp, scripts, styles) => {
    return () => {
        scripts.map((scripts) => {
            gulp.watch(scripts.src, ['scripts']);
        });
        styles.map((styles) => {
            gulp.watch(styles.src, ['styles']);
        });
    };
};
