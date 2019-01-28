'use strict';

/**
 * Define configurations
 */

const info = require( './package.json' );

const config = {
	init: './' + info.name + '.php',
	src: {
		scss: [ './assets/scss/**/*.scss' ],
		css: [ './assets/css/**/*.css', '!./assets/css/vendors/*' ],
		js: [ './assets/js/**/*.js', '!./assets/js/vendors/*' ],
		pot: [ './**/*.php', '!./__build/**/*.php' ],
		build: [
			'./*',
			'./assets/css/**/*',
			'./assets/icons/**/*',
			'./assets/images/**/*',
			'./assets/js/**/*',
			'./inc/**/*',
			'./languages/**/*',
			'./page-templates/**/*',
			'./template-parts/**/*',

			// exclude files and folders
			'!**/Thumbs.db',
			'!**/.DS_Store',
			'!./.gitignore',
			'!./package*.json',
			'!./gulpfile.js',
			'!./node_modules',
			'!./README.md',
			'!./LICENSE.md',
			'!./__build',
		],
	},
	dest: {
		scss: './assets/scss',
		css: './assets/css',
		js: './assets/js',
		icons: './assets/icons',
		pot: './languages',
		build: './__build/' + info.name,
		zip: './__build/zip',
	},
};

/**
 * Init Gulp and plugins
 */

const gulp          = require( 'gulp' );

// Translation
const wpPot         = require( 'gulp-wp-pot' );

// Others
const fs            = require( 'fs' );
const del           = require( 'del' );
const replace       = require( 'gulp-replace' );
const watch         = require( 'gulp-watch' );
const zip           = require( 'gulp-zip' );

/**
 * Task: Change plugin / theme info based on package.json values.
 */
gulp.task( 'main_info', function() {
	var info = JSON.parse( fs.readFileSync( './package.json' ) );

	// Change plugin / theme info
	return gulp.src( [ config.init ] )
		.pipe( replace( new RegExp( '((?:Plugin|Theme) Name: ).*' ), '$1' + info.title ) )
		.pipe( replace( new RegExp( '((?:Plugin|Theme) URI: ).*' ), '$1' + info.uri ) )
		.pipe( replace( new RegExp( '(Description: ).*' ), '$1' + info.description ) )
		.pipe( replace( new RegExp( '(Version: ).*' ), '$1' + info.version ) )
		.pipe( replace( new RegExp( '(Author: ).*' ), '$1' + info.author.name ) )
		.pipe( replace( new RegExp( '(Author URI: ).*' ), '$1' + info.author.url ) )
		.pipe( replace( new RegExp( '(Text Domain: ).*' ), '$1' + info.name ) )
		.pipe( replace( new RegExp( '(Tags: ).*' ), '$1' + info.keywords.join( ', ' ) ) )

		.pipe( replace( new RegExp( '(\'' + info.name.replace( '-', '_' ).toUpperCase() + '_VERSION\', \').*?(\'.*)' ), '$1' + info.version + '$2' ) )

		.pipe( gulp.dest( './' ) );
} );

/**
 * Task: Change info on readme.txt based on package.json values.
 */
gulp.task( 'readme_txt', function() {
	var info = JSON.parse( fs.readFileSync( './package.json' ) );

	var contributors = info.contributors.map(function( contributor ) {
		return contributor.name;
	});

	// Change plugin / theme version on readme.txt
	return gulp.src( [ './readme.txt' ] )
		.pipe( replace( new RegExp( '(===).*(===)' ), '$1 ' + info.title + ' $2' ) )
		.pipe( replace( new RegExp( '(Contributors: ).*' ), '$1' + contributors.join( ', ' ) ) )
		.pipe( replace( new RegExp( '(Tags: ).*' ), '$1' + info.keywords.join( ', ' ) ) )
		.pipe( replace( new RegExp( '(Stable tag: ).*' ), '$1' + info.version ) )

		.pipe( replace( new RegExp( '(\n\n).*(\n\n== Description ==)' ), '$1' + info.description + '$2' ) )

		// .pipe( replace( new RegExp( '(== Description ==\n\n).*(\n\n)' ), '$1' + info.description + '$2' ) )

		.pipe( gulp.dest( './' ) );
} );

/**
 * Wrapper Task: Set plugin / theme info files.
 */
gulp.task( 'info', gulp.series( 'main_info', 'readme_txt' ) );

/**
 * Task: Generate .pot file for translation.
 */
gulp.task( 'pot', function() {
	var info = JSON.parse( fs.readFileSync( './package.json' ) );

	return gulp.src( config.src.pot.concat( [ config.init ] ) )
		.pipe( wpPot( {
			domain: info.name,
			package: info.title,
			metadataFile: config.init,
		} ).on( 'error', function( error ) {
			console.error( error );
			this.emit( 'end' );
		} ) )
		.pipe( gulp.dest( config.dest.pot + '/' + info.name + '.pot' ) );
} );

/**
 * Task: Watch all files and copy to 'build' folder.
 */
gulp.task( 'watch', function() {
	watch( './package.json', function() {
		gulp.task( 'info' )();
	} );

	watch( config.src.pot, function() {
		gulp.task( 'pot' )();
	} );

	watch( config.src.scss, function() {
		gulp.task( 'css' )();
	} );

	watch( config.src.js.concat( [ '!./assets/js/**/*.min.js' ] ), function( obj ) {
		gulp.task( 'js' )();
	} );

	watch( config.src.build, { base: './' }, function( obj ) {
		if ( 'unlink' === obj.event ) {
			del( config.dest.build + '/' + obj.relative, { force: true } );
		} else {
			gulp.src( obj.path, { base: './' } )
				.pipe( gulp.dest( config.dest.build ) );
		}
	} );
} );

/**
 * Task: Clean files in "__build" directory.
 */
gulp.task( 'clean', function() {
	return del( config.dest.build + '/*', { force: true } );
} );

/**
 * Task: Copy selected files from sources to "__build" directory.
 */
gulp.task( 'copy', function() {
	return gulp.src( config.src.build, { base: './' } )
		.pipe( gulp.dest( config.dest.build ) );
} );

/**
 * Wrapper Task: Build.
 */
gulp.task( 'build', gulp.series( 'pot', 'info', 'clean', 'copy' ) );

/**
 * Wrapper Task: Default task.
 */
gulp.task( 'default', gulp.series( 'build', 'watch' ) );

/**
 * Wrapper Task: Build to zip.
 */
gulp.task( 'zip', function() {
	var info = JSON.parse( fs.readFileSync( './package.json' ) );

	return gulp.src( config.dest.build + '/**/*', { buffer: false, base: config.dest.build + '/../' } )
		.pipe( zip( info.name + '-' + info.version + '.zip' ) )
		.pipe( gulp.dest( config.dest.zip ) );
} );