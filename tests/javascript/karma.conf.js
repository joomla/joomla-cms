// Karma configuration
module.exports = function (config) {
  config.set({
    // base path that will be used to resolve all patterns (eg. files, exclude)
    basePath: __dirname.replace('tests/javascript', ''),

    // frameworks to use
    // available frameworks: https://npmjs.org/browse/keyword/karma-adapter
    frameworks: ['fixture', 'jasmine'],

    //

    // list of files / patterns to load in the browser
    files: [
      './**/fixtures/*.html',

      // Include the webcomponents polyfills
      { pattern: 'node_modules/@webcomponents/webcomponentsjs/custom-elements-es5-adapter.js', loaded: true, served: true, watch: false },
      // Load web components polyfill
      { pattern: 'media/vendor/webcomponentsjs/js/webcomponents-sd-ce-pf.js', loaded: true, served: true, watch: false },

    ],

    // preprocess matching files before serving them to the browser
    // available preprocessors: https://npmjs.org/browse/keyword/karma-preprocessor
    preprocessors: {
      '**/system/js/*.js': ['coverage'],
      '**/*.html': ['html2js'],
      '**/*.json': ['json_fixtures'],
    },

    // coverage reporter configuration
    coverageReporter: {
      type: 'html',
      dir: 'build/coverage-js/'
    },

    // test results reporter to use
    // possible values: 'dots', 'progress'
    // available reporters: https://npmjs.org/browse/keyword/karma-reporter
    reporters: ['verbose', 'progress', 'coverage'],

    // web server port
    port: 9876,

    // enable / disable colors in the output (reporters and logs)
    colors: true,

    // level of logging
    // possible values: config.LOG_DISABLE || config.LOG_ERROR || config.LOG_WARN || config.LOG_INFO || config.LOG_DEBUG
    logLevel: config.INFO,

    // enable / disable watching file and executing tests whenever any file changes
    autoWatch: true,

    // start these browsers
    // available browser launchers: https://npmjs.org/browse/keyword/karma-launcher
    browsers: ['Firefox'],

    // Continuous Integration mode
    // if true, Karma captures browsers, runs the tests and exits
    singleRun: false,

    // Currently we have no tests as we've moved switcher away. Once we add tests change this back to true
    failOnEmptyTestSuite: false,

    // list of plugins
    plugins: [
      'karma-fixture',
      'karma-html2js-preprocessor',
      'karma-json-fixtures-preprocessor',
      'karma-jasmine',
      'karma-firefox-launcher',
      'karma-coverage',
      'karma-verbose-reporter'
    ],

    // Concurrency level
    // how many browser should be started simultaneous
    concurrency: Infinity
  });
};
