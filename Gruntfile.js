/**
 * ARYO Activity Log Makefile
 */
'use strict';

module.exports = function(grunt) {

	require('matchdep').filterDev('grunt-*').forEach( grunt.loadNpmTasks );

	// Project configuration.
	grunt.initConfig( {
		pkg: grunt.file.readJSON('package.json'),

		checktextdomain: {
			standard: {
				options:{
					text_domain: 'aryo-aal',
					keywords: [
						// WordPress keywords
						'__:1,2d',
						'_e:1,2d',
						'_x:1,2c,3d',
						'esc_html__:1,2d',
						'esc_html_e:1,2d',
						'esc_html_x:1,2c,3d',
						'esc_attr__:1,2d',
						'esc_attr_e:1,2d',
						'esc_attr_x:1,2c,3d',
						'_ex:1,2c,3d',
						'_n:1,2,4d',
						'_nx:1,2,4c,5d',
						'_n_noop:1,2,3d',
						'_nx_noop:1,2,3c,4d'
					]
				},
				files: [ {
					src: [
						'classes/*.php',
						'language/*.php',
						'*.php'
					],
					expand: true
				} ]
			}
		},

		wp_readme_to_markdown: {
			github: {
				files: {
					'README.md': 'readme.txt'
				}
			}
		}
		
	} );

	// Default task(s).
	grunt.registerTask( 'default', [
		'checktextdomain',
		'wp_readme_to_markdown'
	] );
};