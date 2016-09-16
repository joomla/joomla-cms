'use strict';

/*!
 * Inspired Gruntfile
 * Copyright 2015 Seth Warburton.
 * Version 1.01
 * Licensed under MIT (http://opensource.org/licenses/MIT)
 */

module.exports = function(grunt) {

// Time how long tasks take. A big help when optimizing build times ;)
require('time-grunt')(grunt);

  // Configurable paths
  var config = {
    app: '_src/', // The source directory
    dist: '' // The output directory
  };

  //Initializing the configuration object
  grunt.initConfig({

    // Project settings
    config: config,

    browserSync: {
      bsFiles: {
          src : '<%= config.dist %>css/template.min.css'
      },
      options: {
        watchTask: true,
        files: [
            'index.php',
            '<%= config.dist %>css/*.css',
            '<%= config.dist %>js/*.js',
            'html/**/*.php',
            'html/**/**/*.php',
        ],
        // Set this to match your localhost path and port for *YOUR* environment
        proxy: 'http://j4.loc/administrator'
      }
    },

    // Empties destination and temp folders to start fresh
    clean: {
      build: {
        src: [
          'css/**',
          'images/**',
          'js/**',
        ],
        expand: true,
        options: {
          force: true
        },
      },
    },

    // Combine and copy JS, without minification, for development. Because
    // JS uglify is very slow in comparison we only uglify on build.
    concat: {
        options: {
        stripBanners: false
      },
      dev: {
        files: {
          '<%= config.dist %>js/template.js': ['<%= config.app %>js/template.js'],
        },
      },
    },

    // Copies any remaining files
    copy: {
      files: {
        cwd: '<%= config.app %>js/vendor/',
        src: '**/*',
        dest: '<%= config.dist %>js/vendor/',
        expand: true
      }
    },

    // Process images to optimise filesizes for production
    imagemin: {
      dist: {
        files: [{
          expand: true,
          cwd: '<%= config.app %>images',
          src: '{,*/}*.{gif,jpeg,jpg,png}',
          dest: '<%= config.dist %>images'
        }]
      }
    },

    // Make sure code styles are up to par and there are no obvious mistakes
    jshint: {
      options: {
        jshintrc: '.jshintrc',
        reporter: require('jshint-stylish')
      },
      all: [
        '<%= config.app %>js/*.js'
      ]
    },

    postcss: {
      dev: {
        options: {
          map: true,
          processors: [
            require('autoprefixer')({
              browsers: 'last 1 version, > 5%' // add vendor prefixes
            }),
            require('cssnano')() // optimise and the minify the result
          ],
        },
        files: {
          '<%= config.dist %>css/template.min.css': '<%= config.dist %>css/template.css'
        }
      },
      dist: {
        options: {
          map: false,
          processors: [
            require('autoprefixer')({
              browsers: 'last 1 version, > 5%' // add vendor prefixes
            }),
            require('cssnano')() // optimise and the minify the result
          ],
        },
        files: {
          '<%= config.dist %>css/template.min.css': '<%= config.dist %>css/template.css'
        }
      }
    },

    // Compile Sass source files to CSS
    sass: {
      dev: {
        options: {
          precision: '5',
          sourceMap: true
        },
        files: {
          '<%= config.dist %>css/template.css': '<%= config.app %>scss/template.scss'
        }
      },
      dist: {
        options: {
          precision: '5',
          sourceMap: false
        },
        files: {
          '<%= config.dist %>css/template.css': '<%= config.app %>scss/template.scss'
        }
      }
    },

    scsslint: {
      allFiles: [
        '<%= config.app %>scss',
      ],
      options: {
        config: '<%= config.app %>scss/scss-lint.yml',
        reporterOutput: '<%= config.app %>scss/scss-lint-report.xml'
      },
    },

    // Compress JS files for production
    uglify: {
      dist: {
        files: {
          '<%= config.dist %>js/template.js': '<%= config.app %>js/template.js'
        }
      }
    },

    // Watch files for changes and run tasks based on the changed files
    watch: {
      sass: {
        files: ['<%= config.app %>scss/**/*.scss'],
        tasks: ['styles']
      },
      js: {
        files: ['<%= config.app %>js/*.js'],
        tasks: ['concat:dev']
      },
      gruntfile: {
        files: ['Gruntfile.js']
      }
    }
  });

  // Just-in-time plugin, for loading plugins really quickly.
  require('jit-grunt')(grunt, {
    scsslint: 'grunt-scss-lint'
  });

  // Task registration
  grunt.registerTask('default', ['dev']);
  grunt.registerTask('build', ['dist']);
  grunt.registerTask('styles', []);
  grunt.registerTask('images', []);

  // Primary Task definition
  grunt.registerTask('dev', ['clean','images','sass:dev','postcss:dev','concat:dev','copy','browserSync','watch']);
  grunt.registerTask('dist', ['clean','images','sass:dist','postcss:dist','test','uglify','copy']);

  // Sub-tasks, called by primary tasks, for better organisation.
  grunt.registerTask('images', ['imagemin']);
  grunt.registerTask('styles', ['sass','postcss']);
  grunt.registerTask('test', ['scsslint']);
};
