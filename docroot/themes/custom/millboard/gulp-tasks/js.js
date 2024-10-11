const babel = require('gulp-babel');

module.exports = (gulp, config) => {
  gulp.task('js', () =>
    gulp
      .src(config.js.source)
      .pipe(babel({
        presets: ['@babel/preset-env'],
      }))
      .pipe(gulp.dest(config.js.destination)),
  );
};
