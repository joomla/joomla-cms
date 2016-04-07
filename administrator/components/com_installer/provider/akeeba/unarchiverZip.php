<?php

/**
 * ZIP archive extraction class
 *
 * Since the file data portion of ZIP and JPA are similarly structured (it's empty for dirs,
 * linked node name for symlinks, dumped binary data for no compressions and dumped gzipped
 * binary data for gzip compression) we just have to subclass AKUnarchiverJPA and change the
 * header reading bits. Reusable code ;)
 */
class AKUnarchiverZIP extends AKUnarchiverJPA
{
  var $expectDataDescriptor = false;

  protected function readArchiveHeader()
  {
    debugMsg('Preparing to read archive header');
    // Initialize header data array
    $this->archiveHeaderData = new stdClass();

    // Open the first part
    debugMsg('Opening the first part');
    $this->nextFile();

    // Fail for unreadable files
    if( $this->fp === false ) {
      debugMsg('The first part is not readable');
      return false;
    }

    // Read a possible multipart signature
    $sigBinary = fread( $this->fp, 4 );
    $headerData = unpack('Vsig', $sigBinary);

    // Roll back if it's not a multipart archive
    if( $headerData['sig'] == 0x04034b50 ) {
      debugMsg('The archive is not multipart');
      fseek($this->fp, -4, SEEK_CUR);
    } else {
      debugMsg('The archive is multipart');
    }

    $multiPartSigs = array(
      0x08074b50,    // Multi-part ZIP
      0x30304b50,    // Multi-part ZIP (alternate)
      0x04034b50    // Single file
    );
    if( !in_array($headerData['sig'], $multiPartSigs) )
    {
      debugMsg('Invalid header signature '.dechex($headerData['sig']));
      $this->setError(AKText::_('ERR_CORRUPT_ARCHIVE'));
      return false;
    }

    $this->currentPartOffset = @ftell($this->fp);
    debugMsg('Current part offset after reading header: '.$this->currentPartOffset);

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
      debugMsg('Opening next archive part');
      $this->nextFile();
    }

    if($this->expectDataDescriptor)
    {
      // The last file had bit 3 of the general purpose bit flag set. This means that we have a
      // 12 byte data descriptor we need to skip. To make things worse, there might also be a 4
      // byte optional data descriptor header (0x08074b50).
      $junk = @fread($this->fp, 4);
      $junk = unpack('Vsig', $junk);
      if($junk['sig'] == 0x08074b50) {
        // Yes, there was a signature
        $junk = @fread($this->fp, 12);
        debugMsg('Data descriptor (w/ header) skipped at '.(ftell($this->fp)-12));
      } else {
        // No, there was no signature, just read another 8 bytes
        $junk = @fread($this->fp, 8);
        debugMsg('Data descriptor (w/out header) skipped at '.(ftell($this->fp)-8));
      }

      // And check for EOF, too
      if( $this->isEOF(true) ) {
        debugMsg('EOF before reading header');

        $this->nextFile();
      }
    }

    // Get and decode Local File Header
    $headerBinary = fread($this->fp, 30);
    $headerData = unpack('Vsig/C2ver/vbitflag/vcompmethod/vlastmodtime/vlastmoddate/Vcrc/Vcompsize/Vuncomp/vfnamelen/veflen', $headerBinary);

    // Check signature
    if(!( $headerData['sig'] == 0x04034b50 ))
    {
      debugMsg('Not a file signature at '.(ftell($this->fp)-4));

      // The signature is not the one used for files. Is this a central directory record (i.e. we're done)?
      if($headerData['sig'] == 0x02014b50)
      {
        debugMsg('EOCD signature at '.(ftell($this->fp)-4));
        // End of ZIP file detected. We'll just skip to the end of file...
        while( $this->nextFile() ) {};
        @fseek($this->fp, 0, SEEK_END); // Go to EOF
        return false;
      }
      else
      {
        debugMsg( 'Invalid signature ' . dechex($headerData['sig']) . ' at '.ftell($this->fp) );
        $this->setError(AKText::_('ERR_CORRUPT_ARCHIVE'));
        return false;
      }
    }

    // If bit 3 of the bitflag is set, expectDataDescriptor is true
    $this->expectDataDescriptor = ($headerData['bitflag'] & 4) == 4;

    $this->fileHeader = new stdClass();
    $this->fileHeader->timestamp = 0;

    // Read the last modified data and time
    $lastmodtime = $headerData['lastmodtime'];
    $lastmoddate = $headerData['lastmoddate'];

    if($lastmoddate && $lastmodtime)
    {
      // ----- Extract time
      $v_hour = ($lastmodtime & 0xF800) >> 11;
      $v_minute = ($lastmodtime & 0x07E0) >> 5;
      $v_seconde = ($lastmodtime & 0x001F)*2;

      // ----- Extract date
      $v_year = (($lastmoddate & 0xFE00) >> 9) + 1980;
      $v_month = ($lastmoddate & 0x01E0) >> 5;
      $v_day = $lastmoddate & 0x001F;

      // ----- Get UNIX date format
      $this->fileHeader->timestamp = @mktime($v_hour, $v_minute, $v_seconde, $v_month, $v_day, $v_year);
    }

    $isBannedFile = false;

    $this->fileHeader->compressed  = $headerData['compsize'];
    $this->fileHeader->uncompressed  = $headerData['uncomp'];
    $nameFieldLength        = $headerData['fnamelen'];
    $extraFieldLength        = $headerData['eflen'];

    // Read filename field
    $this->fileHeader->file      = fread( $this->fp, $nameFieldLength );

    // Handle file renaming
    $isRenamed = false;
    if(is_array($this->renameFiles) && (count($this->renameFiles) > 0) )
    {
      if(array_key_exists($this->fileHeader->file, $this->renameFiles))
      {
        $this->fileHeader->file = $this->renameFiles[$this->fileHeader->file];
        $isRenamed = true;
      }
    }

    // Handle directory renaming
    $isDirRenamed = false;
    if(is_array($this->renameDirs) && (count($this->renameDirs) > 0)) {
      if(array_key_exists(dirname($this->fileHeader->file), $this->renameDirs)) {
        $file = rtrim($this->renameDirs[dirname($this->fileHeader->file)],'/').'/'.basename($this->fileHeader->file);
        $isRenamed = true;
        $isDirRenamed = true;
      }
    }

    // Read extra field if present
    if($extraFieldLength > 0) {
      $extrafield = fread( $this->fp, $extraFieldLength );
    }

    debugMsg( '*'.ftell($this->fp).' IS START OF '.$this->fileHeader->file. ' ('.$this->fileHeader->compressed.' bytes)' );


    // Decide filetype -- Check for directories
    $this->fileHeader->type = 'file';
    if( strrpos($this->fileHeader->file, '/') == strlen($this->fileHeader->file) - 1 ) $this->fileHeader->type = 'dir';
    // Decide filetype -- Check for symbolic links
    if( ($headerData['ver1'] == 10) && ($headerData['ver2'] == 3) )$this->fileHeader->type = 'link';

    switch( $headerData['compmethod'] )
    {
      case 0:
        $this->fileHeader->compression = 'none';
        break;
      case 8:
        $this->fileHeader->compression = 'gzip';
        break;
    }

    // Find hard-coded banned files
    if( (basename($this->fileHeader->file) == ".") || (basename($this->fileHeader->file) == "..") )
    {
      $isBannedFile = true;
    }

    // Also try to find banned files passed in class configuration
    if((count($this->skipFiles) > 0) && (!$isRenamed))
    {
      if(in_array($this->fileHeader->file, $this->skipFiles))
      {
        $isBannedFile = true;
      }
    }

    // If we have a banned file, let's skip it
    if($isBannedFile)
    {
      // Advance the file pointer, skipping exactly the size of the compressed data
      $seekleft = $this->fileHeader->compressed;
      while($seekleft > 0)
      {
        // Ensure that we can seek past archive part boundaries
        $curSize = @filesize($this->archiveList[$this->currentPartNumber]);
        $curPos = @ftell($this->fp);
        $canSeek = $curSize - $curPos;
        if($canSeek > $seekleft) $canSeek = $seekleft;
        @fseek( $this->fp, $canSeek, SEEK_CUR );
        $seekleft -= $canSeek;
        if($seekleft) $this->nextFile();
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
    if($this->fileHeader->type == 'file')
    {
      $this->fileHeader->realFile = $this->postProcEngine->processFilename( $this->fileHeader->file );
    }
    elseif($this->fileHeader->type == 'dir')
    {
      $this->fileHeader->timestamp = 0;

      $dir = $this->fileHeader->file;

      $this->postProcEngine->createDirRecursive( $this->fileHeader->file, 0755 );
      $this->postProcEngine->processFilename(null);
    }
    else
    {
      // Symlink; do not post-process
      $this->fileHeader->timestamp = 0;
      $this->postProcEngine->processFilename(null);
    }

    $this->createDirectory();

    // Header is read
    $this->runState = AK_STATE_HEADER;

    return true;
  }

}

