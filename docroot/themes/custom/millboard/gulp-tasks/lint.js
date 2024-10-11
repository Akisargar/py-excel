const stylelint = require('gulp-stylelint');
const eslint = require('gulp-eslint');
const gulpIf = require('gulp-if');

module.exports = (gulp, config) => {
  gulp.task('lint:stylelint', () =>
    gulp.src(config.scss.all).pipe(stylelint(config.stylelint.options)),
  );

  gulp.task('lint:eslint', () =>
    gulp
      .src(config.js.source)
      .pipe(eslint())
      .pipe(gulpIf(process.env.CI === 'true', eslint.failOnError())),
  );

  gulp.task(
    'lint',
    gulp.series('prettier', gulp.parallel('lint:stylelint', 'lint:eslint')),
  );
};
