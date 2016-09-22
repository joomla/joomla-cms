module.exports = function(grunt) {

	// Project configuration.
	grunt.initConfig({
		folder : {
			system : '../media/system/js',
			fields : '../media/system/js/fields',
			puny   : '../media/vendor/punycode/js'
		},
		// Download packages from github
		gitclone: {
			cloneCm: {
				options: {
					repository: 'https://github.com/codemirror/CodeMirror.git',
					branch: 'master',
					directory: 'assets/tmp/codemirror'
				}
			},
			cloneCombo: {
				options: {
					repository: 'https://github.com/danielfarrell/bootstrap-combobox.git',
					branch    : 'master',
					directory : 'assets/tmp/combobox'
				}
			}
		},
		uglify: {
			build: {
				files: [
					{
						src: ['<%= folder.system %>/*.js','!<%= folder.system %>/*.min.js'],
						dest: '',
						expand: true,
						ext: '.min.js'
					},
					{
						src: ['<%= folder.fields %>/*.js','!<%= folder.fields %>/*.min.js'],
						dest: '',
						expand: true,
						ext: '.min.js'
					}
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
		clean: {
			old: {
				src: [
					'assets/tmp/**',
					'../media/vendor/jquery/js/*',
					'!../media/vendor/jquery/js/*jquery-noconflict.js*', // Joomla owned
					'../media/vendor/bootstrap/**',
					'../media/vendor/tether/**',
					'../media/vendor/font-awesome/**',
					'../media/vendor/tinymce/plugins/*',
					'../media/vendor/tinymce/skins/*',
					'../media/vendor/tinymce/themes/*',
					'!../media/vendor/tinymce/plugins/*jdragdrop*',  // Joomla owned
					'../media/vendor/punycode/*',
				],
				expand: true,
				options: {
					force: true
				},
			},
		},
		copy: {
			transfer: {
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
					{ // Bootstrap-combobox
						expand: true,
						cwd: 'assets/tmp/combobox/js',
						src: 'bootstrap-combobox.js',
						dest: '../media/vendor/combobox/js/',
						filter: 'isFile'
					},
					{ // Bootstrap-combobox
						expand: true,
						cwd: 'assets/tmp/combobox/css',
						src: 'bootstrap-combobox.css',
						dest: '../media/vendor/combobox/css/',
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
						dest: '../media/codemirror/mode/',
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
				]
			}
		}
	});

	// Load required modules
	grunt.loadNpmTasks('grunt-contrib-uglify');

	grunt.loadNpmTasks('grunt-contrib-clean');

	grunt.loadNpmTasks('grunt-contrib-copy');

	grunt.loadNpmTasks('grunt-git');

	grunt.registerTask('cloneCm', ['gitclone']);

	grunt.registerTask('cloneCombo', ['gitclone']);

	grunt.registerTask('default', ['old', 'cloneCombo', 'transfer', 'build']);

	grunt.registerTask('build', ['uglify']);

	grunt.registerTask('old', ['clean']);

	grunt.registerTask('transfer', ['copy']);

};