<?php

/**
 *
 * Folder extraction class
 *
 * It was faster to hack this then refactor the base restore operation to
 * recognize a folder.
 *
 */

class AKUnarchiverFolder extends AKUnarchiverJPA
{

  var $expectDataDescriptor = false;

  function __sleep(){
    return array_diff(array_keys(get_object_vars($this)), array(
      'fileList'
      ));
  }

  /**
   * [readArchiveHeader description]
   * @return [type] [description]
   */
  protected function readArchiveHeader()
  {

    // Yuup..
      return true;

  }

  /**
   * [readFileHeader description]
   * @return [type] [description]
   */
  protected function readFileHeader(){
    return true;
  }

  protected function _run(){

    if($this->getState() == 'postrun'){
      debugMsg(__CLASS__.'::_run() - Postrun Abort');
      return;
    }
    $this->setState('running');
    $timer     = AKFactory::getTimer();
    $status    = true;
    $fileCount = 0;

    while( $status && ($timer->getTimeLeft() > 0) ){

      switch( $this->runState ){

        case AK_STATE_NOFILE:
          // Debug
            debugMsg(__CLASS__.'::_run() - Starting');

        case AK_STATE_DATAREAD:
          // Debug
            debugMsg(__CLASS__.'::_run() - Queue File for Processing');
          // Process Runoff
            if( $fileCount++ >= 10000 ){
              debugMsg(__CLASS__.'::_run() - Queue File Aborted');
              return true;
            }
          // Queue or Done
            if( $this->nextFile() && is_resource($this->fp) ){
              $this->runState = AK_STATE_DATA;
            }
            else {
              $this->runState = AK_STATE_DONE;
            }
          break;

        case AK_STATE_DATA:
          // Debug
            debugMsg(__CLASS__.'::_run() - Process Index ['. $this->currentPartNumber .'] '. $this->fileDetails->source);
          // Report
            $message = new stdClass;
            $message->content = new stdClass;
            $message->type = 'readfile';
            if( $this->fileDetails->readIndex === 0 ){
              $message->type = 'startfile';
              $message->content->compressed = $this->fileDetails->size;
            }
            $message->content->uncompressed = $this->fileDetails->readIndex - $this->fileDetails->lastReadIndex;
            $this->notify($message);
          // Process
            if( !$this->processFileData() ){
              $this->runState = AK_STATE_NOFILE;
              debugMsg(__CLASS__.'::_run() - Process Index Aborted ' . $this->getState());
              return true;
            }
            else {
              $this->runState = AK_STATE_POSTPROC;
            }
          break;

        case AK_STATE_POSTPROC:
          // Debug
            debugMsg(__CLASS__.'::_run() - Calling post-processing class');
          // Report
            $message = new stdClass;
            $message->content = new stdClass;
            $message->type = 'finishfile';
            $message->content->uncompressed = $this->fileDetails->size - $this->fileDetails->lastReadIndex;
            $this->notify($message);
          // Post Process
            $this->postProcEngine->process();
            $this->propagateFromObject( $this->postProcEngine );
          // Continue
            $this->runState = AK_STATE_DATAREAD;
          break;

        case AK_STATE_DONE:
        default:
          // Debug
            debugMsg(__CLASS__.'::_run() - Done');
          // Report
            $message = new stdClass;
            $message->type = 'endfile';
            $message->content = new stdClass;
            $this->notify($message);
            $this->setState('finished');
            return true;
          break;

      }

    }

    // Debug
      debugMsg([__CLASS__.'::_run() - Timer Expired', $this->fileDetails]);

    // Success
      return true;

  }

  /**
   * Creates the directory this file points to
   */
  protected function createDirectory(){

    // Abort on dryrun
      if( AKFactory::get('kickstart.setup.dryrun','0') ) return true;

    // Do we need to create a directory?
      $lastSlash = strrpos($this->fileDetails->target, '/');
      $dirName = substr( $this->fileDetails->target, 0, $lastSlash);
      $perms = $this->flagRestorePermissions ? $this->fileDetails->permissions : 0755;
      $ignore = AKFactory::get('kickstart.setup.ignoreerrors', false) || $this->isIgnoredDirectory($dirName);
      if( ($this->postProcEngine->createDirRecursive($dirName, $perms) == false) && (!$ignore) ) {
        $this->setError( AKText::sprintf('COULDNT_CREATE_DIR', $dirName) );
        return false;
      }

    // Success
      return true;

  }

  /**
   * Opens the next part file for reading
   */
  protected function nextFile( $continue=false ){

    // Are we loading the next Queue
      $continue = $continue || (isset($this->fileDetails) && $this->fileDetails->readIndex);

    // Increment
      if( !$continue )
        ++$this->currentPartNumber;

    // Debug
      debugMsg('Current part is ' . $this->currentPartNumber . '; opening the next part');

    // Close
      if( is_resource($this->fp) ){
        @fclose($this->fp);
      }
      $this->fp = null;

    // Load New or Continue Existing
      if( !$continue || empty($this->fileDetails) || empty($this->fileDetails->source) ){

        // Reset
          $this->fileDetails = (object)array();

        // Lookup
          if( empty($this->fileList) )
            $file = $this->__scanFolderRecursively( $this->getFilename() );
          if( isset($this->fileList[ $this->currentPartNumber ]) )
            $file = $this->fileList[ $this->currentPartNumber ];
          if( !$file || !is_readable($file) ){
            $this->setState('postrun');
            return false;
          }

        // Debug
          debugMsg(' - Found for Queue ' . $file);

        // Translate
          $root = AKFactory::get('kickstart.setup.destdir');
          $this->fileDetails = (object)array(
            'type'          => 'file',
            'source'        => $file,
            'target'        => $root . substr($file, strlen($this->getFilename())),
            'realTarget'    => null,
            'readIndex'     => 0,
            'lastReadIndex' => 0,
            'size'          => filesize( $file ),
            'permissions'   => fileperms( $file ),
            'timestamp'     => filemtime( $file )
            );

      }

    // Open
      debugMsg(' - Opening file ' . $this->fileDetails->source);
      $this->fp = @fopen($this->fileDetails->source, 'rb');
      if( $this->fp === false ){
        debugMsg('Could not open file - crash imminent');
        return false;
      }
      fseek($this->fp, (int)$this->fileDetails->readIndex);

    // Allow path translation
      if( AKFactory::get('kickstart.setup.restoreperms', false) ){
        $this->fileDetails->realTarget = $this->postProcEngine->processFilename( $this->fileDetails->target, $this->fileDetails->permissions );
      }
      else {
        $this->fileDetails->realTarget = $this->postProcEngine->processFilename( $this->fileDetails->target );
      }
      $this->postProcEngine->timestamp = $this->fileDetails->timestamp;

    // Create Missing Folders
      $this->createDirectory();

    // Complete
      return true;

  }

  /**
   * [processFileData description]
   * @return [type] [description]
   */
  protected function processFileData(){

    // Validate
      if( !is_resource($this->fp) ){
        $this->nextFile( true );
      }
      if( !is_resource($this->fp) ){
        debugMsg( ['** Error Reading File / Missing Resource', $this->fileDetails] );
        $this->setError( AKText::sprintf('COULDNT_READ_FILE', $target) );
        return false;
      }

    // Stage
      $size      = $this->fileDetails->size;
      $source    = $this->fileDetails->source;
      $target    = $this->fileDetails->realTarget;
      $readIndex =& $this->fileDetails->readIndex;

    // Set Permissions if not started
      if( ($readIndex == 0) && !AKFactory::get('kickstart.setup.dryrun','0') ){
        $this->setCorrectPermissions( $source );
      }

    // Open the output file
      if( !AKFactory::get('kickstart.setup.dryrun','0') ){
        $ignore = AKFactory::get('kickstart.setup.ignoreerrors', false) || $this->isIgnoredDirectory($source);
        if( $readIndex == 0 ){
          $outfp = @fopen( $target, 'wb' );
        }
        else {
          $outfp = @fopen( $target, 'ab' );
        }
        // Can we write to the file?
        if( ($outfp === false) && (!$ignore) ) {
          // An error occured
          debugMsg('Could not write to output file');
          $this->setError( AKText::sprintf('COULDNT_WRITE_FILE', $target) );
          return false;
        }
      }

    // Mark Last
      $this->fileDetails->lastReadIndex = $this->fileDetails->readIndex;

    // Reference to the global timer
      $timer = AKFactory::getTimer();
      $toReadBytes = 0;
      $leftBytes = $size - $readIndex;

    // Loop while there's data to read and enough time to do it
      while( ($leftBytes > 0) && ($timer->getTimeLeft() > 0) ){
        $toReadBytes = ($leftBytes > $this->chunkSize) ? $this->chunkSize : $leftBytes;
        $data = $this->fread( $this->fp, $toReadBytes );
        $reallyReadBytes = akstringlen($data);
        $leftBytes -= $reallyReadBytes;
        $readIndex += $reallyReadBytes;
        $this->totalWritten += $reallyReadBytes;
        if($reallyReadBytes < $toReadBytes){
          debugMsg('Not enough data in file / the file is corrupt.');
          $this->setError( AKText::_('ERR_CORRUPT_FILE') );
          return false;
        }
        if( !AKFactory::get('kickstart.setup.dryrun','0') )
          if(is_resource($outfp))
            @fwrite( $outfp, $data );
      }

    // Debug
      debugMsg(' - Wrote ['. $readIndex .' of '. $size .'] to file ' . $target);

    // Close the file pointer
      if( !AKFactory::get('kickstart.setup.dryrun','0') )
        if(is_resource($outfp))
          @fclose($outfp);

    // Did we Bail from Timeout?
      if( $leftBytes > 0 ){
        $this->runState = AK_STATE_DATA;
        return false;
      }

    // We finished!
      $this->runState = AK_STATE_DATAREAD;
      $readIndex = 0;

    // Complete
      return true;

  }


  /**
   * Scans for archive parts
   */
  protected function scanArchives()
  {

    // Reset
      $this->currentPartNumber = -1;
      $this->currentPartOffset = 0;
      $this->runState          = AK_STATE_NOFILE;
      $this->totalSize         = 0;
      $this->totalWritten      = 0;
      $this->fileCount         = 0;
      $this->fileList          = array();

    // Scan
      $this->__scanFolderRecursively( $this->getFilename() );

    // Send start of file notification
      $message = new stdClass;
      $message->type = 'totalsize';
      $message->content = new stdClass;
      $message->content->totalsize = $this->totalSize;
      $message->content->filelist  = array();
      $this->notify($message);

  }

  /**
   * [__scanFolderRecursively description]
   * @param  [type] $path [description]
   * @return [type]       [description]
   */
  protected function __scanFolderRecursively( $base, $path=null, $seekPart=null, $partCount=0 ){
    $files = scandir( $base.DS.$path );
    foreach( $files AS $file ){
      if( !preg_match('/^\.+$/', $file) ){
        if( is_dir($base.DS.$path.$file) ){
          $res = $this->__scanFolderRecursively($base, $path.$file.DS, $seekPart, $partCount);
          if( $res )
            return $res;
        }
        else if( is_readable($base.DS.$path.$file) ){
          $this->fileCount++;
          $this->totalSize += filesize($base.DS.$path.$file);
          $this->fileList[] = $base.DS.$path.$file;
          $partCount++;
          if( !is_null($seekPart) && $partCount > $seekPart )
            return $base.DS.$path.$file;
        }
      }
    }
  }

}

