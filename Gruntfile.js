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

		bumpup: {
			options: {
				updateProps: {
					pkg: 'package.json'
				}
			},
			file: 'package.json'
		},

		replace: {
			plugin_main: {
				src: [ 'aryo-activity-log.php' ],
				overwrite: true,
				replacements: [
					{
						from: /Version: \d{1,1}\.\d{1,2}\.\d{1,2}/g,
						to: 'Version: <%= pkg.version %>'
					}
				]
			},
			
			readme: {
				src: [ 'readme.txt' ],
				overwrite: true,
				replacements: [
					{
						from: /Stable tag: \d{1,1}\.\d{1,2}\.\d{1,2}/g,
						to: 'Stable tag: <%= pkg.version %>'
					}
				]
			}
		},

		shell: {
			git_add_all : {
				command: [
					'git add --all',
					'git commit -m "Bump to <%= pkg.version %>"'
				].join( '&&' )
			}
		},

		release: {
			options: {
				bump: false,
				npm: false,
				tagName: 'v<%= version %>',
				commitMessage: 'released v<%= version %>',
				tagMessage: 'Tagged as v<%= version %>'
			}
		},

		wp_readme_to_markdown: {
			github: {
				options: {
					travisUrlRepo: 'https://travis-ci.org/KingYes/wordpress-aryo-activity-log',
					gruntDependencyStatusUrl: 'https://david-dm.org/KingYes/wordpress-aryo-activity-log'
				},
				files: {
					'README.md': 'readme.txt'
				}
			}
		},

		copy: {
			main: {
				src: [
					'**',
					'!node_modules/**',
					'!build/**',
					'!bin/**',
					'!.git/**',
					'!tests/**',
					'!.travis.yml',
					'!README.md',
					'!phpunit.xml',
					'!vendor/**',
					'!Gruntfile.js',
					'!package.json',
					'!.gitignore',
					'!.gitmodules',
					'!*~'
				],
				dest: 'build/'
			}
		},
		
		clean: {
			//Clean up build folder
			main: [
				'build'
			]
		},

		wp_deploy: {
			deploy:{
				options: {
					plugin_slug: '<%= pkg.name %>',
					svn_user: 'kingyes',
					build_dir: 'build/'
				}
			}
		},

		phpunit: {
			classes: {
				dir: ''
			},
			options: {
				bin: 'phpunit',
				bootstrap: 'tests/bootstrap.php',
				colors: true
			}
		}
		
	} );

	// Default task(s).
	grunt.registerTask( 'default', [
		'checktextdomain',
		'phpunit',
		'wp_readme_to_markdown'
	] );

	grunt.registerTask( 'build', [
		'default',
		'clean',
		'copy'
	] );

	grunt.registerTask( 'publish', [
		'checktextdomain',
		'phpunit',
		'bumpup',
		'replace',
		'wp_readme_to_markdown',
		'shell:git_add_all',
		'release'
	] );
};