var gulp = require('gulp');                   // Gulp!

var stylus = require('gulp-stylus');          // Stylus
var nib = require('nib');                     // Nib helper library for stylus
var jeet = require('jeet');                   // Jeet grid for stylus
var rupture = require('rupture');             // Rupture helper library for stylus
var prefix = require('gulp-autoprefixer');    // Autoprefixr
var minifycss = require('gulp-minify-css');   // Minify CSS
var concat = require('gulp-concat');          // Concat files
var uglify = require('gulp-uglify');          // Uglify javascript
var rename = require('gulp-rename');          // Rename files
var util = require('gulp-util');              // Logging
var jshint = require("gulp-jshint");          // jshint
var plumber = require('gulp-plumber');


/***********************************
 * Compile all CSS for the site
 **********************************/

  gulp.task('stylus', function (){
    gulp.src(['assets/styl/admin.styl'])
      .pipe(stylus({ use: [
        nib(),
        jeet(),
        rupture()
      ]}))
      .pipe(plumber())
      .pipe(concat('admin.css'))
      .pipe(gulp.dest('assets/css/'))
      .pipe(rename({suffix: '.min'}))
      .pipe(minifycss())
      .pipe(gulp.dest('assets/css/'));

    gulp.src(['assets/styl/frontend.styl'])
      .pipe(stylus({ use: [
        nib(),
        jeet(),
        rupture()
      ]}))
      .pipe(plumber())
      .pipe(concat('frontend.css'))
      .pipe(gulp.dest('assets/css/'))
      .pipe(rename({suffix: '.min'}))
      .pipe(minifycss())
      .pipe(gulp.dest('assets/css/'));
  });


/***************************************
 * Get all the JS, concat and uglify
 **************************************/

  gulp.task('javascripts', function(){
    gulp.src([
      'assets/js/plugins/*.js',
      'assets/js/_*.js'])                         // Gets all the user JS _*.js from assets/js
      .pipe(plumber())
      .pipe(concat('scripts.js'))                 // Concat all the scripts
      .pipe(gulp.dest('assets/js/'))              // Set destination to assets/js
      .pipe(rename({suffix: '.min'}))             // Rename it
      .pipe(uglify())                             // Uglify(minify)
      .pipe(gulp.dest('assets/js/'))              // Set destination to assets/js
      util.log(util.colors.yellow('Javascripts compiled and minified'));
  });


/****************
 * JS hint
 ***************/

gulp.task('jshint', function() {
  gulp.src('assets/js/_*.js')
      .pipe(jshint())
      .pipe(jshint.reporter('jshint-stylish'));
});

/***********************************
 * Default Gulp Task
 **********************************/

gulp.task('watch', function(){

  gulp.watch('**/*.php').on('change', function(file) {
    util.log(util.colors.yellow('PHP file changed' + ' (' + file.path + ')'));
  });


  gulp.watch("assets/styl/**/*.styl", ['stylus']);              // Watch and run stylus on changes
  gulp.watch("assets/js/_*.js", ['jshint', 'javascripts']);     // Watch and run javascripts on changes

});

gulp.task('default', ['stylus', 'jshint', 'javascripts', 'watch']);