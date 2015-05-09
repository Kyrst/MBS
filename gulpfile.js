var gulp = require('gulp'),
	compass = require('gulp-compass'),
	jshint = require('gulp-jshint'),
	stylish = require('jshint-stylish'),
	imagemin = require('gulp-imagemin'),
	uglify = require('gulp-uglify'),
	util = require('gulp-util'),
	concat = require('gulp-concat'),
	scsslint = require('gulp-scss-lint'),
	cache = require('gulp-cached'),
	debug = require('gulp-debug');

var PUBLIC_DIR = 'public/',
	RESOURCES_DIR = 'resources/',
	ASSETS_DIR = RESOURCES_DIR + 'assets/',
	SASS_DIR = ASSETS_DIR + 'sass/',
	CSS_DIR = PUBLIC_DIR + 'css/',
	JS_DIR = PUBLIC_DIR + 'js/',
	IMAGES_DIR = PUBLIC_DIR + 'images/';

gulp.task('scss-lint', function()
{
	gulp.src(SASS_DIR + '**/*.scss')
		//.pipe(cache('scsslint'))
		.pipe(debug());
		//.pipe(scsslint());
});

gulp.task('compass', function()
{
	gulp.src(SASS_DIR + '**/*.scss')
		.pipe(compass(
		{
			config_file: 'config.rb',
			css: CSS_DIR,
			sass: SASS_DIR
		}))
		.on('error', function(error)
		{
			console.log(error);

			this.emit('end');
		})
		.pipe(debug())
		.pipe(gulp.dest(CSS_DIR));
});

gulp.task('js-lint', function()
{
	return gulp.src(ASSETS_DIR + 'js/**/*.js')
		.pipe(jshint())
		.pipe(jshint.reporter(stylish));
});

gulp.task('concat-core', function()
{
	return gulp.src(
		[
			PUBLIC_DIR + 'libs/core/js/uri.js',
			PUBLIC_DIR + 'libs/core/js/ajax/ajax_request.js',
			PUBLIC_DIR + 'libs/core/js/ajax.js',
			PUBLIC_DIR + 'libs/core/js/ui/dialog.js',
			PUBLIC_DIR + 'libs/core/js/ui/message/engine/sweetalert.js',
			PUBLIC_DIR + 'libs/core/js/ui/message/engine/noty.js',
			PUBLIC_DIR + 'libs/core/js/ui/message.js',
			PUBLIC_DIR + 'libs/core/js/ui.js',
			PUBLIC_DIR + 'libs/core/js/form/file_upload.js',
			PUBLIC_DIR + 'libs/core/js/form.js',
			PUBLIC_DIR + 'libs/core/js/dynamic_table.js',
			PUBLIC_DIR + 'libs/core/js/dynamic_item.js',
			PUBLIC_DIR + 'libs/core/js/core.js'
		])
		.pipe(uglify())
		.pipe(concat('core.min.js'))
		.pipe(debug())
		.pipe(gulp.dest(PUBLIC_DIR + 'libs/core/'));
});

gulp.task('uglify', function()
{
	gulp.src(ASSETS_DIR + 'js/**/*.js')
		//.pipe(uglify().on('error', util.log))
		.pipe(debug())
		.pipe(gulp.dest(JS_DIR));
});

gulp.task('imagemin', function()
{
	return gulp.src(ASSETS_DIR + 'images/*')
		.pipe(imagemin(
			{
				progressive: true,
				svgoPlugins: [{ removeViewBox: false }]
			}))
		.pipe(debug())
		.pipe(gulp.dest(IMAGES_DIR));
});

gulp.task('watch', function()
{
	gulp.watch(SASS_DIR + '**/*.scss', ['scss-lint', 'compass']);
	gulp.watch(ASSETS_DIR + 'js/**/*.js', ['js-lint', 'uglify']);
	gulp.watch(PUBLIC_DIR + 'libs/core/js/**/*.js', ['js-lint', 'concat-core']);
});

gulp.task('default', [/*'scss-lint', */'compass', 'js-lint', 'concat-core', 'uglify', 'imagemin', 'watch']);
