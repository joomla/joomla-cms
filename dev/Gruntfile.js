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
		copy: {
			transfer: {
				files: [
					{ // jQuery files
						expand: true,
						cwd: 'assets/node_modules/jquery/dist/',
						src: ['**'],
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
					// // Licenses
					// { // jQuery files
					// 	src: ['assets/node_modules/jquery/LICENSE.txt'],
					// 	dest: '../media/vendor/jquery/'
					// },
					// { // Bootstrap files
					// 	src: [
					// 		'assets/node_modules/bootstrap/LICENSE',
					// 	],
					// 	dest: '../media/vendor/bootstrap/'
					// },
					// { // Bootstrap files
					// 	src: [
					// 		'assets/node_modules/bootstrap/node_modules/tether/LICENSE',
					// 	],
					// 	dest: '../media/vendor/tether/LICENSE',
					// },
				]
			}
		}
	});

	// Load required modules
	grunt.loadNpmTasks('grunt-contrib-uglify');

	grunt.loadNpmTasks('grunt-contrib-copy');

	grunt.registerTask('default', ['build', 'transfer']);

	grunt.registerTask('build', ['uglify']);

	grunt.registerTask('transfer', ['copy']);
};