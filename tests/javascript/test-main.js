var allTestFiles = [];
var TEST_REGEXP = /(spec|test)\.js$/i;

// Get a list of all the test files to include
Object.keys(window.__karma__.files).forEach(function (file) {
	if (TEST_REGEXP.test(file)) {
		// Normalize paths to RequireJS module names.
		// If you require sub-dependencies of test files to be loaded as-is (requiring file extension)
		// then do not normalize the paths
		var normalizedTestModule = file.replace(/^\/base\/|\.js$/g, '');
		allTestFiles.push(normalizedTestModule);
	}
});

require.config({
	// Karma serves files under /base, which is the basePath from your config file
	baseUrl: '/base',

	paths: {
		'jquery': 'tests/javascript/node_modules/jquery/dist/jquery.min',
		'jui': 'media/jui/js/jquery.ui.core.min',
		'jui-sortable': 'media/jui/js/jquery.ui.sortable.min',
		'bootstrap': 'media/jui/js/bootstrap.min',
		'jasmineJquery': 'tests/javascript/node_modules/jasmine-jquery/lib/jasmine-jquery',
		'libs': 'media/system/js',
		'testsRoot': 'tests/javascript',
		'text': 'tests/javascript/node_modules/text/text',
		'calLang': 'media/system/js/fields/calendar-locales/en',
		'calDate': 'media/system/js/fields/calendar-locales/date/gregorian/date-helper'
	},

	shim: {
		jasmineJquery: ['jquery'],
		bootstrap: ['jquery'],
		'jui-sortable': ['jquery'],
		'libs/repeatable': {
			deps: ['bootstrap', 'jquery']
		},
		'libs/validate': {
			deps: ['jquery']
		},
		'libs/subform-repeatable': {
			deps: ['jquery', 'jui', 'jui-sortable']
		},
		'libs/sendtestmail': {
			deps: ['jquery']
		},
		'libs/combobox': {
			deps: ['jquery']
		},
		'libs/fields/calendar-vanilla': {
			deps: ['calLang', 'calDate']
		}
	},

	// dynamically load all test files
	deps: allTestFiles,

	// we have to kickoff jasmine, as it is asynchronous
	callback: window.__karma__.start
});
