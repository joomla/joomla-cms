module.exports = function(grunt) {

	var settings  = grunt.file.readYAML('settings.yaml'),
		path        = require('path'),
		preText     = '{\n "name": "joomla-assets",\n "version": "4.0.0",\n "description": "External assets that Joomla is using",\n "dependencies": {\n  ',
		postText    = '  },\n  "license": "GPL-2.0+"\n}',
		name,
		disabledNPM = ['jcrop', 'autocomplete', 'coddemirror'],
		vendorsTxt = '',
		vendorsArr = '',
		polyFillsUrls =[];

	for (name in settings.vendors) {
		if (disabledNPM.indexOf(name) < 0 ) {
			vendorsTxt += '"' + name + '": "' + settings.vendors[name].version + '",';
		}
	}

	for (name in settings.vendors) {
		vendorsArr += '\'' + name + '\' => array(\'version\' => \'' + settings.vendors[name].version + '\',' + '\'dependencies\' => \'' + settings.vendors[name].dependencies + '\'),\n\t\t\t';
	}

	// Build the array of the polyfills urls for curl
	for (name in settings.polyfills) {
		var filename = settings.polyfills[name].toLowerCase();
		if (filename === 'element.prototype.classlist') filename = 'classlist';
		polyFillsUrls.push({url: 'https://cdn.polyfill.io/v2/polyfill.js?features=' + settings.polyfills[name] + '&flags=always,gated&ua=Mozilla/4.0%20(compatible;%20MSIE%208.0;%20Windows%20NT%206.0;%20Trident/4.0)', localFile: 'polyfill.' + filename + '.js'});
	}

	// Build the package.json and assets.php for all 3rd Party assets
	grunt.file.write('assets/package.json', preText + vendorsTxt.substring(0, vendorsTxt.length - 1) + postText);
	grunt.file.write('assets.php', '<?php\ndefined(\'_JEXEC\') or die;\n\nabstract class ExternalAssets{\n\tpublic static function getCoreAssets() {\n\t\t return array(\n\t\t\t' + vendorsArr + '\n\t\t);\n\t}\n}\n');

	// Project configuration.
	grunt.initConfig({
		folder : {
			system   : '../media/system/js',
			fields   : '../media/system/js/fields',
			legacy   : '../media/system/js/legacy',
			puny     : '../media/vendor/punycode/js',
			cmadd    : '../media/vendor/codemirror/addon',
			cmkey    : '../media/vendor/codemirror/keymap',
			cmlib    : '../media/vendor/codemirror/lib',
			cmmod    : '../media/vendor/codemirror/mode',
			cmthem   : '../media/vendor/codemirror/theme',
			polyfills : '../media/vendor/polyfills/js',
			jupdate    : '../media/plg_quickicon_joomlaupdate/js',
			extupdate : '../media/plg_quickicon_extensionupdate/js',
		},

		// Let's clean up the system
		clean: {
			assets: {
				src: [
					'assets/node_modules/**',
					'assets/tmp/**',
					'../media/vendor/jquery/js/*',
					'!../media/vendor/jquery/js/*jquery-noconflict.js*', // Joomla owned
					'../media/vendor/bootstrap/**',
					'../media/vendor/tether/**',
					'../media/vendor/jcrop/**',
					'../media/vendor/dragula/**',
					'../media/vendor/font-awesome/**',
					'../media/vendor/tinymce/plugins/*',
					'../media/vendor/tinymce/skins/*',
					'../media/vendor/tinymce/themes/*',
					'!../media/vendor/tinymce/plugins/*jdragdrop*',  // Joomla owned
					'../media/vendor/punycode/*',
					'../media/vendor/codemirror/*',
					'../media/vendor/autocomplete/*',
					'../media/vendor/mediaelement/*',
					'../media/vendor/chosenjs/*',
					'../media/vendor/awesomplete/*',
					'../media/vendor/polyfills/*',
				],
				expand: true,
				options: {
					force: true
				},
			},
			tmp: {
				src: [
					'assets/',
				],
				expand: true,
				options: {
					force: true
				}
			}
		},
		// Update all the packages to the version specified in assets/package.json
		shell: {
			update: {
				command: [
					'cd assets',
					'npm install'
				].join('&&')
			}
		},
		// Get the latest codemirror
		curl: {
			'cmGet': {
				src: 'https://github.com/codemirror/CodeMirror/archive/' + settings.vendors.codemirror.version + '.zip',
				dest: 'assets/tmp/cmzip.zip'
			},
			'jCrop': {
				src: 'https://github.com/tapmodo/Jcrop/archive/v' + settings.vendors.jcrop.version + '.zip',
				dest: 'assets/tmp/jcrop.zip'
			},
			'autoComplete': {
				src: 'https://github.com/devbridge/jQuery-Autocomplete/archive/v' + settings.vendors.autocomplete.version + '.zip',
				dest: 'assets/tmp/autoc.zip'
			},
		},
		fetchpages: {
			polyfills: {
				options: {
					baseURL: '',
					destinationFolder: '../media/vendor/polyfills/js/',
					urls: polyFillsUrls,
					cleanHTML: false,
					fetchBaseURL: false,
					followLinks: false
				}
			}
		},
		unzip: {
			'cmUnzip': {
				router: function (filepath) {
					var re = new RegExp('CodeMirror-' + settings.vendors.codemirror.version + '/', 'g');
					var newFilename = filepath.replace(re, '');
					return newFilename;
				},
				src: 'assets/tmp/cmzip.zip',
				dest: 'assets/tmp/codemirror/'
			},
			'autoUnzip': {
				router: function (filepath) {
					var re = new RegExp('jQuery-Autocomplete-' + settings.vendors.autocomplete.version + '/', 'g');
					var newFilename = filepath.replace(re, '');
					return newFilename;
				},
				src: 'assets/tmp/autoc.zip',
				dest: 'assets/tmp/autocomplete/'
			},
			'jcropUnzip': {
				router: function (filepath) {
					var re = new RegExp(settings.vendors.jcrop.version + '/', 'g');
					var newFilename = filepath.replace(re, '');
					return newFilename;
				},
				src: 'assets/tmp/jcrop.zip',
				dest: 'assets/tmp/jcrop/'
			},
		},
		// Concatenate some javascript files
		concat: {
			someFiles: {
				files: [
					{
						src: settings.CmAddons.js.map(function (v) {
							return 'assets/tmp/codemirror/' + v;
						}),
						dest:'assets/tmp/codemirror/lib/addons.js'
					},
					{
						src: settings.CmAddons.css.map(function (v) {
							return 'assets/tmp/codemirror/' + v;
						}),
						dest: 'assets/tmp/codemirror/lib/addons.css'
					}
				]
			}
		},

		// Minimize some javascript files
		uglify: {
			allJs: {
				files: [
					{
						src: ['<%= folder.system %>/*.js','!<%= folder.system %>/*.min.js'],
						dest: '',
						expand: true,
						ext: '.min.js'
					},
					{
					 	src: [
							'<%= folder.fields %>/*.js',
							'!<%= folder.fields %>/*.min.js',
							'!<%= folder.fields %>/calendar.js',  // exclude calendar
							'!<%= folder.fields %>/calendar-*.js' // exclude calendar
						],
					 	dest: '',
					 	expand: true,
					 	ext: '.min.js'
					},
					{
					 	src: ['<%= folder.legacy %>/*.js', '!<%= folder.legacy %>/*.min.js'],
					 	dest: '',
					 	expand: true,
					 	ext: '.min.js'
					},
					{
						src: ['<%= folder.cmadd %>/*/*.js','!<%= folder.cmadd %>/*/*.min.js'],
						dest: '',
						expand: true,
						ext: '.min.js'
					},
					{
						src: ['<%= folder.cmkey %>/*.js','!<%= folder.cmkey %>/*.min.js'],
						dest: '',
						expand: true,
						ext: '.min.js'
					},
					{
						src: ['<%= folder.cmlib %>/*.js','!<%= folder.cmlib %>/*.min.js'],
						dest: '',
						expand: true,
						ext: '.min.js'
					},
					{
						src: ['<%= folder.cmmod %>/*/*.js','!<%= folder.cmmod %>/*/*.min.js'],
						dest: '',
						expand: true,
						ext: '.min.js'
					},
					{
						src: ['<%= folder.cmthem %>/*/*.js','!<%= folder.cmthem %>/*/*.min.js'],
						dest: '',
						expand: true,
						ext: '.min.js'
					},
					{
						src: ['<%= folder.jupdate %>/*.js','!<%= folder.jupdate %>/*.min.js'],
						dest: '',
						expand: true,
						ext: '.min.js'
					},
					{
						src: ['<%= folder.extupdate %>/*.js','!<%= folder.extupdate %>/*.min.js'],
						dest: '',
						expand: true,
						ext: '.min.js'
					},
					{
						src: '<%= folder.polyfills %>/polyfill.classlist.js',
						dest: '<%= folder.polyfills %>/polyfill.classlist.min.js',
					},
					{
						src: '<%= folder.polyfills %>/polyfill.event.js',
						dest: '<%= folder.polyfills %>/polyfill.event.min.js',
					},
					// Uglifying punicode.js fails!!!
					// {
					// 	src: ['<%= folder.puny %>/*.js','!<%= folder.puny %>/*.min.js'],
					// 	dest: '',
					// 	expand: true,
					// 	ext: '.min.js'
					// }
				]
			}
		},

		// Transfer all the assets to media/vendor
		copy: {
			fromSource: {
				files: [
					{ // jQuery files
						expand: true,
						cwd: 'assets/node_modules/jquery/dist/',
						src: ['*', '!(core.js)'],
						dest: '../media/vendor/jquery/js/',
						filter: 'isFile'
					},
					{ // jQuery migrate files
						expand: true,
						cwd: 'assets/node_modules/jquery-migrate/dist/',
						src: ['**'],
						dest: '../media/vendor/jquery/js/',
						filter: 'isFile'
					},
					{ //Bootastrap js files
						expand: true,
						cwd: 'assets/node_modules/bootstrap/dist/js/',
						src: ['**'],
						dest: '../media/vendor/bootstrap/js/',
						filter: 'isFile'
					},
					{ //Bootastrap scss files
						expand: true,
						cwd: 'assets/node_modules/bootstrap/scss/',
						src: ['**'],
						dest: '../media/vendor/bootstrap/scss/',
						filter: 'isFile'
					},
					{ //Bootastrap css files
						expand: true,
						cwd: 'assets/node_modules/bootstrap/dist/css/',
						src: ['**'],
						dest: '../media/vendor/bootstrap/css/',
						filter: 'isFile'
					},
					{ //Teether js files
						expand: true,
						cwd: 'assets/node_modules/tether/dist/js/',
						src: ['**'],
						dest: '../media/vendor/tether/js/',
						filter: 'isFile'
					},
					{ // Punycode
						expand: true,
						cwd: 'assets/node_modules/punycode/',
						src: ['punycode.js', 'LICENSE-MIT.txt'],
						dest: '../media/vendor/punycode/js/',
						filter: 'isFile'
					},
					{ // jcrop
						expand: true,
						cwd: 'assets/tmp/jcrop/jcrop-css',
						src: ['**'],
						dest: '../media/vendor/jcrop/css/',
						filter: 'isFile'
					},
					{ // jcrop
						expand: true,
						cwd: 'assets/tmp/jcrop/jcrop-js',
						src: ['jcrop.min.js', 'jcrop.js'],
						dest: '../media/vendor/jcrop/js/',
						filter: 'isFile'
					},
					{ // autocomplete
						expand: true,
						cwd: 'assets/tmp/autocomplete/dist',
						src: ['jquery.autocomplete.min.js', 'jquery.autocomplete.js', 'license.txt'],
						dest: '../media/vendor/autocomplete/js/',
						filter: 'isFile'
					},
					{ // chosen
						expand: true,
						cwd: 'assets/node_modules/chosenjs',
						src: ['chosen.css', 'chosen.min.js', 'chosen-sprite.png', 'chosen-sprite@2x.png'],
						dest: '../media/vendor/chosenjs/css/',
						filter: 'isFile'
					},
					{ // chosen
						expand: true,
						cwd: 'assets/node_modules/chosenjs',
						src: ['chosen.jquery.min.js', 'chosen.jquery.js'],
						dest: '../media/vendor/chosenjs/js/',
						filter: 'isFile'
					},
					{ //Font Awesome css files
						expand: true,
						cwd: 'assets/node_modules/font-awesome/css/',
						src: ['**'],
						dest: '../media/vendor/font-awesome/css/',
						filter: 'isFile'
					},
					{ //Font Awesome scss files
						expand: true,
						cwd: 'assets/node_modules/font-awesome/scss/',
						src: ['**'],
						dest: '../media/vendor/font-awesome/scss/',
						filter: 'isFile'
					},
					{ //Font Awesome fonts files
						expand: true,
						cwd: 'assets/node_modules/font-awesome/fonts/',
						src: ['**'],
						dest: '../media/vendor/font-awesome/fonts/',
						filter: 'isFile'
					},
					// tinyMCE
					{ // tinyMCE files
						expand: true,
						cwd: 'assets/node_modules/tinymce/plugins/',
						src: ['**'],
						dest: '../media/vendor/tinymce/plugins/',
						filter: 'isFile'
					},
					{ // tinyMCE files
						expand: true,
						cwd: 'assets/node_modules/tinymce/skins/',
						src: ['**'],
						dest: '../media/vendor/tinymce/skins/',
						filter: 'isFile'
					},
					{ // tinyMCE files
						expand: true,
						cwd: 'assets/node_modules/tinymce/themes/',
						src: ['**'],
						dest: '../media/vendor/tinymce/themes/',
						filter: 'isFile'
					},
					{ // tinyMCE files
						expand: true,
						cwd: 'assets/node_modules/tinymce/',
						src: ['tinymce.js','tinymce.min.js','license.txt','changelog.txt'],
						dest: '../media/vendor/tinymce/',
						filter: 'isFile'
					},
					// Code mirror
					{ // Code mirror files
						expand: true,
						cwd: 'assets/tmp/codemirror/addon/',
						src: ['**'],
						dest: '../media/vendor/codemirror/addon/',
						filter: 'isFile'
					},
					{ // Code mirror files
						expand: true,
						cwd: 'assets/tmp/codemirror/keymap/',
						src: ['**'],
						dest: '../media/vendor/codemirror/keymap/',
						filter: 'isFile'
					},
					{ // Code mirror files
						expand: true,
						cwd: 'assets/tmp/codemirror/lib',
						src: ['**'],
						dest: '../media/vendor/codemirror/lib/',
						filter: 'isFile'
					},
					{ // Code mirror files
						expand: true,
						cwd: 'assets/tmp/codemirror/mode',
						src: ['**'],
						dest: '../media/vendor/codemirror/mode/',
						filter: 'isFile'
					},
					{ // Code mirror files
						expand: true,
						cwd: 'assets/tmp/codemirror/theme',
						src: ['**'],
						dest: '../media/vendor/codemirror/theme/',
						filter: 'isFile'
					},
					// Licenses
					{ // jQuery
						src: ['assets/node_modules/jquery/LICENSE.txt'],
						dest: '../media/vendor/jquery/LICENSE.txt',
					},
					{ // jCrop
						src: ['assets/tmp/jcop/jcrop-MIT-LICENSE.txt'],
						dest: '../media/vendor/jcrop/jcrop-MIT-LICENSE.txt',
					},
					{ // Bootstrap
						src: ['assets/node_modules/bootstrap/LICENSE'],
						dest: '../media/vendor/bootstrap/LICENSE',
					},
					{ // tether
						src: ['assets/node_modules/tether/LICENSE'],
						dest: '../media/vendor/tether/LICENSE',
					},
					{ // Code mirror
						src: ['assets/tmp/codemirror/LICENSE'],
						dest: '../media/vendor/codemirror/LICENSE',
					},
					{ // Jcrop
						src: ['assets/tmp/jcrop/jcrop-MIT-LICENSE.txt'],
						dest: '../media/vendor/jcrop/jcrop-MIT-LICENSE.txt',
					},
					{ // Dragula
						src: ['assets/node_modules/dragula/license'],
						dest: '../media/vendor/dragula/license',
					},
					{ // Media Element
						expand: true,
						cwd: 'assets/node_modules/mediaelement/build',
						src: ['*.js', '*.swf', '*.xap', '!jquery.js'],
						dest: '../media/vendor/mediaelement/js/',
						filter: 'isFile'
					},
					{ // Media Element
						expand: true,
						cwd: 'assets/node_modules/mediaelement/build',
						src: ['*.css', '*.png', '*.svg', '*.gif'],
						dest: '../media/vendor/mediaelement/css/',
						filter: 'isFile'
					},
					{ // MiniColors
						expand: true,
						cwd: 'assets/node_modules/jquery-minicolors',
						src: ['*.js'],
						dest: '../media/vendor/minicolors/js/',
						filter: 'isFile'
					},
					{ // MiniColors
						expand: true,
						cwd: 'assets/node_modules/jquery-minicolors',
						src: ['*.css', '*.png'],
						dest: '../media/vendor/minicolors/css/',
						filter: 'isFile'
					},
					{ // Awesomplete
						expand: true,
						cwd: 'assets/node_modules/awesomplete',
						src: ['awesomplete.js', 'awesomplete.min.js'],
						dest: '../media/vendor/awesomplete/js/',
						filter: 'isFile'
					},
					{ // Awesomplete
						expand: true,
						cwd: 'assets/node_modules/awesomplete',
						src: ['awesomplete.css'],
						dest: '../media/vendor/awesomplete/css/',
					},
					{ // Dragula
						expand: true,
						cwd: 'assets/node_modules/dragula/dist',
						src: ['*.js'],
						dest: '../media/vendor/dragula/js/',
						filter: 'isFile'
					},
					{ // Dragula
						expand: true,
						cwd: 'assets/node_modules/dragula/dist',
						src: ['*.css'],
						dest: '../media/vendor/dragula/css/',
						filter: 'isFile'
					},
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
					cwd: '../media/vendor/codemirror',
					src: ['*.css', '!*.min.css', '!theme/*.css'],
					dest: '../media/vendor/codemirror',
				}]
			}
		}
	});

	// Load required modules
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-shell');
	grunt.loadNpmTasks('grunt-zip');
	grunt.loadNpmTasks('grunt-curl');
	grunt.loadNpmTasks('grunt-fetch-pages');

	grunt.registerTask('default',
		[
			'clean:assets',
			'shell:update',
			'curl:cmGet',
			'curl:jCrop',
			'curl:autoComplete',
			'fetchpages:polyfills',
			'unzip:cmUnzip',
			'unzip:autoUnzip',
			'unzip:jcropUnzip',
			'concat:someFiles',
			'copy:fromSource',
			'uglify:allJs',
			'cssmin:allCss',
			'clean:tmp',
		]
	);
	
	grunt.registerTask('minify', 'Minifies scripts and styles.', function() {
		grunt.task.run([
			'uglify:allJs',
			'cssmin:allCss',
			'clean:tmp',
		]);
	});
};
