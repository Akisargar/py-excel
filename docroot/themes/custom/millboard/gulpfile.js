const gulp = require('gulp');

const config = require('./gulp-tasks/config');

const tasks = [
  'scss',
  'js',
  'svg',
  'prettier',
  'lint',
  'images',
  'watch',
  'default',
];

tasks.forEach((task) => {
  const t = require(`./gulp-tasks/${task}`);
  t(gulp, config);
});
