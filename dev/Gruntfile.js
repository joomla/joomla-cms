module.exports = function(grunt) {

	// Project configuration.
	grunt.initConfig({
		folder : {
			system : '../media/system/js',
			fields : '../media/system/js/fields'
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
				]
			}
		},
		clean: {
			old: {
				src: [
					'../media/vendor/jquery/js/*',
					'!../media/vendor/jquery/js/*jquery-noconflict.js*', // Joomla owned
					'../media/vendor/bootstrap/**',
					'../media/vendor/tether/**',
					'../media/vendor/font-awesome/**',
					'../media/vendor/tinymce/plugins/*',
					'../media/vendor/tinymce/skins/*',
					'../media/vendor/tinymce/themes/*',
					'!../media/vendor/tinymce/plugins/*jdragdrop*',  // Joomla owned
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
		// Licenses
					{ // jQuery
						expand: true,
						cwd: 'assets/node_modules/jquery/',
						src: ['LICENSE.txt'],
						dest: '../media/vendor/jquery/',
						filter: 'isFile'
					},
					{ // Bootstrap
						expand: true,
						cwd: 'assets/node_modules/bootstrap/',
						src: ['LICENSE'],
						dest: '../media/vendor/bootstrap/',
						filter: 'isFile'
					},
					{ // tether
						src: [
							'assets/node_modules/tether/LICENSE',
						],
						dest: '../media/vendor/tether/LICENSE',
					},
				]
			}
		}
	});

	// Load required modules
	grunt.loadNpmTasks('grunt-contrib-uglify');

	grunt.loadNpmTasks('grunt-contrib-clean');

	grunt.loadNpmTasks('grunt-contrib-copy');

	grunt.registerTask('default', ['build', 'transfer']);

	grunt.registerTask('build', ['uglify']);

	grunt.registerTask('old', ['clean']);

	grunt.registerTask('transfer', ['copy']);

};