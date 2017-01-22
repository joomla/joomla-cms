module.exports = function(grunt) {
	var settings      = grunt.file.readYAML('grunt_settings.yaml'),
		path          = require('path'),
		preText       = '{\n "name": "joomla-assets",\n "version": "4.0.0",\n "description": "External assets that Joomla is using",\n "dependencies": {\n  ',
		postText      = '  },\n  "license": "GPL-2.0+"\n}',
		name, tinyXml, codemirrorXml,
		vendorsTxt    = '',
		vendorsArr    = '',
		polyFillsUrls = [],
		xmlVersionStr = /(<version>)(\d+.\d+.\d+)(<\/version>)/;

	// Loop to get some text for the packgage.json
	for (name in settings.vendors) {
		vendorsTxt += '"' + name + '": "' + settings.vendors[name].version + '",';
	}

	// Loop to get some text for the assets.php
	for (name in settings.vendors) {
		vendorsArr += '\'' + name + '\' => array(\'version\' => \'' + settings.vendors[name].version + '\',' + '\'dependencies\' => \'' + settings.vendors[name].dependencies + '\'),\n\t\t\t';
	}

	// Build the package.json and assets.php for all 3rd Party assets
	grunt.file.write('build/assets_tmp/package.json', preText + vendorsTxt.substring(0, vendorsTxt.length - 1) + postText);
//	grunt.file.write('build/assets_tmp.php', '<?php\ndefined(\'_JEXEC\') or die;\n\nabstract class ExternalAssets{\n\tpublic static function getCoreAssets() {\n\t\t return array(\n\t\t\t' + vendorsArr + '\n\t\t);\n\t}\n}\n');

	// Project configuration.
	grunt.initConfig({
		folder : {
			system        : 'media/system/js',
			fields        : 'media/system/js/fields',
			legacy        : 'media/system/js/legacy',
			vendor        : 'media/vendor',
			puny          : 'media/vendor/punycode/js',
			codemirror    : 'media/vendor/codemirror',
			adminTemplate : 'administrator/templates/atum',
			node_module   : 'build/assets_tmp/node_modules/'
		},

		// Let's clean up the system
		clean: {
			assets: {
				src: [
					'media/vendor/jquery/js/*',
					'media/vendor/bootstrap/**',
					'media/vendor/tether/**',
					'media/vendor/jcrop/**',
					'media/vendor/dragula/**',
					'media/vendor/font-awesome/**',
					'media/vendor/tinymce/plugins/*',
					'media/vendor/tinymce/skins/*',
					'media/vendor/tinymce/themes/*',
					'media/vendor/punycode/*',
					'media/vendor/codemirror/*',
					'media/vendor/mediaelement/*',
					'media/vendor/chosenjs/*',
					'media/vendor/awesomplete/*',
					'media/vendor/flying-focus-a11y/*',
				],
				expand: true,
				options: {
					force: true
				}
			},
			temp: { src: [ 'build/assets_tmp/*', 'build/assets_tmp/tmp', 'build/assets_tmp/package.json' ], expand: true, options: { force: true } }
		},

		// Update all the packages to the version specified in assets/package.json
		shell: {
			update: {
				command: [
					'cd build/assets_tmp',
					'npm install'
				].join('&&')
			}
		},

		// Concatenate some javascript files
		concat: {
			someFiles: {
				files: [
					{
						src: settings.CmAddons.js.map(function (v) {
							return '<%= folder.node_module %>codemirror/' + v;
						}),
						dest: '<%= folder.node_module %>codemirror/lib/addons.js'
					},
					{
						src: settings.CmAddons.css.map(function (v) {
							return '<%= folder.node_module %>codemirror/' + v;
						}),
						dest: '<%= folder.node_module %>codemirror/lib/addons.css'
					}
				]
			}
		},

		// Transfer all the assets to media/vendor
		copy: {
			fromSource: {
				files: [
					// jQuery js files
					{ expand: true, cwd: '<%= folder.node_module %>jquery/dist/', src: ['*', '!(core.js)'], dest: 'media/vendor/jquery/js/', filter: 'isFile'},
					// jQuery js migrate files
					{ expand: true, cwd: '<%= folder.node_module %>jquery-migrate/dist/', src: ['**'], dest: 'media/vendor/jquery/js/', filter: 'isFile'},
					//Bootastrap js files
					{ expand: true, cwd: '<%= folder.node_module %>bootstrap/dist/js/', src: ['**'], dest: 'media/vendor/bootstrap/js/', filter: 'isFile'},
					//Bootastrap scss files
					{ expand: true, cwd: '<%= folder.node_module %>bootstrap/scss/', src: ['**'], dest: 'media/vendor/bootstrap/scss/', filter: 'isFile'},
					//Bootastrap css files
					{ expand: true, cwd: '<%= folder.node_module %>bootstrap/dist/css/', src: ['**'], dest: 'media/vendor/bootstrap/css/', filter: 'isFile'},
					//Teether js files
					{ expand: true, cwd: '<%= folder.node_module %>tether/dist/js/', src: ['**'], dest: 'media/vendor/tether/js/', filter: 'isFile'},
					// Punycode js files
					{ expand: true, cwd: '<%= folder.node_module %>punycode/', src: ['punycode.js', 'LICENSE-MIT.txt'], dest: 'media/vendor/punycode/js/', filter: 'isFile'},
					// Cropperjs css files
					{ expand: true, cwd: '<%= folder.node_module %>cropperjs/dist', src: ['*.css'], dest: 'media/vendor/cropperjs/css/', filter: 'isFile'},
					// Cropperjs js files
					{ expand: true, cwd: '<%= folder.node_module %>cropperjs/dist', src: ['*.js'], dest: 'media/vendor/cropperjs/js/', filter: 'isFile'},
					//Font Awesome css files
					{ expand: true, cwd: '<%= folder.node_module %>font-awesome/css/', src: ['**'], dest: 'media/vendor/font-awesome/css/', filter: 'isFile'},
					//Font Awesome scss files
					{ expand: true, cwd: '<%= folder.node_module %>font-awesome/scss/', src: ['**'], dest: 'media/vendor/font-awesome/scss/', filter: 'isFile'},
					//Font Awesome fonts files
					{ expand: true, cwd: '<%= folder.node_module %>font-awesome/fonts/', src: ['**'], dest: 'media/vendor/font-awesome/fonts/', filter: 'isFile'},
					// tinyMCE plugins
					{ expand: true, cwd: '<%= folder.node_module %>tinymce/plugins/', src: ['**'], dest: 'media/vendor/tinymce/plugins/', filter: 'isFile'},
					// tinyMCE skins
					{ expand: true, cwd: '<%= folder.node_module %>tinymce/skins/', src: ['**'], dest: 'media/vendor/tinymce/skins/', filter: 'isFile'},
					// tinyMCE themes
					{ expand: true, cwd: '<%= folder.node_module %>tinymce/themes/', src: ['**'], dest: 'media/vendor/tinymce/themes/', filter: 'isFile'},
					// tinyMCE js files
					{ expand: true, cwd: '<%= folder.node_module %>tinymce/', src: ['tinymce.js','tinymce.min.js','license.txt','changelog.txt'], dest: 'media/vendor/tinymce/', filter: 'isFile'},
					// Code mirror addon files
					{ expand: true, cwd: '<%= folder.node_module %>codemirror/addon/', src: ['**'], dest: 'media/vendor/codemirror/addon/', filter: 'isFile'},
					// Code mirror keymap files
					{ expand: true, cwd: '<%= folder.node_module %>codemirror/keymap/', src: ['**'], dest: 'media/vendor/codemirror/keymap/', filter: 'isFile'},
					// Code mirror lib files
					{ expand: true, cwd: '<%= folder.node_module %>codemirror/lib', src: ['**'], dest: 'media/vendor/codemirror/lib/', filter: 'isFile'},
					// Code mirror mode files
					{ expand: true, cwd: '<%= folder.node_module %>codemirror/mode', src: ['**'], dest: 'media/vendor/codemirror/mode/', filter: 'isFile'},
					// Code mirror theme files
					{ expand: true, cwd: '<%= folder.node_module %>codemirror/theme', src: ['**'], dest: 'media/vendor/codemirror/theme/', filter: 'isFile'},
					// Media Element js, swf, xap files
					{ expand: true, cwd: '<%= folder.node_module %>mediaelement/build', src: ['*.js', '*.swf', '*.xap', '!jquery.js'], dest: 'media/vendor/mediaelement/js/', filter: 'isFile'},
					// Media Element css, png, gif, svg files
					{ expand: true, cwd: '<%= folder.node_module %>mediaelement/build', src: ['*.css', '*.png', '*.svg', '*.gif'], dest: 'media/vendor/mediaelement/css/', filter: 'isFile'},
					// MiniColors js files
					{ expand: true, cwd: '<%= folder.node_module %>jquery-minicolors', src: ['*.js'], dest: 'media/vendor/minicolors/js/', filter: 'isFile'},
					// MiniColors css, ong files
					{ expand: true, cwd: '<%= folder.node_module %>jquery-minicolors', src: ['*.css', '*.png'], dest: 'media/vendor/minicolors/css/', filter: 'isFile'},
					// Awesomplete js files
					{ expand: true, cwd: '<%= folder.node_module %>awesomplete', src: ['awesomplete.js', 'awesomplete.min.js'], dest: 'media/vendor/awesomplete/js/', filter: 'isFile'},
					// Awesomplete css files
					{ expand: true, cwd: '<%= folder.node_module %>awesomplete', src: ['awesomplete.css'], dest: 'media/vendor/awesomplete/css/'},
					// Dragula js files
					{ expand: true, cwd: '<%= folder.node_module %>dragula/dist', src: ['*.js'], dest: 'media/vendor/dragula/js/', filter: 'isFile'},
					// Dragula css files
					{ cwd: '<%= folder.node_module %>dragula/dist', src: ['*.css'], dest: 'media/vendor/dragula/css/', expand: true, filter: 'isFile'},
					// perfect-scrollbar js files
					{ expand: true, cwd: '<%= folder.node_module %>perfect-scrollbar/dist/js', src: ['*.js'], dest: 'media/vendor/perfect-scrollbar/js/', filter: 'isFile'},
					// perfect-scrollbar css files
					{ cwd: '<%= folder.node_module %>perfect-scrollbar/dist/css', src: ['*.css'], dest: 'media/vendor/perfect-scrollbar/css/', expand: true, filter: 'isFile'},
					// flying-focus js files
					{ expand: true, cwd: '<%= folder.node_module %>flying-focus-a11y/src/js', src: ['*.js'], dest: 'media/vendor/flying-focus-a11y/js/', filter: 'isFile'},
					// perfect-scrollbar scss files
					{ cwd: '<%= folder.node_module %>flying-focus-a11y/src/scss', src: ['*.scss'], dest: 'media/vendor/flying-focus-a11y/scss/', expand: true, filter: 'isFile'},

					// Licenses
					{ src: ['<%= folder.node_module %>jquery/LICENSE.txt'], dest: 'media/vendor/jquery/LICENSE.txt'},
					{ src: ['<%= folder.node_module %>bootstrap/LICENSE'], dest: 'media/vendor/bootstrap/LICENSE'},
					{ src: ['<%= folder.node_module %>tether/LICENSE'], dest: 'media/vendor/tether/LICENSE'},
					{ src: ['<%= folder.node_module %>codemirror/LICENSE'], dest: 'media/vendor/codemirror/LICENSE'},
					{ src: ['<%= folder.node_module %>jcrop/jcrop-MIT-LICENSE.txt'], dest: 'media/vendor/jcrop/jcrop-MIT-LICENSE.txt'},
					{ src: ['<%= folder.node_module %>dragula/license'], dest: 'media/vendor/dragula/license'},
					{ src: ['<%= folder.node_module %>awesomplete/LICENSE'], dest: 'media/vendor/awesomplete/LICENSE'},
					{ src: ['<%= folder.node_module %>perfect-scrollbar/LICENSE'], dest: 'media/vendor/perfect-scrollbar/LICENSE'},
					{ src: ['<%= folder.node_module %>flying-focus-a11y/MIT-LICENSE'], dest: 'media/vendor/flying-focus-a11y/MIT-LICENSE'},
				]
			}
		},

		// Compile Sass source files to CSS
		sass: {
			dist: {
				options: {
					precision: '5',
					sourceMap: true // SHOULD BE FALSE FOR DIST
				},
				files: {
					'<%= folder.adminTemplate %>/css/template.css': '<%= folder.adminTemplate %>/scss/template.scss'
				}
			}
		},

		// Validate the SCSS
		scsslint: {
			allFiles: [
				'<%= folder.adminTemplate %>/scss',
			],
			options: {
				config: 'scss-lint.yml',
				reporterOutput: '<%= folder.adminTemplate %>/scss/scss-lint-report.xml'
			}
		},

		// Minimize some javascript files
		uglify: {
			allJs: {
				files: [
					{
						src: [
							'<%= folder.system %>/*.js',
							'!<%= folder.system %>/*.min.js',
							'<%= folder.system %>/fields/*.js',
							'!<%= folder.system %>/fields/*.min.js',
							'<%= folder.system %>/legacy/*.js',
							'!<%= folder.system %>/legacy/*.min.js',
							'<%= folder.codemirror %>/addon/*/*.js',
							'!<%= folder.codemirror %>/addon*/*.min.js',
							'<%= folder.codemirror %>/keymap/*.js',
							'!<%= folder.codemirror %>/keymap/*.min.js',
							'<%= folder.codemirror %>/lib/*.js',
							'!<%= folder.codemirror %>/lib/*.min.js',
							'<%= folder.codemirror %>/mode/*/*.js',
							'!<%= folder.codemirror %>/mode/*/*.min.js',
							'<%= folder.codemirror %>/theme/*/*.js',
							'!<%= folder.codemirror %>/theme/*/*.min.js',
							'<%= folder.vendor %>/flying-focus-a11y/js/*.js',
							// '<%= folder.puny %>/*.js',            // Uglifying punicode.js fails!!!
							// '!<%= folder.puny %>/*.min.js',       // Uglifying punicode.js fails!!!
						],
						dest: '',
						expand: true,
						ext: '.min.js'
					}
				]
			},
			templates: {
				files: [
					{
						src: [
							'<%= folder.adminTemplate %>/*.js',
						],
						dest: '',
						expand: true,
						ext: '.min.js'
					}
				]
			}
		},

		// Initiate task after CSS is generated
		postcss: {
			options: {
				map: false,
				processors: [
					require('autoprefixer')({browsers: 'last 2 versions'})
				],
			},
			dist: {
				src: '<%= folder.adminTemplate %>/css/template.css'
			}
		},

		// Let's minify some css files
		cssmin: {
			allCss: {
				files: [{
					expand: true,
					matchBase: true,
					ext: '.min.css',
					cwd: 'media/vendor/codemirror',
					src: ['*.css', '!*.min.css', '!theme/*.css'],
					dest: 'media/vendor/codemirror',
				}]
			},
			templates: {
				files: [{
					expand: true,
					matchBase: true,
					ext: '.min.css',
					cwd: '<%= folder.adminTemplate %>/css',
					src: ['*.css', '!*.min.css', '!theme/*.css'],
					dest: '<%= folder.adminTemplate %>/css',
				}]
			}
		},
	});

	// Load required modules
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-scss-lint');
	grunt.loadNpmTasks('grunt-sass');
	grunt.loadNpmTasks('grunt-shell');
	grunt.loadNpmTasks('grunt-postcss');

	grunt.registerTask('default',
		[
			'clean:assets',
			'shell:update',
			'concat:someFiles',
			'copy:fromSource',
			'sass:dist',
			'uglify:allJs',
			'cssmin:allCss',
			'postcss',
			'cssmin:templates',
			'updateXML',
			'clean:temp'
		]
	);

	grunt.registerTask('test-scss', ['scsslint']);

	grunt.registerTask('updateXML', 'Update XML for tinyMCE and Codemirror', function() {
		// Update the XML files for tinyMCE and Codemirror
		tinyXml = grunt.file.read('plugins/editors/tinymce/tinymce.xml');
		codemirrorXml = grunt.file.read('plugins/editors/codemirror/codemirror.xml');

		tinyXml = tinyXml.replace(xmlVersionStr, "$1" + settings.vendors.tinymce.version + "$3");
		codemirrorXml = codemirrorXml.replace(xmlVersionStr, "$1" + settings.vendors.codemirror.version + "$3");

		grunt.file.write('plugins/editors/tinymce/tinymce.xml', tinyXml);
		grunt.file.write('plugins/editors/codemirror/codemirror.xml', codemirrorXml);
	});

	grunt.registerTask('scripts', 'Minifies the javascript files.', function() {
		grunt.task.run([
			'uglify:allJs'
		]);
	});

	grunt.registerTask('styles', 'Minifies the stylesheet files.', function() {
		grunt.task.run([
			'cssmin:allCss'
		]);
	});

	grunt.registerTask('compile', 'Compiles the stylesheet files.', function() {
		grunt.task.run([
			'uglify:templates',
			'scsslint',
			'sass:dist',
			'postcss',
			'cssmin:templates'
		]);
	 });

};
