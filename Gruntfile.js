/* global module:false */
module.exports = function(grunt) {

	// Project configuration
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		meta: {
			banner: ''
				//'/*!\n' +
				//' * Above the fold Optimization <%= pkg.version %>\n' +
				//' * @author info@optimalisatie.nl\n' +
				//' */'
		},

		uglify: {
			options: {
				banner: '<%= meta.banner %>\n'
			},
			build: {
				files: {
                    'public/js/abovethefold.min.js' : [
                        'public/js/abovethefold.js'
                    ]

				}
			}
		}
	});

	// Load Dependencies
	require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

    grunt.registerTask( 'default', [ 'uglify' ] );
};
