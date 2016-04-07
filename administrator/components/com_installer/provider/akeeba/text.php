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

/**
 * A simple INI-based i18n engine
 */

class AKText extends AKAbstractObject
{
  /**
   * The default (en_GB) translation used when no other translation is available
   * @var array
   */
  private $default_translation = array(
    'AUTOMODEON' => 'Auto-mode enabled',
    'ERR_NOT_A_JPA_FILE' => 'The file is not a JPA archive',
    'ERR_CORRUPT_ARCHIVE' => 'The archive file is corrupt, truncated or archive parts are missing',
    'ERR_INVALID_LOGIN' => 'Invalid login',
    'COULDNT_CREATE_DIR' => 'Could not create %s folder',
    'COULDNT_WRITE_FILE' => 'Could not open %s for writing.',
    'WRONG_FTP_HOST' => 'Wrong FTP host or port',
    'WRONG_FTP_USER' => 'Wrong FTP username or password',
    'WRONG_FTP_PATH1' => 'Wrong FTP initial directory - the directory doesn\'t exist',
    'FTP_CANT_CREATE_DIR' => 'Could not create directory %s',
    'FTP_TEMPDIR_NOT_WRITABLE' => 'Could not find or create a writable temporary directory',
    'SFTP_TEMPDIR_NOT_WRITABLE' => 'Could not find or create a writable temporary directory',
    'FTP_COULDNT_UPLOAD' => 'Could not upload %s',
    'THINGS_HEADER' => 'Things you should know about Akeeba Kickstart',
    'THINGS_01' => 'Kickstart is not an installer. It is an archive extraction tool. The actual installer was put inside the archive file at backup time.',
    'THINGS_02' => 'Kickstart is not the only way to extract the backup archive. You can use Akeeba eXtract Wizard and upload the extracted files using FTP instead.',
    'THINGS_03' => 'Kickstart is bound by your server\'s configuration. As such, it may not work at all.',
    'THINGS_04' => 'You should download and upload your archive files using FTP in Binary transfer mode. Any other method could lead to a corrupt backup archive and restoration failure.',
    'THINGS_05' => 'Post-restoration site load errors are usually caused by .htaccess or php.ini directives. You should understand that blank pages, 404 and 500 errors can usually be worked around by editing the aforementioned files. It is not our job to mess with your configuration files, because this could be dangerous for your site.',
    'THINGS_06' => 'Kickstart overwrites files without a warning. If you are not sure that you are OK with that do not continue.',
    'THINGS_07' => 'Trying to restore to the temporary URL of a cPanel host (e.g. http://1.2.3.4/~username) will lead to restoration failure and your site will appear to be not working. This is normal and it\'s just how your server and CMS software work.',
    'THINGS_08' => 'You are supposed to read the documentation before using this software. Most issues can be avoided, or easily worked around, by understanding how this software works.',
    'THINGS_09' => 'This text does not imply that there is a problem detected. It is standard text displayed every time you launch Kickstart.',
    'CLOSE_LIGHTBOX' => 'Click here or press ESC to close this message',
    'SELECT_ARCHIVE' => 'Select a backup archive',
    'ARCHIVE_FILE' => 'Archive file:',
    'SELECT_EXTRACTION' => 'Select an extraction method',
    'WRITE_TO_FILES' => 'Write to files:',
    'WRITE_HYBRID' => 'Hybrid (use FTP only if needed)',
    'WRITE_DIRECTLY' => 'Directly',
    'WRITE_FTP' => 'Use FTP for all files',
    'WRITE_SFTP' => 'Use SFTP for all files',
    'FTP_HOST' => '(S)FTP host name:',
    'FTP_PORT' => '(S)FTP port:',
    'FTP_FTPS' => 'Use FTP over SSL (FTPS)',
    'FTP_PASSIVE' => 'Use FTP Passive Mode',
    'FTP_USER' => '(S)FTP user name:',
    'FTP_PASS' => '(S)FTP password:',
    'FTP_DIR' => '(S)FTP directory:',
    'FTP_TEMPDIR' => 'Temporary directory:',
    'FTP_CONNECTION_OK' => 'FTP Connection Established',
    'SFTP_CONNECTION_OK' => 'SFTP Connection Established',
    'FTP_CONNECTION_FAILURE' => 'The FTP Connection Failed',
    'SFTP_CONNECTION_FAILURE' => 'The SFTP Connection Failed',
    'FTP_TEMPDIR_WRITABLE' => 'The temporary directory is writable.',
    'FTP_TEMPDIR_UNWRITABLE' => 'The temporary directory is not writable. Please check the permissions.',
    'FTPBROWSER_ERROR_HOSTNAME' => "Invalid FTP host or port",
    'FTPBROWSER_ERROR_USERPASS' => "Invalid FTP username or password",
    'FTPBROWSER_ERROR_NOACCESS' => "Directory doesn't exist or you don't have enough permissions to access it",
    'FTPBROWSER_ERROR_UNSUPPORTED' => "Sorry, your FTP server doesn't support our FTP directory browser.",
    'FTPBROWSER_LBL_GOPARENT' => "&lt;up one level&gt;",
    'FTPBROWSER_LBL_INSTRUCTIONS' => 'Click on a directory to navigate into it. Click on OK to select that directory, Cancel to abort the procedure.',
    'FTPBROWSER_LBL_ERROR' => 'An error occurred',
    'SFTP_NO_SSH2' => 'Your web server does not have the SSH2 PHP module, therefore can not connect to SFTP servers.',
    'SFTP_NO_FTP_SUPPORT' => 'Your SSH server does not allow SFTP connections',
    'SFTP_WRONG_USER' => 'Wrong SFTP username or password',
    'SFTP_WRONG_STARTING_DIR' => 'You must supply a valid absolute path',
    'SFTPBROWSER_ERROR_NOACCESS' => "Directory doesn't exist or you don't have enough permissions to access it",
    'SFTP_COULDNT_UPLOAD' => 'Could not upload %s',
    'SFTP_CANT_CREATE_DIR' => 'Could not create directory %s',
    'UI-ROOT' => '&lt;root&gt;',
    'CONFIG_UI_FTPBROWSER_TITLE' => 'FTP Directory Browser',
    'FTP_BROWSE' => 'Browse',
    'BTN_CHECK' => 'Check',
    'BTN_RESET' => 'Reset',
    'BTN_TESTFTPCON' => 'Test FTP connection',
    'BTN_TESTSFTPCON' => 'Test SFTP connection',
    'BTN_GOTOSTART' => 'Start over',
    'FINE_TUNE' => 'Fine tune',
    'MIN_EXEC_TIME' => 'Minimum execution time:',
    'MAX_EXEC_TIME' => 'Maximum execution time:',
    'SECONDS_PER_STEP' => 'seconds per step',
    'EXTRACT_FILES' => 'Extract files',
    'BTN_START' => 'Start',
    'EXTRACTING' => 'Extracting',
    'DO_NOT_CLOSE_EXTRACT' => 'Do not close this window while the extraction is in progress',
    'RESTACLEANUP' => 'Restoration and Clean Up',
    'BTN_RUNINSTALLER' => 'Run the Installer',
    'BTN_CLEANUP' => 'Clean Up',
    'BTN_SITEFE' => 'Visit your site\'s front-end',
    'BTN_SITEBE' => 'Visit your site\'s back-end',
    'WARNINGS' => 'Extraction Warnings',
    'ERROR_OCCURED' => 'An error occured',
    'STEALTH_MODE' => 'Stealth mode',
    'STEALTH_URL' => 'HTML file to show to web visitors',
    'ERR_NOT_A_JPS_FILE' => 'The file is not a JPA archive',
    'ERR_INVALID_JPS_PASSWORD' => 'The password you gave is wrong or the archive is corrupt',
    'JPS_PASSWORD' => 'Archive Password (for JPS files)',
    'INVALID_FILE_HEADER' => 'Invalid header in archive file, part %s, offset %s',
    'NEEDSOMEHELPKS' => 'Want some help to use this tool? Read this first:',
    'QUICKSTART' => 'Quick Start Guide',
    'CANTGETITTOWORK' => 'Can\'t get it to work? Click me!',
    'NOARCHIVESCLICKHERE' => 'No archives detected. Click here for troubleshooting instructions.',
    'POSTRESTORATIONTROUBLESHOOTING' => 'Something not working after the restoration? Click here for troubleshooting instructions.',
    'UPDATE_HEADER' => 'An updated version of Akeeba Kickstart (<span id="update-version">unknown</span>) is available!',
    'UPDATE_NOTICE' => 'You are advised to always use the latest version of Akeeba Kickstart available. Older versions may be subject to bugs and will not be supported.',
    'UPDATE_DLNOW' => 'Download now',
    'UPDATE_MOREINFO' => 'More information',
    'IGNORE_MOST_ERRORS' => 'Ignore most errors',
    'WRONG_FTP_PATH2' => 'Wrong FTP initial directory - the directory doesn\'t correspond to your site\'s web root',
    'ARCHIVE_DIRECTORY' => 'Archive directory:',
    'RELOAD_ARCHIVES'  => 'Reload',
    'CONFIG_UI_SFTPBROWSER_TITLE'  => 'SFTP Directory Browser',
  );

  /**
   * The array holding the translation keys
   * @var array
   */
  private $strings;

  /**
   * The currently detected language (ISO code)
   * @var string
   */
  private $language;

  /*
   * Initializes the translation engine
   * @return AKText
   */
  public function __construct()
  {
    // Start with the default translation
    $this->strings = $this->default_translation;
    // Try loading the translation file in English, if it exists
    $this->loadTranslation('en-GB');
    // Try loading the translation file in the browser's preferred language, if it exists
    $this->getBrowserLanguage();
    if(!is_null($this->language))
    {
      $this->loadTranslation();
    }
  }

  /**
   * Singleton pattern for Language
   * @return AKText The global AKText instance
   */
  public static function &getInstance()
  {
    static $instance;

    if(!is_object($instance))
    {
      $instance = new AKText();
    }

    return $instance;
  }

  public static function _($string)
  {
    $text = self::getInstance();

    $key = strtoupper($string);
    $key = substr($key, 0, 1) == '_' ? substr($key, 1) : $key;

    if (isset ($text->strings[$key]))
    {
      $string = $text->strings[$key];
    }
    else
    {
      if (defined($string))
      {
        $string = constant($string);
      }
    }

    return $string;
  }

  public static function sprintf($key)
  {
    $text = self::getInstance();
    $args = func_get_args();
    if (count($args) > 0) {
      $args[0] = $text->_($args[0]);
      return @call_user_func_array('sprintf', $args);
    }
    return '';
  }

  public function dumpLanguage()
  {
    $out = '';
    foreach($this->strings as $key => $value)
    {
      $out .= "$key=$value\n";
    }
    return $out;
  }

  public function asJavascript()
  {
    $out = '';
    foreach($this->strings as $key => $value)
    {
      $key = addcslashes($key, '\\\'"');
      $value = addcslashes($value, '\\\'"');
      if(!empty($out)) $out .= ",\n";
      $out .= "'$key':\t'$value'";
    }
    return $out;
  }

  public function resetTranslation()
  {
    $this->strings = $this->default_translation;
  }

  public function getBrowserLanguage()
  {
    // Detection code from Full Operating system language detection, by Harald Hope
    // Retrieved from http://techpatterns.com/downloads/php_language_detection.php
    $user_languages = array();
    //check to see if language is set
    if ( isset( $_SERVER["HTTP_ACCEPT_LANGUAGE"] ) )
    {
      $languages = strtolower( $_SERVER["HTTP_ACCEPT_LANGUAGE"] );
      // $languages = ' fr-ch;q=0.3, da, en-us;q=0.8, en;q=0.5, fr;q=0.3';
      // need to remove spaces from strings to avoid error
      $languages = str_replace( ' ', '', $languages );
      $languages = explode( ",", $languages );

      foreach ( $languages as $language_list )
      {
        // pull out the language, place languages into array of full and primary
        // string structure:
        $temp_array = array();
        // slice out the part before ; on first step, the part before - on second, place into array
        $temp_array[0] = substr( $language_list, 0, strcspn( $language_list, ';' ) );//full language
        $temp_array[1] = substr( $language_list, 0, 2 );// cut out primary language
        if( (strlen($temp_array[0]) == 5) && ( (substr($temp_array[0],2,1) == '-') || (substr($temp_array[0],2,1) == '_') ) )
        {
          $langLocation = strtoupper(substr($temp_array[0],3,2));
          $temp_array[0] = $temp_array[1].'-'.$langLocation;
        }
        //place this array into main $user_languages language array
        $user_languages[] = $temp_array;
      }
    }
    else// if no languages found
    {
      $user_languages[0] = array( '','' ); //return blank array.
    }

    $this->language = null;
    $basename=basename(__FILE__, '.php') . '.ini';

    // Try to match main language part of the filename, irrespective of the location, e.g. de_DE will do if de_CH doesn't exist.
    if (class_exists('AKUtilsLister'))
    {
      $fs = new AKUtilsLister();
      $iniFiles = $fs->getFiles(KSROOTDIR, '*.'.$basename );
      if(empty($iniFiles) && ($basename != 'kickstart.ini')) {
        $basename = 'kickstart.ini';
        $iniFiles = $fs->getFiles(KSROOTDIR, '*.'.$basename );
      }
    }
    else
    {
      $iniFiles = null;
    }

    if (is_array($iniFiles)) {
      foreach($user_languages as $languageStruct)
      {
        if(is_null($this->language))
        {
          // Get files matching the main lang part
          $iniFiles = $fs->getFiles(KSROOTDIR, $languageStruct[1].'-??.'.$basename );
          if (count($iniFiles) > 0) {
            $filename = $iniFiles[0];
            $filename = substr($filename, strlen(KSROOTDIR)+1);
            $this->language = substr($filename, 0, 5);
          } else {
            $this->language = null;
          }
        }
      }
    }

    if(is_null($this->language)) {
      // Try to find a full language match
      foreach($user_languages as $languageStruct)
      {
        if (@file_exists($languageStruct[0].'.'.$basename) && is_null($this->language)) {
          $this->language = $languageStruct[0];
        } else {

        }
      }
    } else {
      // Do we have an exact match?
      foreach($user_languages as $languageStruct)
      {
        if(substr($this->language,0,strlen($languageStruct[1])) == $languageStruct[1]) {
          if(file_exists($languageStruct[0].'.'.$basename)) {
            $this->language = $languageStruct[0];
          }
        }
      }
    }

    // Now, scan for full language based on the partial match

  }

  private function loadTranslation( $lang = null )
  {
    if (defined('KSLANGDIR'))
    {
      $dirname = KSLANGDIR;
    }
    else
    {
      $dirname = KSROOTDIR;
    }
    $basename = basename(__FILE__, '.php') . '.ini';
    if( empty($lang) ) $lang = $this->language;

    $translationFilename = $dirname.DIRECTORY_SEPARATOR.$lang.'.'.$basename;
    if(!@file_exists($translationFilename) && ($basename != 'kickstart.ini')) {
      $basename = 'kickstart.ini';
      $translationFilename = $dirname.DIRECTORY_SEPARATOR.$lang.'.'.$basename;
    }
    if(!@file_exists($translationFilename)) return;
    $temp = self::parse_ini_file($translationFilename, false);

    if(!is_array($this->strings)) $this->strings = array();
    if(empty($temp)) {
      $this->strings = array_merge($this->default_translation, $this->strings);
    } else {
      $this->strings = array_merge($this->strings, $temp);
    }
  }

  public function addDefaultLanguageStrings($stringList = array())
  {
    if(!is_array($stringList)) return;
    if(empty($stringList)) return;

    $this->strings = array_merge($stringList, $this->strings);
  }

  /**
   * A PHP based INI file parser.
   *
   * Thanks to asohn ~at~ aircanopy ~dot~ net for posting this handy function on
   * the parse_ini_file page on http://gr.php.net/parse_ini_file
   *
   * @param string $file Filename to process
   * @param bool $process_sections True to also process INI sections
   * @return array An associative array of sections, keys and values
   * @access private
   */
  public static function parse_ini_file($file, $process_sections = false, $raw_data = false)
  {
    $process_sections = ($process_sections !== true) ? false : true;

    if(!$raw_data)
    {
      $ini = @file($file);
    }
    else
    {
      $ini = $file;
    }
    if (count($ini) == 0) {return array();}

    $sections = array();
    $values = array();
    $result = array();
    $globals = array();
    $i = 0;
    if(!empty($ini)) foreach ($ini as $line) {
      $line = trim($line);
      $line = str_replace("\t", " ", $line);

      // Comments
      if (!preg_match('/^[a-zA-Z0-9[]/', $line)) {continue;}

      // Sections
      if ($line{0} == '[') {
        $tmp = explode(']', $line);
        $sections[] = trim(substr($tmp[0], 1));
        $i++;
        continue;
      }

      // Key-value pair
      list($key, $value) = explode('=', $line, 2);
      $key = trim($key);
      $value = trim($value);
      if (strstr($value, ";")) {
        $tmp = explode(';', $value);
        if (count($tmp) == 2) {
          if ((($value{0} != '"') && ($value{0} != "'")) ||
          preg_match('/^".*"\s*;/', $value) || preg_match('/^".*;[^"]*$/', $value) ||
          preg_match("/^'.*'\s*;/", $value) || preg_match("/^'.*;[^']*$/", $value) ){
            $value = $tmp[0];
          }
        } else {
          if ($value{0} == '"') {
            $value = preg_replace('/^"(.*)".*/', '$1', $value);
          } elseif ($value{0} == "'") {
            $value = preg_replace("/^'(.*)'.*/", '$1', $value);
          } else {
            $value = $tmp[0];
          }
        }
      }
      $value = trim($value);
      $value = trim($value, "'\"");

      if ($i == 0) {
        if (substr($line, -1, 2) == '[]') {
          $globals[$key][] = $value;
        } else {
          $globals[$key] = $value;
        }
      } else {
        if (substr($line, -1, 2) == '[]') {
          $values[$i-1][$key][] = $value;
        } else {
          $values[$i-1][$key] = $value;
        }
      }
    }

    for($j = 0; $j < $i; $j++) {
      if ($process_sections === true) {
        $result[$sections[$j]] = $values[$j];
      } else {
        $result[] = $values[$j];
      }
    }

    return $result + $globals;
  }
}

