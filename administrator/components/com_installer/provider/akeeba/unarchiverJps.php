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
 * Akeeba Restore
 * A JSON-powered JPA, JPS and ZIP archive extraction library
 *
 * @copyright   2010-2014 Nicholas K. Dionysopoulos / Akeeba Ltd.
 * @license     GNU GPL v2 or - at your option - any later version
 * @package     akeebabackup
 * @subpackage  kickstart
 */

/**
 * JPS archive extraction class
 */
class AKUnarchiverJPS extends AKUnarchiverJPA
{
  protected $archiveHeaderData = array();

  protected $password = '';

  public function __construct()
  {
    parent::__construct();

    $this->password = AKFactory::get('kickstart.jps.password','');
  }

  protected function readArchiveHeader()
  {
    // Initialize header data array
    $this->archiveHeaderData = new stdClass();

    // Open the first part
    $this->nextFile();

    // Fail for unreadable files
    if( $this->fp === false ) return false;

    // Read the signature
    $sig = fread( $this->fp, 3 );

    if ($sig != 'JPS')
    {
      // Not a JPA file
      $this->setError( AKText::_('ERR_NOT_A_JPS_FILE') );
      return false;
    }

    // Read and parse the known portion of header data (5 bytes)
    $bin_data = fread($this->fp, 5);
    $header_data = unpack('Cmajor/Cminor/cspanned/vextra', $bin_data);

    // Load any remaining header data (forward compatibility)
    $rest_length = $header_data['extra'];
    if( $rest_length > 0 )
      $junk = fread($this->fp, $rest_length);
    else
      $junk = '';

    // Temporary array with all the data we read
    $temp = array(
      'signature' =>       $sig,
      'major' =>         $header_data['major'],
      'minor' =>         $header_data['minor'],
      'spanned' =>       $header_data['spanned']
    );
    // Array-to-object conversion
    foreach($temp as $key => $value)
    {
      $this->archiveHeaderData->{$key} = $value;
    }

    $this->currentPartOffset = @ftell($this->fp);

    $this->dataReadLength = 0;

    return true;
  }

  /**
   * Concrete classes must use this method to read the file header
   * @return bool True if reading the file was successful, false if an error occured or we reached end of archive
   */
  protected function readFileHeader()
  {
    // If the current part is over, proceed to the next part please
    if( $this->isEOF(true) ) {
      $this->nextFile();
    }

    // Get and decode Entity Description Block
    $signature = fread($this->fp, 3);

    // Check for end-of-archive siganture
    if($signature == 'JPE')
    {
      $this->setState('postrun');
      return true;
    }

    $this->fileHeader = new stdClass();
    $this->fileHeader->timestamp = 0;

    // Check signature
    if( $signature != 'JPF' )
    {
      if($this->isEOF(true))
      {
        // This file is finished; make sure it's the last one
        $this->nextFile();
        if(!$this->isEOF(false))
        {
          $this->setError(AKText::sprintf('INVALID_FILE_HEADER', $this->currentPartNumber, $this->currentPartOffset));
          return false;
        }
        // We're just finished
        return false;
      }
      else
      {
        fseek($this->fp, -6, SEEK_CUR);
        $signature = fread($this->fp, 3);
        if($signature == 'JPE')
        {
          return false;
        }

        $this->setError(AKText::sprintf('INVALID_FILE_HEADER', $this->currentPartNumber, $this->currentPartOffset));
        return false;
      }
    }
    // This a JPA Entity Block. Process the header.

    $isBannedFile = false;

    // Read and decrypt the header
    $edbhData = fread($this->fp, 4);
    $edbh = unpack('vencsize/vdecsize', $edbhData);
    $bin_data = fread($this->fp, $edbh['encsize']);

    // Decrypt and truncate
    $bin_data = AKEncryptionAES::AESDecryptCBC($bin_data, $this->password, 128);
    $bin_data = substr($bin_data,0,$edbh['decsize']);

    // Read length of EDB and of the Entity Path Data
    $length_array = unpack('vpathsize', substr($bin_data,0,2) );
    // Read the path data
    $file = substr($bin_data,2,$length_array['pathsize']);

    // Handle file renaming
    $isRenamed = false;
    if(is_array($this->renameFiles) && (count($this->renameFiles) > 0) )
    {
      if(array_key_exists($file, $this->renameFiles))
      {
        $file = $this->renameFiles[$file];
        $isRenamed = true;
      }
    }

    // Handle directory renaming
    $isDirRenamed = false;
    if(is_array($this->renameDirs) && (count($this->renameDirs) > 0)) {
      if(array_key_exists(dirname($file), $this->renameDirs)) {
        $file = rtrim($this->renameDirs[dirname($file)],'/').'/'.basename($file);
        $isRenamed = true;
        $isDirRenamed = true;
      }
    }

    // Read and parse the known data portion
    $bin_data = substr($bin_data, 2 + $length_array['pathsize']);
    $header_data = unpack('Ctype/Ccompression/Vuncompsize/Vperms/Vfilectime', $bin_data);

    $this->fileHeader->timestamp = $header_data['filectime'];
    $compressionType = $header_data['compression'];

    // Populate the return array
    $this->fileHeader->file = $file;
    $this->fileHeader->uncompressed = $header_data['uncompsize'];
    switch($header_data['type'])
    {
      case 0:
        $this->fileHeader->type = 'dir';
        break;

      case 1:
        $this->fileHeader->type = 'file';
        break;

      case 2:
        $this->fileHeader->type = 'link';
        break;
    }
    switch( $compressionType )
    {
      case 0:
        $this->fileHeader->compression = 'none';
        break;
      case 1:
        $this->fileHeader->compression = 'gzip';
        break;
      case 2:
        $this->fileHeader->compression = 'bzip2';
        break;
    }
    $this->fileHeader->permissions = $header_data['perms'];

    // Find hard-coded banned files
    if( (basename($this->fileHeader->file) == ".") || (basename($this->fileHeader->file) == "..") )
    {
      $isBannedFile = true;
    }

    // Also try to find banned files passed in class configuration
    if((count($this->skipFiles) > 0) && (!$isRenamed) )
    {
      if(in_array($this->fileHeader->file, $this->skipFiles))
      {
        $isBannedFile = true;
      }
    }

    // If we have a banned file, let's skip it
    if($isBannedFile)
    {
      $done = false;
      while(!$done)
      {
        // Read the Data Chunk Block header
        $binMiniHead = fread($this->fp, 8);
        if( in_array( substr($binMiniHead,0,3), array('JPF','JPE') ) )
        {
          // Not a Data Chunk Block header, I am done skipping the file
          @fseek($this->fp,-8,SEEK_CUR); // Roll back the file pointer
          $done = true; // Mark as done
          continue; // Exit loop
        }
        else
        {
          // Skip forward by the amount of compressed data
          $miniHead = unpack('Vencsize/Vdecsize', $binMiniHead);
          @fseek($this->fp, $miniHead['encsize'], SEEK_CUR);
        }
      }

      $this->currentPartOffset = @ftell($this->fp);
      $this->runState = AK_STATE_DONE;
      return true;
    }

    // Last chance to prepend a path to the filename
    if(!empty($this->addPath) && !$isDirRenamed)
    {
      $this->fileHeader->file = $this->addPath.$this->fileHeader->file;
    }

    // Get the translated path name
    $restorePerms = AKFactory::get('kickstart.setup.restoreperms', false);
    if($this->fileHeader->type == 'file')
    {
      // Regular file; ask the postproc engine to process its filename
      if($restorePerms)
      {
        $this->fileHeader->realFile = $this->postProcEngine->processFilename( $this->fileHeader->file, $this->fileHeader->permissions );
      }
      else
      {
        $this->fileHeader->realFile = $this->postProcEngine->processFilename( $this->fileHeader->file );
      }
    }
    elseif($this->fileHeader->type == 'dir')
    {
      $dir = $this->fileHeader->file;
      $this->fileHeader->realFile = $dir;

      // Directory; just create it
      if($restorePerms)
      {
        $this->postProcEngine->createDirRecursive( $this->fileHeader->file, $this->fileHeader->permissions );
      }
      else
      {
        $this->postProcEngine->createDirRecursive( $this->fileHeader->file, 0755 );
      }
      $this->postProcEngine->processFilename(null);
    }
    else
    {
      // Symlink; do not post-process
      $this->postProcEngine->processFilename(null);
    }

    $this->createDirectory();

    // Header is read
    $this->runState = AK_STATE_HEADER;

    $this->dataReadLength = 0;

    return true;
  }

  /**
   * Concrete classes must use this method to process file data. It must set $runState to AK_STATE_DATAREAD when
   * it's finished processing the file data.
   * @return bool True if processing the file data was successful, false if an error occured
   */
  protected function processFileData()
  {
    switch( $this->fileHeader->type )
    {
      case 'dir':
        return $this->processTypeDir();
        break;

      case 'link':
        return $this->processTypeLink();
        break;

      case 'file':
        switch($this->fileHeader->compression)
        {
          case 'none':
            return $this->processTypeFileUncompressed();
            break;

          case 'gzip':
          case 'bzip2':
            return $this->processTypeFileCompressedSimple();
            break;

        }
        break;
    }
  }

  private function processTypeFileUncompressed()
  {
    // Uncompressed files are being processed in small chunks, to avoid timeouts
    if( ($this->dataReadLength == 0) && !AKFactory::get('kickstart.setup.dryrun','0') )
    {
      // Before processing file data, ensure permissions are adequate
      $this->setCorrectPermissions( $this->fileHeader->file );
    }

    // Open the output file
    if( !AKFactory::get('kickstart.setup.dryrun','0') )
    {
      $ignore = AKFactory::get('kickstart.setup.ignoreerrors', false) || $this->isIgnoredDirectory($this->fileHeader->file);
      if ($this->dataReadLength == 0) {
        $outfp = @fopen( $this->fileHeader->realFile, 'wb' );
      } else {
        $outfp = @fopen( $this->fileHeader->realFile, 'ab' );
      }

      // Can we write to the file?
      if( ($outfp === false) && (!$ignore) ) {
        // An error occured
        $this->setError( AKText::sprintf('COULDNT_WRITE_FILE', $this->fileHeader->realFile) );
        return false;
      }
    }

    // Does the file have any data, at all?
    if( $this->fileHeader->uncompressed == 0 )
    {
      // No file data!
      if( !AKFactory::get('kickstart.setup.dryrun','0') && is_resource($outfp) ) @fclose($outfp);
      $this->runState = AK_STATE_DATAREAD;
      return true;
    }
    else
    {
      $this->setError('An uncompressed file was detected; this is not supported by this archive extraction utility');
      return false;
    }

    return true;
  }

  private function processTypeFileCompressedSimple()
  {
    $timer = AKFactory::getTimer();

    // Files are being processed in small chunks, to avoid timeouts
    if( ($this->dataReadLength == 0) && !AKFactory::get('kickstart.setup.dryrun','0') )
    {
      // Before processing file data, ensure permissions are adequate
      $this->setCorrectPermissions( $this->fileHeader->file );
    }

    // Open the output file
    if( !AKFactory::get('kickstart.setup.dryrun','0') )
    {
      // Open the output file
      $outfp = @fopen( $this->fileHeader->realFile, 'wb' );

      // Can we write to the file?
      $ignore = AKFactory::get('kickstart.setup.ignoreerrors', false) || $this->isIgnoredDirectory($this->fileHeader->file);
      if( ($outfp === false) && (!$ignore) ) {
        // An error occured
        $this->setError( AKText::sprintf('COULDNT_WRITE_FILE', $this->fileHeader->realFile) );
        return false;
      }
    }

    // Does the file have any data, at all?
    if( $this->fileHeader->uncompressed == 0 )
    {
      // No file data!
      if( !AKFactory::get('kickstart.setup.dryrun','0') )
        if(is_resource($outfp)) @fclose($outfp);
      $this->runState = AK_STATE_DATAREAD;
      return true;
    }

    $leftBytes = $this->fileHeader->uncompressed - $this->dataReadLength;

    // Loop while there's data to write and enough time to do it
    while( ($leftBytes > 0) && ($timer->getTimeLeft() > 0) )
    {
      // Read the mini header
      $binMiniHeader = fread($this->fp, 8);
      $reallyReadBytes = akstringlen($binMiniHeader);
      if($reallyReadBytes < 8)
      {
        // We read less than requested! Why? Did we hit local EOF?
        if( $this->isEOF(true) && !$this->isEOF(false) )
        {
          // Yeap. Let's go to the next file
          $this->nextFile();
          // Retry reading the header
          $binMiniHeader = fread($this->fp, 8);
          $reallyReadBytes = akstringlen($binMiniHeader);
          // Still not enough data? If so, the archive is corrupt or missing parts.
          if($reallyReadBytes < 8) {
            $this->setError( AKText::_('ERR_CORRUPT_ARCHIVE') );
            return false;
          }
        }
        else
        {
          // Nope. The archive is corrupt
          $this->setError( AKText::_('ERR_CORRUPT_ARCHIVE') );
          return false;
        }
      }

      // Read the encrypted data
      $miniHeader = unpack('Vencsize/Vdecsize', $binMiniHeader);
      $toReadBytes = $miniHeader['encsize'];
      $data = $this->fread( $this->fp, $toReadBytes );
      $reallyReadBytes = akstringlen($data);
      if($reallyReadBytes < $toReadBytes)
      {
        // We read less than requested! Why? Did we hit local EOF?
        if( $this->isEOF(true) && !$this->isEOF(false) )
        {
          // Yeap. Let's go to the next file
          $this->nextFile();
          // Read the rest of the data
          $toReadBytes -= $reallyReadBytes;
          $restData = $this->fread( $this->fp, $toReadBytes );
          $reallyReadBytes = akstringlen($restData);
          if($reallyReadBytes < $toReadBytes) {
            $this->setError( AKText::_('ERR_CORRUPT_ARCHIVE') );
            return false;
          }
          if(akstringlen($data) == 0) {
            $data = $restData;
          } else {
            $data .= $restData;
          }
        }
        else
        {
          // Nope. The archive is corrupt
          $this->setError( AKText::_('ERR_CORRUPT_ARCHIVE') );
          return false;
        }
      }

      // Decrypt the data
      $data = AKEncryptionAES::AESDecryptCBC($data, $this->password, 128);

      // Is the length of the decrypted data less than expected?
      $data_length = akstringlen($data);
      if($data_length < $miniHeader['decsize']) {
        $this->setError(AKText::_('ERR_INVALID_JPS_PASSWORD'));
        return false;
      }

      // Trim the data
      $data = substr($data,0,$miniHeader['decsize']);

      // Decompress
      $data = gzinflate($data);
      $unc_len = akstringlen($data);

      // Write the decrypted data
      if( !AKFactory::get('kickstart.setup.dryrun','0') )
        if(is_resource($outfp)) @fwrite( $outfp, $data, akstringlen($data) );

      // Update the read length
      $this->dataReadLength += $unc_len;
      $leftBytes = $this->fileHeader->uncompressed - $this->dataReadLength;
    }

    // Close the file pointer
    if( !AKFactory::get('kickstart.setup.dryrun','0') )
      if(is_resource($outfp)) @fclose($outfp);

    // Was this a pre-timeout bail out?
    if( $leftBytes > 0 )
    {
      $this->runState = AK_STATE_DATA;
    }
    else
    {
      // Oh! We just finished!
      $this->runState = AK_STATE_DATAREAD;
      $this->dataReadLength = 0;
    }

    return true;
  }

  /**
   * Process the file data of a link entry
   * @return bool
   */
  private function processTypeLink()
  {

    // Does the file have any data, at all?
    if( $this->fileHeader->uncompressed == 0 )
    {
      // No file data!
      $this->runState = AK_STATE_DATAREAD;
      return true;
    }

    // Read the mini header
    $binMiniHeader = fread($this->fp, 8);
    $reallyReadBytes = akstringlen($binMiniHeader);
    if($reallyReadBytes < 8)
    {
      // We read less than requested! Why? Did we hit local EOF?
      if( $this->isEOF(true) && !$this->isEOF(false) )
      {
        // Yeap. Let's go to the next file
        $this->nextFile();
        // Retry reading the header
        $binMiniHeader = fread($this->fp, 8);
        $reallyReadBytes = akstringlen($binMiniHeader);
        // Still not enough data? If so, the archive is corrupt or missing parts.
        if($reallyReadBytes < 8) {
          $this->setError( AKText::_('ERR_CORRUPT_ARCHIVE') );
          return false;
        }
      }
      else
      {
        // Nope. The archive is corrupt
        $this->setError( AKText::_('ERR_CORRUPT_ARCHIVE') );
        return false;
      }
    }

    // Read the encrypted data
    $miniHeader = unpack('Vencsize/Vdecsize', $binMiniHeader);
    $toReadBytes = $miniHeader['encsize'];
    $data = $this->fread( $this->fp, $toReadBytes );
    $reallyReadBytes = akstringlen($data);
    if($reallyReadBytes < $toReadBytes)
    {
      // We read less than requested! Why? Did we hit local EOF?
      if( $this->isEOF(true) && !$this->isEOF(false) )
      {
        // Yeap. Let's go to the next file
        $this->nextFile();
        // Read the rest of the data
        $toReadBytes -= $reallyReadBytes;
        $restData = $this->fread( $this->fp, $toReadBytes );
        $reallyReadBytes = akstringlen($data);
        if($reallyReadBytes < $toReadBytes) {
          $this->setError( AKText::_('ERR_CORRUPT_ARCHIVE') );
          return false;
        }
        $data .= $restData;
      }
      else
      {
        // Nope. The archive is corrupt
        $this->setError( AKText::_('ERR_CORRUPT_ARCHIVE') );
        return false;
      }
    }

    // Decrypt the data
    $data = AKEncryptionAES::AESDecryptCBC($data, $this->password, 128);

    // Is the length of the decrypted data less than expected?
    $data_length = akstringlen($data);
    if($data_length < $miniHeader['decsize']) {
      $this->setError(AKText::_('ERR_INVALID_JPS_PASSWORD'));
      return false;
    }

    // Trim the data
    $data = substr($data,0,$miniHeader['decsize']);

    // Try to remove an existing file or directory by the same name
    if(file_exists($this->fileHeader->file)) { @unlink($this->fileHeader->file); @rmdir($this->fileHeader->file); }
    // Remove any trailing slash
    if(substr($this->fileHeader->file, -1) == '/') $this->fileHeader->file = substr($this->fileHeader->file, 0, -1);
    // Create the symlink - only possible within PHP context. There's no support built in the FTP protocol, so no postproc use is possible here :(

    if( !AKFactory::get('kickstart.setup.dryrun','0') )
    {
      @symlink($data, $this->fileHeader->file);
    }

    $this->runState = AK_STATE_DATAREAD;

    return true; // No matter if the link was created!
  }

  /**
   * Process the file data of a directory entry
   * @return bool
   */
  private function processTypeDir()
  {
    // Directory entries in the JPA do not have file data, therefore we're done processing the entry
    $this->runState = AK_STATE_DATAREAD;
    return true;
  }

  /**
   * Creates the directory this file points to
   */
  protected function createDirectory()
  {
    if( AKFactory::get('kickstart.setup.dryrun','0') ) return true;

    // Do we need to create a directory?
    $lastSlash = strrpos($this->fileHeader->realFile, '/');
    $dirName = substr( $this->fileHeader->realFile, 0, $lastSlash);
    $perms = $this->flagRestorePermissions ? $retArray['permissions'] : 0755;
    $ignore = AKFactory::get('kickstart.setup.ignoreerrors', false) || $this->isIgnoredDirectory($dirName);
    if( ($this->postProcEngine->createDirRecursive($dirName, $perms) == false) && (!$ignore) ) {
      $this->setError( AKText::sprintf('COULDNT_CREATE_DIR', $dirName) );
      return false;
    }
    else
    {
      return true;
    }
  }
}

