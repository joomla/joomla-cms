const allTestFiles = [];
const TEST_REGEXP = /(spec|test)\.js$/i;

// Get a list of all the test files to include
Object.keys(window.__karma__.files).forEach((file) => {
	if (TEST_REGEXP.test(file)) {
		// Normalize paths to RequireJS module names.
		// If you require sub-dependencies of test files to be loaded as-is (requiring file extension)
		// then do not normalize the paths
		const normalizedTestModule = file.replace(/^\/base\/|\.js$/g, '');
		allTestFiles.push(normalizedTestModule);
	}
});

require.config({
	// Karma serves files under /base, which is the basePath from your config file
	baseUrl: '/base',

	paths: {
		'jasmineJquery': 'node_modules/jasmine-jquery/lib/jasmine-jquery',
		'core': 'media/system/js/core.min',

		'jquery': 'node_modules/jquery/dist/jquery.min',
		'bootstrap': 'media/vendor/bootstrap/js/bootstrap.min',

		'libs': 'media/system/js',
		'legacy': 'media/legacy/js',
		'testsRoot': 'tests/javascript',
		'text': 'node_modules/text/text',
		'fields': 'media/system/js/fields',
		'calLang': 'media/system/js/fields/calendar-locales/en',
		'calDate': 'media/system/js/fields/calendar-locales/date/gregorian/date-helper',
		'JCE': 'media/system/webcomponents/js'
	},

	shim: {
		jasmineJquery: ['jquery'],
		bootstrap: ['jquery'],
		'libs/validate': {
			deps: []
		},
		'JCE/joomla-field-subform': {
			deps: []
		},
		'JCE/joomla-field-send-test-mail': {
			deps: ['jquery']
		},
		'libs/fields/calendar': {
			deps: ['calLang', 'calDate']
		}
	},

	// dynamically load all test files
	deps: allTestFiles,

	// we have to kickoff jasmine, as it is asynchronous
	callback: window.__karma__.start
});
