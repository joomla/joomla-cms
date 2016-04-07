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
 * The Master Setup will read the configuration parameters from installer.config.php or
 * the JSON-encoded "configuration" input variable and return the status.
 *
 * @return bool True if the master configuration was applied to the Factory object
 */
  function masterSetup(){

    /**
     *
     * When KICKSTART is defined we assume that a foreign controller is
     *   handling the request.  This effects whether we try to load a
     *   local configuration file, and whether we are going to try
     *   to import an additional `configuration` parameter.
     *
     */

    /**
     *
     * 1. Import basic setup parameters
     *
     */

      // We are the controller - config found or fail
        if( !defined('KICKSTART') ){

          $setupFile = 'restoration.php';
          if( file_exists($setupFile) ){
            require_once $setupFile;
            $ini_data = $restoration_setup;
            if( empty($ini_data) ){
              AKFactory::set('kickstart.enabled', false);
              return false;
            }
          }
          else if( empty($GLOBALS['restoration_setup']) ){
            AKFactory::set('kickstart.enabled', false);
            return false;
          }
          AKFactory::set('kickstart.enabled', true);

        }

      // We don't yet have a configuration
        if( empty($ini_data) ){

          // Config INI string
            if( !empty($GLOBALS['restoration_setup']) && is_string($GLOBALS['restoration_setup']) ){
              $ini_data = AKText::parse_ini_file($GLOBALS['restoration_setup'], false, true);
            }

          // Config Array
            else if( is_array($GLOBALS['restoration_setup']) ){
              $ini_data = $GLOBALS['restoration_setup'];
            }

        }

      // Whatever we found, let's pull it into the factory
        if( !empty($ini_data) ){
          foreach( $ini_data as $key => $value ){
            AKFactory::set($key, $value);
          }
          AKFactory::set('kickstart.enabled', true);
          unset($init_data);
        }

    /**
     *
     * 2. Explode JSON parameters into $_REQUEST scope
     *
     */

      // Detect a JSON string in the request variable and store it.
        $json = getQueryParam('json', null);

      // Decrypt a possibly encrypted JSON string
        $password = AKFactory::get('kickstart.security.password', null);

      // Reset Request Environemnt
        $_REQUEST = $_POST = $_GET = array();

      // Parse Encoded Request
        if (!empty($json)) {
          if (!empty($password)) {
            $json = AKEncryptionAES::AESDecryptCtr($json, $password, 128);
            if (empty($json)) {
              die('###{"status":false,"message":"Invalid login"}###');
            }
          }
          // Get the raw data
          $raw = json_decode($json, true);
          if (!empty($password) && (empty($raw))) {
            die('###{"status":false,"message":"Invalid login"}###');
          }
          // Pass all JSON data to the request array
          if (!empty($raw)) {
            foreach ($raw as $key => $value) {
              $_REQUEST[$key] = $value;
            }
          }
        }
        elseif (!empty($password)) {
          die('###{"status":false,"message":"Invalid login"}###');
        }

    /**
     *
     * 3. Try the "factory" variable
     *    We are going to wakeup a serialized factory object if possible
     *
     */

      // A "factory" variable will override all other settings.
        $serialized = getQueryParam('factory', null);
        if (!is_null($serialized)) {
          // Get the serialized factory
          AKFactory::unserialize($serialized);
          AKFactory::set('kickstart.enabled', true);
          return true;
        }

    /**
     *
     * 4. Try the configuration variable for Kickstart (if a slave)
     *
     */

      if (defined('KICKSTART')) {
        $configuration = getQueryParam('configuration');
        if (!is_null($configuration)) {
          // Let's decode the configuration from JSON to array
          $ini_data = json_decode($configuration, true);
        }
        else {
          // Neither exists. Enable Kickstart's interface anyway.
          $ini_data = array('kickstart.enabled' => true);
        }
        // Import any INI data we might have from other sources
        if (!empty($ini_data)) {
          foreach ($ini_data as $key => $value) {
            AKFactory::set($key, $value);
          }
          AKFactory::set('kickstart.enabled', true);
          return true;
        }
      }

  }
