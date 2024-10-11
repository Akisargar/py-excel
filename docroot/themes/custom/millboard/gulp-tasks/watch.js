module.exports = (gulp, config) => {
  gulp.task('watch:scss', () => {
    gulp.watch(
      ...config.scss.all,
      gulp.series('lint:stylelint', 'scss'),
    );
  });

  gulp.task('watch:js', () => {
    gulp.watch(
      ...config.js.source,
      gulp.series('lint:eslint', 'js'),
    );
  });

  gulp.task('watch', gulp.parallel('watch:scss', 'watch:js'));
};
