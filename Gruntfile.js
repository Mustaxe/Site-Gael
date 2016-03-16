'use strict';

module.exports = function (grunt) {

	// Load grunt tasks automatically
	require('load-grunt-tasks')(grunt);

	require('time-grunt')(grunt);

	// Define the configuration for all the tasks
	grunt.initConfig({
		// Empties folders to start fresh
		watch: {

			js: {
				files: ['js/{,*/}*.js'],
				tasks: ['jshint'],
				options: {
					livereload: true
				}
			},
			compass: {
				files: ['css/src/**/*.{scss,sass}'],
				tasks: ['compass:server']
			}
		},

		jshint: {
			options: {
				jshintrc: '.jshintrc',
				reporter: require('jshint-stylish')
			},
			all: [
				'Gruntfile.js',
				'/js/{,*/}*.js',
				'/js/{,*/}*.min.js'
			]
		},

		compass: {
			options: {
				sassDir: 'css/src',
				cssDir: 'css',
				generatedImagesDir: 'images/generated',
				imagesDir: 'images',
				javascriptsDir: 'js',
				fontsDir: 'fonts',
				httpImagesPath: '/images',
				httpGeneratedImagesPath: '/images/generated',
				httpFontsPath: '/fonts/webfonts',
				relativeAssets: false,
				assetCacheBuster: false
			},
			server: {
				options: {
					debugInfo: true
				}
			}
		},

		clean: {
			dist: {
				files: [{
					dot: true,
					src: [
						'svg'
					]
				}]
			}
		},

		svgmin: {
			dist: {
				files: [{                // Dictionary of files
					expand: true,        // Enable dynamic expansion.
					removeViewBox: false,
					cwd: 'src_svg',        // Src matches are relative to this path.
					src: ['**/*.svg'],    // Actual pattern(s) to match.
					dest: 'svg'        // Destination path prefix.
				}]
			}
		},

		cssmin: {
			dist: {
				files: {
					'css/main.min.css': [
						'css/main.css'
					]
				}
			}
		},

		uglify: {
			dist: {
				files: {
					'js/scripts.min.js': [
						'js/scripts.js'
					]
				}
			}
		},

		concat: {
			options: {
			  separator: ';',
			},
			dist: {
				src: [
					'vendor/jquery-1.10.1.min.js',
					'vendor/ddscrollspy.min.js',
					'vendor/jquery.mCustomScrollbar.concat.min.js',
					'vendor/jquery.stellar.min.js',
					'vendor/jquery.easing.1.3.min.js',
					'vendor/TweenMax.min.js',
					'vendor/validate.min.js'
				],
				dest: 'vendor/vendor.min.js'
			}
		}
	});

	grunt.registerTask('serve', function (target) {
		if (target === 'dist') {
			return grunt.task.run(['build', 'connect:dist:keepalive']);
		}

		grunt.task.run([
			'compass:server',
			'watch'
		]);
	});

	grunt.registerTask('build', [
		// 'clean:dist',
		'cssmin:dist',
		// 'svgmin:dist',
		'uglify:dist',
		'concat'
	]);
};
