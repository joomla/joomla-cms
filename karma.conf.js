// Karma configuration

module.exports = function (config) {
	config.set({

		// base path that will be used to resolve all patterns (eg. files, exclude)
		basePath: '',

		// frameworks to use
		// available frameworks: https://npmjs.org/browse/keyword/karma-adapter
		frameworks: ['jasmine-ajax', 'jasmine', 'requirejs'],

		// list of files / patterns to load in the browser
		files: [
			{pattern: 'tests/javascript/node_modules/jquery/dist/jquery.min.js', included: false},
			{pattern: 'tests/javascript/node_modules/jasmine-jquery/lib/jasmine-jquery.js', included: false},
			{pattern: 'tests/javascript/node_modules/text/text.js', included: false},
			{pattern: 'media/jui/js/bootstrap.min.js', included: false},
			{pattern: 'media/jui/js/jquery.ui.core.min.js', included: false},
			{pattern: 'media/jui/js/jquery.ui.sortable.min.js', included: false},
			{pattern: 'media/system/js/*.js', included: false},
			{pattern: 'media/system/js/legacy/*.js', included: false},
			{pattern: 'media/system/js/fields/*.js', included: false},
			{pattern: 'media/system/js/fields/calendar-locales/*.js', included: false},
			{pattern: 'media/system/js/fields/calendar-locales/date/gregorian/*.js', included: false},
			{pattern: 'tests/javascript/**/fixture.html', included: false},
			{pattern: 'tests/javascript/**/spec.js', included: false},
			{pattern: 'tests/javascript/**/spec-setup.js', included: false},
			{pattern: 'images/*.png', included: false},

			'tests/javascript/test-main.js'
		],

		// list of files to exclude
		exclude: [
			'media/system/js/*uncompressed.js'
		],

		// preprocess matching files before serving them to the browser
		// available preprocessors: https://npmjs.org/browse/keyword/karma-preprocessor
		preprocessors: {
			'**/system/js/*!(uncompressed).js': ['coverage']
		},

		// coverage reporter configuration
		coverageReporter: {
			type : 'text'
		},

		// test results reporter to use
		// possible values: 'dots', 'progress'
		// available reporters: https://npmjs.org/browse/keyword/karma-reporter
		reporters: ['verbose', 'coverage'],

		// web server port
		port: 9876,

		// enable / disable colors in the output (reporters and logs)
		colors: true,

		// level of logging
		// possible values: config.LOG_DISABLE || config.LOG_ERROR || config.LOG_WARN || config.LOG_INFO || config.LOG_DEBUG
		logLevel: config.LOG_INFO,

		// enable / disable watching file and executing tests whenever any file changes
		autoWatch: true,

		// start these browsers
		// available browser launchers: https://npmjs.org/browse/keyword/karma-launcher
		browsers: ['Firefox'],

		// Continuous Integration mode
		// if true, Karma captures browsers, runs the tests and exits
		singleRun: false,

		// list of plugins
		plugins: [
			'karma-jasmine',
			'karma-jasmine-ajax',
			'karma-firefox-launcher',
			'karma-coverage',
			'karma-requirejs',
			'karma-verbose-reporter'
		],

		// Concurrency level
		// how many browser should be started simultaneous
		concurrency: Infinity
	});
};
