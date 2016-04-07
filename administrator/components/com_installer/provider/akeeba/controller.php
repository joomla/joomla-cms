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

// Mini-controller for installer.php
  if( !defined('KICKSTART') ){

    /**
     * The observer class, used to report number of files and bytes processed
     * This is specifically paired with the dialog monitoring our progress
     * This is also specifically designed for archive logic and not generalized
     * to include uncompressed (folder) installations
     */
      class RestorationObserver extends AKAbstractPartObserver {

        public $compressedTotal = 0;
        public $uncompressedTotal = 0;
        public $filesProcessed = 0;

        public function update($object, $message)
        {

          // Validation
            if(!is_object($message)) return;
            if( !array_key_exists('type', get_object_vars($message)) ) return;

          // We started a file
            if( in_array($message->type, array('startfile')) ){
              $this->filesProcessed++;
            }

          // Push Values
            if( isset($message->content->compressed) ){
              $this->compressedTotal += $message->content->compressed;
            }
            if( isset($message->content->uncompressed) ){
              $this->uncompressedTotal += $message->content->uncompressed;
            }

        }
        public function __toString()
        {
          return __CLASS__;
        }
      }

    // Import configuration
      masterSetup();

    // Start Return
      $retArray = array(
        'status'  => true,
        'message'  => null
      );

    // Already Enabled?
      $enabled = AKFactory::get('kickstart.enabled', false);

    // Request Task Switch
      if( $enabled ){
        $task = getQueryParam('task');
        switch( $task ){

          // Status Request
            case 'ping':

              // ping task - really does nothing!
                $timer = AKFactory::getTimer();
                $timer->enforce_min_exec_time();
                break;

          // Start the Installation
            case 'startRestore':

              // Reset the factory
                AKFactory::nuke();

          // Continue the Installation
            case 'stepRestore':

              // Stage
                $engine = AKFactory::getUnarchiver(); // Get the engine
                $observer = new RestorationObserver(); // Create a new observer

              // Prepare & Execute
                $engine->attach($observer); // Attach the observer
                $engine->tick();
                $ret = $engine->getStatusArray();

              // We have an error
                if( $ret['Error'] != '' ) {
                  $retArray['status'] = false;
                  $retArray['done'] = true;
                  $retArray['message'] = $ret['Error'];
                }

              // The installation is complete
                elseif( !$ret['HasRun'] ) {
                  $retArray['files'] = $observer->filesProcessed;
                  $retArray['bytesIn'] = $observer->compressedTotal;
                  $retArray['bytesOut'] = $observer->uncompressedTotal;
                  $retArray['status'] = true;
                  $retArray['done'] = true;
                }

              // We are still running and stopped
                else {
                  $retArray['files'] = $observer->filesProcessed;
                  $retArray['bytesIn'] = $observer->compressedTotal;
                  $retArray['bytesOut'] = $observer->uncompressedTotal;
                  $retArray['status'] = true;
                  $retArray['done'] = false;
                  $retArray['factory'] = AKFactory::serialize();
                }

              // Debug
                // debugMsg( ['controller.stepRestore', $ret, $retArray] );

              break;

          // Finish the Installtion
            case 'finalizeRestore':

              /**
               * Everything here is Joomla specific and should be moved
               * to a handler that comes with the installation package
               */

              // Stage Target
                $root = AKFactory::get('kickstart.setup.destdir');

              // Remove the installation directory
              // TODO: This is joomla specific and should be part of a post
              //       installation event for that package
                recursive_remove_directory( $root.'/installation' );

              // Stage File Handler
                $postproc = AKFactory::getPostProc();

              // Rename htaccess.bak to .htaccess
              // TODO: Move to postproc for specific package
                if( file_exists($root.'/htaccess.bak') ){
                  if( file_exists($root.'/.htaccess')  ){
                    $postproc->unlink($root.'/.htaccess');
                  }
                  $postproc->rename( $root.'/htaccess.bak', $root.'/.htaccess' );
                }

              // Rename htaccess.bak to .htaccess
              // TODO: Move to postproc for specific package
                if( file_exists($root.'/web.config.bak') ){
                  if( file_exists($root.'/web.config')  ){
                    $postproc->unlink($root.'/web.config');
                  }
                  $postproc->rename( $root.'/web.config.bak', $root.'/web.config' );
                }

              // Remove installer.config.php
                if( file_exists($root.'/web.config.bak') ){
                  $basepath = KSROOTDIR;
                  $basepath = rtrim( str_replace('\\','/',$basepath), '/' );
                  if(!empty($basepath))
                    $basepath .= '/';
                  $postproc->unlink( $basepath.'installer.config.php' );
                }

              /**
               * The following is part of the portable restore operation and
               * should be replaced with the install package handler
               */

              // Import a custom finalisation file
                if( is_readable(__DIR__ . '/installer.finalization.php') ){
                  include_once __DIR__ . '/installer.finalization.php';
                }

              // Run a custom finalisation script
                if( function_exists('finalizeRestore') ){
                  finalizeRestore($root, $basepath);
                }

              break;

          // Invalid task!
            default:
              $enabled = false;
              break;

        }
      }

    // We aren't yet enabled and don't know why so we're defaulting to a
    // authentication error
      if( !$enabled ){
        $retArray['status'] = false;
        $retArray['message'] = AKText::_('ERR_INVALID_LOGIN');
      }

    // Encode / Encrypt the message
      $json = json_encode($retArray);
      $password = AKFactory::get('kickstart.security.password', null);
      if( !empty($password) ){
        $json = AKEncryptionAES::AESEncryptCtr($json, $password, 128);
      }

    // Return the message
      echo "###$json###";

  }
