<?php

/**
 * Akeeba Restore
 * A JSON-powered JPA, JPS and ZIP archive extraction library
 *
 * @copyright   2010-2014 Nicholas K. Dionysopoulos / Akeeba Ltd.
 * @license     GNU GPL v2 or - at your option - any later version
 * @package     akeebabackup
 * @subpackage  kickstart
 */

// OS specific
  defined('DS') or define('DS', DIRECTORY_SEPARATOR);

// Unarchiver run states
  define('KSDEBUG', 1);
  define('AK_STATE_NOFILE', 0); // File header not read yet
  define('AK_STATE_HEADER', 1); // File header read; ready to process data
  define('AK_STATE_DATA', 2); // Processing file data
  define('AK_STATE_DATAREAD', 3); // Finished processing file data; ready to post-process
  define('AK_STATE_POSTPROC', 4); // Post-processing
  define('AK_STATE_DONE', 5); // Done with post-processing

// Define Installer Path
  defined('KSROOTDIR') || define('KSROOTDIR', dirname(__FILE__));
  defined('KSLANGDIR') || define('KSLANGDIR', KSROOTDIR);

// Windows system detection
  if (!defined('_AKEEBA_IS_WINDOWS')) {
    if (function_exists('php_uname')) {
      define('_AKEEBA_IS_WINDOWS', stristr(php_uname(), 'windows'));
    }
    else {
      define('_AKEEBA_IS_WINDOWS', DIRECTORY_SEPARATOR == '\\');
    }
  }

// Make sure the locale is correct for basename() to work
  if( function_exists('setlocale')) {
    @setlocale(LC_ALL, 'en_US.UTF8');
  }
