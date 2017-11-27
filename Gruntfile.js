module.exports = function(grunt) {
	var settings      = grunt.file.readYAML('grunt-settings.yaml'),
		path          = require('path'),
		preText       = `{
	"name": "joomla-assets",
	"version": "4.0.0",
	"description": "External assets that Joomla is using",
	"dependencies": {
`,
		postText      = `
	},
	"license": "GPL-2.0+"
}`,
		name, tinyXml, codemirrorXml,
		vendorsTxt    = '',
		vendorsArr    = '',
		xmlVersionStr = /(<version>)(\d+.\d+.\d+)(<\/version>)/;

	// Loop to get some text for the packgage.json
	for (name in settings.vendors) {
		vendorsTxt += `
		"` + name + '": "' + settings.vendors[name].version + `",`;
	}

	// Loop to get some text for the assets.php
	for (name in settings.vendors) {
		vendorsArr += `'` + name + `' => array('version' => '` + settings.vendors[name].version + `',` + `'dependencies' => '` + settings.vendors[name].dependencies + `'),`;
	}

	// Build the package.json and assets.php for all 3rd Party assets
	grunt.file.write('build/assets_tmp/package.json', preText + vendorsTxt.substring(0, vendorsTxt.length - 1) + postText);

	// Project configuration.
	grunt.initConfig({
		folder : {
			media         : 'media',
			editors       : 'media/editors',
			system        : 'media/system/js',
			fields        : 'media/system/js/fields',
			legacy        : 'media/system/js/legacy',
			vendor        : 'media/vendor',
			puny          : 'media/vendor/punycode/js',
			codemirror    : 'media/vendor/codemirror',
			adminTemplate : 'administrator/templates/atum',
			installTemplate : 'installation/template',
			siteTemplate  : 'templates/cassiopeia',
			node_module   : 'build/assets_tmp/node_modules/',
		},

		// Let's clean up the system
		clean: {
			assets: {
				src: [
					'media/vendor/jquery/js/*',
					'media/vendor/bootstrap/**',
					'media/vendor/popper/**',
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
					'media/vendor/diff/**',
					'media/vendor/polyfills/**',
				],
				expand: true,
				options: {
					force: true
				}
			},
			temp: {
				src: [
					'build/assets_tmp/*',
					'build/assets_tmp/tmp',
					'build/assets_tmp/package.json'
				],
				expand: true,
				options: { force: true } 
			},
			css: {
				src: [
					'<%= folder.adminTemplate %>/css/font-awesome.css',
					'<%= folder.adminTemplate %>/css/bootstrap.css',
					'<%= folder.adminTemplate %>/css/template.css',
					'<%= folder.adminTemplate %>/css/template-rtl.css',
				],
				expand: true
			},
			allMinJs: [
				'media/**/*.min.js', '!media/vendor/*.min.js',
				'media/**/**/*.min.js', '!media/system/webcomponents/*.min.js',
				'!media/vendor/**/*.min.js',
				'media/**/**/**/*.min.js', '!media/vendor/**/**/*.min.js',
				'media/**/**/**/**/*.min.js', '!media/vendor/**/**/**/*.min.js'
			]
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
					// Bootastrap js files
					{ expand: true, cwd: '<%= folder.node_module %>bootstrap/dist/js/', src: ['**'], dest: 'media/vendor/bootstrap/js/', filter: 'isFile'},
					// Bootastrap scss files
					{ expand: true, cwd: '<%= folder.node_module %>bootstrap/scss/', src: ['**'], dest: 'media/vendor/bootstrap/scss/', filter: 'isFile'},
					// Bootastrap css files
					{ expand: true, cwd: '<%= folder.node_module %>bootstrap/dist/css/', src: ['**'], dest: 'media/vendor/bootstrap/css/', filter: 'isFile'},
					// Popper js files
					{ expand: true, cwd: '<%= folder.node_module %>popper.js/dist/umd/', src: ['*.js'], dest: 'media/vendor/popper/js/', filter: 'isFile'},
					// Punycode js files
					{ expand: true, cwd: '<%= folder.node_module %>punycode/', src: ['punycode.js', 'LICENSE-MIT.txt'], dest: 'media/vendor/punycode/js/', filter: 'isFile'},
					// Cropperjs css files
					{ expand: true, cwd: '<%= folder.node_module %>cropperjs/dist', src: ['*.css'], dest: 'media/vendor/cropperjs/css/', filter: 'isFile'},
					// Cropperjs js files
					{ expand: true, cwd: '<%= folder.node_module %>cropperjs/dist', src: ['*.js'], dest: 'media/vendor/cropperjs/js/', filter: 'isFile'},
					// Font Awesome css files
					{ expand: true, cwd: '<%= folder.node_module %>font-awesome/css/', src: ['**'], dest: 'media/vendor/font-awesome/css/', filter: 'isFile'},
					// Font Awesome scss files
					{ expand: true, cwd: '<%= folder.node_module %>font-awesome/scss/', src: ['**'], dest: 'media/vendor/font-awesome/scss/', filter: 'isFile'},
					// Font Awesome fonts files
					{ expand: true, cwd: '<%= folder.node_module %>font-awesome/fonts/', src: ['**'], dest: 'media/vendor/font-awesome/fonts/', filter: 'isFile'},
					// TinyMCE plugins
					{ expand: true, cwd: '<%= folder.node_module %>tinymce/plugins/', src: ['**'], dest: 'media/vendor/tinymce/plugins/', filter: 'isFile'},
					// TinyMCE skins
					{ expand: true, cwd: '<%= folder.node_module %>tinymce/skins/', src: ['**'], dest: 'media/vendor/tinymce/skins/', filter: 'isFile'},
					// TinyMCE themes
					{ expand: true, cwd: '<%= folder.node_module %>tinymce/themes/', src: ['**'], dest: 'media/vendor/tinymce/themes/', filter: 'isFile'},
					// TinyMCE js files
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
					{ expand: true, cwd: '<%= folder.node_module %>@claviska/jquery-minicolors/', src: ['jquery.minicolors.js','jquery.minicolors.min.js'], dest: 'media/vendor/minicolors/js/', filter: 'isFile'},
					// MiniColors css, ong files
					{ expand: true, cwd: '<%= folder.node_module %>@claviska/jquery-minicolors', src: ['*.css', '*.png'], dest: 'media/vendor/minicolors/css/', filter: 'isFile'},
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
					// flying-focus scss files
					{ cwd: '<%= folder.node_module %>flying-focus-a11y/src/scss', src: ['*.scss'], dest: 'media/vendor/flying-focus-a11y/scss/', expand: true, filter: 'isFile'},
					// flying-focus js files
					{ expand: true, cwd: '<%= folder.node_module %>flying-focus-a11y/src/js', src: ['*.js'], dest: 'media/vendor/flying-focus-a11y/js/', filter: 'isFile'},
					// JSDiff js files
					{ expand: true, cwd: '<%= folder.node_module %>diff/dist', src: ['*.js'], dest: 'media/vendor/diff/js/', filter: 'isFile'},
					// XPath polyfill js files
					{ expand: false, src: '<%= folder.node_module %>wicked-good-xpath/dist/wgxpath.install.js', dest: 'media/vendor/polyfills/js/polyfill-wgxpath.js', filter: 'isFile'},

					// Licenses
					{ src: ['<%= folder.node_module %>jquery/LICENSE.txt'], dest: 'media/vendor/jquery/LICENSE.txt'},
					{ src: ['<%= folder.node_module %>bootstrap/LICENSE'], dest: 'media/vendor/bootstrap/LICENSE'},
					{ src: ['<%= folder.node_module %>tether/LICENSE'], dest: 'media/vendor/tether/LICENSE'},
					{ src: ['<%= folder.node_module %>codemirror/LICENSE'], dest: 'media/vendor/codemirror/LICENSE'},
					{ src: ['<%= folder.node_module %>jcrop/jcrop-MIT-LICENSE.txt'], dest: 'media/vendor/jcrop/jcrop-MIT-LICENSE.txt'},
					{ src: ['<%= folder.node_module %>dragula/license'], dest: 'media/vendor/dragula/license'},
					{ src: ['<%= folder.node_module %>awesomplete/LICENSE'], dest: 'media/vendor/awesomplete/LICENSE'},
					{ src: ['<%= folder.node_module %>perfect-scrollbar/LICENSE'], dest: 'media/vendor/perfect-scrollbar/LICENSE'},
					{ src: ['<%= folder.node_module %>flying-focus-a11y/MIT-LICENSE.txt'], dest: 'media/vendor/flying-focus-a11y/MIT-LICENSE.txt'},
					{ src: ['<%= folder.node_module %>diff/LICENSE'], dest: 'media/vendor/diff/LICENSE'},
					{ src: ['<%= folder.node_module %>wicked-good-xpath/LICENSE'], dest: 'media/vendor/polyfills/wicked-good-xpath-LICENSE'},
				]
			},
			webcomponents: {
				files: [
					// Joomla UI custom elements js files
					{ expand: true, cwd: '<%= folder.node_module %>joomla-ui-custom-elements/dist', src: ['**'], dest: 'media/vendor/joomla-custom-elements/', filter: 'isFile'},
				]
			}
		},

		// Compile Sass source files to CSS
		sass: {
			site: {
				options: {
					precision: '5',
					sourceMap: true // SHOULD BE FALSE FOR DIST
				},
				files: {
					'<%= folder.adminTemplate %>/css/template.css': '<%= folder.adminTemplate %>/scss/template.scss',
					'<%= folder.siteTemplate %>/css/template.css' : '<%= folder.siteTemplate %>/scss/template.scss',
				}
			},
			admin: {
				options: {
					precision: '5',
					sourceMap: true // SHOULD BE FALSE FOR DIST
				},
				files: {
					'<%= folder.adminTemplate %>/css/font-awesome.css' : '<%= folder.adminTemplate %>/scss/font-awesome.scss',
					'<%= folder.adminTemplate %>/css/bootstrap.css'    : '<%= folder.adminTemplate %>/scss/bootstrap.scss',
					'<%= folder.adminTemplate %>/css/template.css'     : '<%= folder.adminTemplate %>/scss/template.scss',
					'<%= folder.adminTemplate %>/css/template-rtl.css' : '<%= folder.adminTemplate %>/scss/template-rtl.scss',
				}
			}
		},

		// Validate the SCSS
		scsslint: {
			site: {
				options: {
					config: 'scss-lint.yml',
					reporterOutput: 'scss-lint-report.xml'
				},
				src: [
					'<%= folder.siteTemplate %>/scss'
				]
			},
			admin: {
				options: {
					config: 'scss-lint.yml',
					reporterOutput: 'scss-lint-report.xml'
				},
				src: [
					'<%= folder.adminTemplate %>/scss'
				]
			}
		},

		// Minimize some javascript files
		uglify: {
			allJs: {
				files: [
					{
						src: [
							/**
							 *  EXCLUSIONS
							 *
							 * '<%= folder.puny %>/*.js', '!<%= folder.puny %>/*.min.js', // Uglifying punicode.js fails ES6!!!
							 *
							 * Please DO NOT MINIFY the webcomponents folder here!!! They're already minified!
							 * '<%= folder.system %>/polyfills/webcomponents/*.js', '!<%= folder.system %>/polyfills/webcomponents/*.min.js',
							 * '<%= folder.media %>/system/webcomponents/*.js', '!<%= folder.media %>/system/webcomponents/*.min.js',
							 */

							'<%= folder.system %>/*.js',
							'!<%= folder.system %>/*.min.js',
							'<%= folder.system %>/fields/*.js',
							'!<%= folder.system %>/fields/*.min.js',
							'<%= folder.system %>/fields/calendar-locales/date/*/*.js',
							'!<%= folder.system %>/fields/calendar-locales/date/*/*.min.js',
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
							'<%= folder.editors %>/none/js/*.js',
							'!<%= folder.editors %>/none/js/*.min.js',
							'<%= folder.editors %>/tinymce/js/*.js',
							'!<%= folder.editors %>/tinymce/js/*.min.js',
							'<%= folder.editors %>/codemirror/js/*.js',
							'!<%= folder.editors %>/codemirror/js/*.min.js',
							'<%= folder.media %>/com_associations/js/*.js',
							'!<%= folder.media %>/com_associations/js/*.min.js',
							'<%= folder.media %>/com_contact/js/*.js',
							'!<%= folder.media %>/com_contact/js/*.min.js',
							'<%= folder.media %>/com_content/js/*.js',
							'!<%= folder.media %>/com_content/js/*.min.js',
							'<%= folder.media %>/com_contenthistory/js/*.js',
							'!<%= folder.media %>/com_contenthistory/js/*.min.js',
							'<%= folder.media %>/com_finder/js/*.js',
							'!<%= folder.media %>/com_finder/js/*.min.js',
							'<%= folder.media %>/com_joomlaupdate/js/*.js',
							'!<%= folder.media %>/com_joomlaupdate/js/*.min.js',
							'<%= folder.media %>/com_languages/js/*.js',
							'!<%= folder.media %>/com_languages/js/*.min.js',
							'<%= folder.media %>/com_fields/js/*.js',
							'!<%= folder.media %>/com_fields/js/*.min.js',
							'<%= folder.media %>/com_menus/js/*.js',
							'!<%= folder.media %>/com_menus/js/*.min.js',
							'<%= folder.media %>/com_modules/js/*.js',
							'!<%= folder.media %>/com_modules/js/*.min.js',
							'<%= folder.media %>/com_wrapper/js/*.js',
							'!<%= folder.media %>/com_wrapper/js/*.min.js',
							'<%= folder.media %>/editors/none/js/*.js',
							'!<%= folder.media %>/editors/none/js/*.min.js',
							'<%= folder.media %>/editors/tinymce/js/*.js',
							'!<%= folder.media %>/editors/tinymce/js/*.min.js',
							'<%= folder.media %>/editors/codemirror/js/*.js',
							'!<%= folder.media %>/editors/codemirror/js/*.min.js',
							'<%= folder.media %>/editors/tinymce/js/plugins/dragdrop/*.js',
							'!<%= folder.media %>/editors/tinymce/js/plugins/dragdrop/*.min.js',
							'<%= folder.media %>/contacts/**/js/*.min.js',
							'<%= folder.media %>/jui/js/*.js',
							'!<%= folder.media %>/jui/js/*.min.js',
							'<%= folder.media %>/media/js/*.js',
							'!<%= folder.media %>/media/js/*.min.js',
							'<%= folder.media %>/mod_languages/js/*.js',
							'!<%= folder.media %>/mod_languages/js/*.min.js',
							'<%= folder.media %>/plg_captcha_recaptcha/js/*.js',
							'!<%= folder.media %>/plg_captcha_recaptcha/js/*.min.js',
							'<%= folder.media %>/plg_quickicon_extensionupdate/js/*.js',
							'!<%= folder.media %>/plg_quickicon_extensionupdate/js/*.min.js',
							'<%= folder.media %>/plg_quickicon_joomlaupdate/js/*.js',
							'!<%= folder.media %>/plg_quickicon_joomlaupdate/js/*.min.js',
							'<%= folder.media %>/plg_system_highlight/js/*.js',
							'!<%= folder.media %>/plg_system_highlight/js/*.min.js',
							'<%= folder.media %>/plg_system_stats/js/*.js',
							'!<%= folder.media %>/plg_system_stats/js/*.min.js',
							'<%= folder.media %>/plg_system_debug/js/*.js',
							'!<%= folder.media %>/plg_system_debug/js/*.min.js',
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
							'<%= folder.siteTemplate %>/*.js',
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
					require('autoprefixer')({
						browsers: [
							settings.Browsers
						]
					})
				],
			},
			site: {
				src: [
					'<%= folder.siteTemplate %>/css/template.css',
				]
			},
			admin: {
				src: [
					'<%= folder.adminTemplate %>/css/bootstrap.css',
					'<%= folder.adminTemplate %>/css/font-awesome.css',
					'<%= folder.adminTemplate %>/css/template.css',
					'<%= folder.adminTemplate %>/css/template-rtl.css',
				]
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
					src: [
						'*.css',
						'!*.min.css'
					],
					dest: 'media/vendor/codemirror',
				}]
			},
			site: {
				files: [{
					expand: true,
					matchBase: true,
					ext: '.min.css',
					cwd: '<%= folder.siteTemplate %>/css',
					src: [
						'*.css',
						'!user.css',
						'!*.min.css'
					],
					dest: '<%= folder.siteTemplate %>/css',
				}]
			},
			installTemplate: {
				files: [{
					expand: true,
					matchBase: true,
					ext: '.min.css',
					cwd: '<%= folder.installTemplate %>/css',
					src: [
						'*.css',
						'!*.min.css',
						'!theme/*.css'
					],
					dest: '<%= folder.installTemplate %>/css',
				}]
			},
			admin: {
				files: [{
					expand: true,
					matchBase: true,
					ext: '.min.css',
					cwd: '<%= folder.adminTemplate %>/css',
					src: [
						'*.css',
						'!user.css',
						'!*.min.css'
					],
					dest: '<%= folder.adminTemplate %>/css',
				}]
			}
		},

		// Watch files for changes and run tasks based on the changed files
		watch: {
			siteTemplate: {
				files: [
					'<%= folder.adminTemplate %>/**/*.scss',
					'<%= folder.siteTemplate %>/**/*.scss',
					'media/system/scss/**/*.scss',
				],
				tasks: ['compile:site']
			},
			adminTemplate: {
				files: [
					'<%= folder.adminTemplate %>/**/*.scss',
					'media/system/scss/**/*.scss',
				],
				tasks: ['compile:admin']
			},
			gruntfile: {
				files: ['Gruntfile.js']
			}
		}
	});

	/**
	 * Webcomponents polyfills start
	 */
	grunt.registerTask('polyfills-wc', 'Create a copy of the polyfills', () => {
		// Copy polyfills in the system/polyfills directory
		if (grunt.file.exists('node_modules/@webcomponents/webcomponentsjs/custom-elements-es5-adapter.js')) {
			let polyfills = ['webcomponents-hi-ce', 'webcomponents-hi-sd-ce', 'webcomponents-hi', 'webcomponents-lite', 'webcomponents-sd-ce'];

			polyfills.forEach((polyfill) => {
				// Put a copy of webcomponentjs polyfills in the dist folder
				grunt.config.set('copy.' + polyfill + '.files', [{
					src: 'node_modules/@webcomponents/webcomponentsjs/' + polyfill + '.js',
					dest: 'media/system/js/polyfills/webcomponents/' + polyfill + '.js'
				}]);

				grunt.task.run('copy:' + polyfill);

				// Put a copy of webcomponentjs polyfills maps in the dist folder
				grunt.config.set('copy.' + polyfill + '-map.files', [{
					src: 'node_modules/@webcomponents/webcomponentsjs/' + polyfill + '.js.map',
					dest: 'media/system/js/polyfills/webcomponents/' + polyfill + '.js.map'
				}]);

				grunt.task.run('copy:' + polyfill + '-map');
			})


			// Patch the Custom Element Polyfill to add the WebComponentsReady event
			grunt.registerTask('patchCE', 'Patch Custom Elements Polyfill', () => {
				// Patch the Custom Element polyfill
				console.log(grunt.file.read('node_modules/@webcomponents/custom-elements/custom-elements.min.js'))
				if (grunt.file.exists('node_modules/@webcomponents/custom-elements/custom-elements.min.js')) {
					let ce = grunt.file.read('node_modules/@webcomponents/custom-elements/custom-elements.min.js');
					console.log(ce)
					ce = ce.replace('//# sourceMappingURL=custom-elements.min.js.map', `
(function(){
	window.WebComponents = window.WebComponents || {};
	requestAnimationFrame(function() {
		window.WebComponents.ready= true;
		document.dispatchEvent(new CustomEvent("WebComponentsReady", { bubbles:true }) );
	})
})();
//# sourceMappingURL=custom-elements.js.map`);

					grunt.file.write('media/system/js/polyfills/webcomponents/webcomponents-ce.js', ce);
				}
			});

			// Copy the Custom Elements polyfill map
			grunt.config.set('copy.ce-map.files', [{
				src: 'node_modules/@webcomponents/custom-elements/custom-elements.min.js.map',
				dest: 'media/system/js/polyfills/webcomponents/webcomponents-ce.js.map'
			}]);

			grunt.registerTask('all-ce', ['patchCE', 'copy:ce-map']);
			// grunt.task.run('copy:ce-map');
			grunt.task.run('all-ce');
		}

		// Uglify the polyfills
		grunt.config.set('uglify.polyfills-js.files', [{
			src: ['!media/system/js/polyfills/webcomponents/*.min.js', 'media/system/js/polyfills/webcomponents/*.js'],
			dest: '',
			ext: '.min.js',
			expand: true
		}]);

		grunt.task.run('uglify:polyfills-js');
	});
	/**
	 * Webcomponents polyfills end
	 */

	/**
	 * Custom Elements start
	 */
	// Compile the css
	grunt.registerTask('compile-ce', 'Compile css files', () => {
		const compileCss = (element) => {
			if (grunt.file.exists('build/webcomponents/scss/' + element + '/' + element + '.scss')) {
				// Compile the css files
				grunt.config.set('sass.' + element + '.files', [{
					src: 'build/webcomponents/scss/' + element + '/' + element + '.scss',
					dest: settings.webcomponents[element].css + '/joomla-' + element + '.css'
				}]);

				grunt.task.run('sass:' + element);

				// Autoprefix the CSS files
				grunt.config.set('postcss.' + element + '.files', [{
					map: false,
					processors: [
						require('autoprefixer')({
							browsers: [
								`grunt.settings.browsers`,
							]
						})
					],
					src: settings.webcomponents[element].css + '/joomla-' + element + '.css',
				}]);

				grunt.task.run('postcss:' + element);

				// Autoprefix the CSS files
				grunt.config.set('cssmin.' + element + '.files', [{
					src: settings.webcomponents[element].css + '/joomla-' + element + '.css',
					dest: settings.webcomponents[element].css + '/joomla-' + element + '.min.css'
				}]);

				grunt.task.run('cssmin:' + element);
			}
		};

		console.info('Build the custom elements stylesheets')
		for (name in settings.webcomponents) {
			compileCss(name);
		}
	});

	// Create the Custom Elements
	grunt.registerTask('createElements', 'Create the Custom Elemets', () => {
		// Create the custom element
		const createElement = (element) => {
			let tmpJs = '';

			if (grunt.file.exists('build/webcomponents/js/' + element + '/' + element + '.js')) {
				// Repeat
				tmpJs = grunt.file.read('build/webcomponents/js/' + element + '/' + element + '.js');
				grunt.file.write('build/webcomponents/js/' + element + '/' + element + '_es6.js', tmpJs);

				// Browserify the ES5 Element
				grunt.config.set('browserify.options', {
					"transform": [
						[
							"babelify",
							{
								"presets": [
									"es2015",
									"minify"
								],
								"plugins": [
									"static-fs"
								]
							}
						]
					]
				});

				// As custom elements (plain Js and css)
				grunt.config.set('browserify.' + element + '.files', [{
					dest: settings.webcomponents[element].js + '/joomla-' + element + '-es5.js',
					src: 'build/webcomponents/js/' + element + '/' + element + '_es6.js',
				}]);

				grunt.task.run('browserify:' + element);

				// Uglify the scripts
				grunt.config.set('uglify.' + element + '-js' + '.files', [{
					src: [settings.webcomponents[element].js + '/joomla-' + element + '-es5.js', '!' + settings.webcomponents[element].js +'/joomla-' + element + '.min.js'],
					dest: '',
					ext: '.min.js',
					expand: true
				}]);

				grunt.task.run('uglify:' + element + '-js');

				// Put an ES6 copy in the dist folder
				grunt.config.set('copy.' + element + '-es6' + '.files', [{
					src: 'build/webcomponents/js/' + element + '/' + element + '_es6.js',
					dest: settings.webcomponents[element].js + '/joomla-' + element + '.js'
				}]);

				grunt.task.run('copy:' + element + '-es6');

				// Uglify the ES6 script
				grunt.config.set('uglify.' + element + '-es6' + '.files', [{
					src: settings.webcomponents[element].js + '/joomla-' + element + '.js',
					dest: settings.webcomponents[element].js +'/joomla-' + element + '.min.js',
				}]);

				grunt.task.run('uglify:' + element + '-es6');

				// Remove the temporary file
				grunt.registerTask('deleteTmpCE', 'Delete temp files', () => {
					if (grunt.file.exists('build/webcomponents/js/' + element + '/' + element + '_es6.js')) {
						grunt.file.delete('build/webcomponents/js/' + element + '/' + element + '_es6.js');
					}
				});

				grunt.task.run('deleteTmpCE');
			}
		};

		console.info('Build the custom Elements')
		for (name in settings.webcomponents) {
			createElement(name);
		}
	});
	/**
	 * Custom Elements end
	 */

	// Load required modules
	grunt.loadNpmTasks('grunt-babel');
	grunt.loadNpmTasks('grunt-browserify');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-postcss-x');
	grunt.loadNpmTasks('grunt-sass');
	grunt.loadNpmTasks('grunt-scss-lint');
	grunt.loadNpmTasks('grunt-shell');

	grunt.registerTask('default',
		[
			'clean:assets',
			'shell:update',
			'concat:someFiles',
			'copy:fromSource',
			'sass:site',
			'sass:admin',
			'clean:allMinJs',
			'uglify:allJs',
			'copy:webcomponents',
			'cssmin:allCss',
			'postcss:site',
			'postcss:admin',
			'cssmin:site',
			'cssmin:admin',
			'clean:css',
			'updateXML',
			'clean:temp'
		]
	);

	grunt.registerTask('webcomponents', ['polyfills-wc', 'compile-ce', 'createElements']);

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
			'clean:allMinJs',
			'uglify:allJs'
		]);
	});

	grunt.registerTask('compile:site', 'Compiles the stylesheets files for the site template', function() {
		grunt.task.run([
			'uglify:templates',
			'scsslint:site',
			'sass:site',
			'postcss:site',
			'cssmin:site',
			'clean:css',
			'watch:siteTemplate'
		]);
	});

	grunt.registerTask('compile:admin', 'Compiles the stylesheets files for the admin template', function() {
		grunt.task.run([
			'uglify:templates',
			'scsslint:admin',
			'sass:admin',
			'postcss:admin',
			'cssmin:admin',
			'clean:css',
			'watch:adminTemplate'
		]);
	});

	grunt.registerTask('installation', 'Compiles the error-locales.js translation file', function() {

		// Set the initial template
		var template = `
window.errorLocale = {`;

		grunt.file.recurse('installation/language', function(abspath, rootdir, subdir, filename) {

			if (abspath.indexOf('.ini') > -1) {
				var fs = require('fs'), ini = require('ini'), languageStrings = ini.parse(fs.readFileSync(abspath, 'utf-8'));

				if (languageStrings["MIN_PHP_ERROR_LANGUAGE"]) {
					template = template + `
	"` + subdir + `": {
		"language": "` + languageStrings["MIN_PHP_ERROR_LANGUAGE"] + `",
		"header": "` + languageStrings["MIN_PHP_ERROR_HEADER"] + `",
		"text1": "` + languageStrings["MIN_PHP_ERROR_TEXT"] + `",
		"help-url-text": "` + languageStrings["MIN_PHP_ERROR_URL_TEXT"] + `"
	},`;
				}
			}
		});

		// Add the closing bracket
		template = template + `
}`;

		// Write the file
		grunt.file.write('templates/system/js/error-locales.js', template);
	});

};
