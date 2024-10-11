## Compilation

Install packages with

- yarn or,
- npm install

Compile and lint theme with

- yarn gulp or,
- gulp

### Gulp

The Gulp installation and tasks are setup to work on install, but are still intended to be easily updated based on project needs. The tasks are declared in `gulpfile.js` and broken out within the `gulp-tasks/` subfolder. You can list the available Gulp tasks with `gulp --tasks`. The most common gulp task is `gulp watch` when developing locally, which covers Sass compiling, JS linting, and building dynamic styleguides.
