const prettier = require('gulp-prettier');

module.exports = (gulp, config) => {
  gulp.task('prettier', () =>
    gulp
      .src([...config.scss.source])
      .pipe(prettier())
      .pipe(gulp.dest((file) => file.base)),
  );
};
