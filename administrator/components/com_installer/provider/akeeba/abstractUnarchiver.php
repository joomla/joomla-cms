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
 * The base class of unarchiver classes
 */
abstract class AKAbstractUnarchiver extends AKAbstractPart
{
  /** @var string Archive filename */
  protected $filename = null;

  /** @var array List of the names of all archive parts */
  public $archiveList = array();

  /** @var int The total size of all archive parts */
  public $totalSize = array();

  /** @var integer Current archive part number */
  protected $currentPartNumber = -1;

  /** @var integer The offset inside the current part */
  protected $currentPartOffset = 0;

  /** @var bool Should I restore permissions? */
  protected $flagRestorePermissions = false;

  /** @var AKAbstractPostproc Post processing class */
  protected $postProcEngine = null;

  /** @var string Absolute path to prepend to extracted files */
  protected $addPath = '';

  /** @var array Which files to rename */
  public $renameFiles = array();

  /** @var array Which directories to rename */
  public $renameDirs = array();

  /** @var array Which files to skip */
  public $skipFiles = array();

  /** @var integer Chunk size for processing */
  protected $chunkSize = 524288;

  /** @var resource File pointer to the current archive part file */
  protected $fp = null;

  /** @var int Run state when processing the current archive file */
  protected $runState = null;

  /** @var stdClass File header data, as read by the readFileHeader() method */
  protected $fileHeader = null;

  /** @var int How much of the uncompressed data we've read so far */
  protected $dataReadLength = 0;

  /** @var array Unwriteable files in these directories are always ignored and do not cause errors when not extracted */
  protected $ignoreDirectories = array();

  /**
   * Public constructor
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Wakeup function, called whenever the class is unserialized
   */
  public function __wakeup()
  {
    if($this->currentPartNumber >= 0)
    {
      $this->fp = @fopen($this->archiveList[$this->currentPartNumber], 'rb');
      if( (is_resource($this->fp)) && ($this->currentPartOffset > 0) )
      {
        @fseek($this->fp, $this->currentPartOffset);
      }
    }
  }

  /**
   * Sleep function, called whenever the class is serialized
   */
  public function shutdown()
  {
    if(is_resource($this->fp))
    {
      $this->currentPartOffset = @ftell($this->fp);
      @fclose($this->fp);
    }
  }

  /**
   * Implements the abstract _prepare() method
   */
  final protected function _prepare()
  {
    parent::__construct();

    if( count($this->_parametersArray) > 0 )
    {
      foreach($this->_parametersArray as $key => $value)
      {
        switch($key)
        {
          // Archive's absolute filename
          case 'filename':
            $this->filename = $value;

            // Sanity check
            if (!empty($value))
            {
              $value = strtolower($value);

              if (strlen($value) > 6)
              {
                if (
                  (substr($value, 0, 7) == 'http://')
                  || (substr($value, 0, 8) == 'https://')
                  || (substr($value, 0, 6) == 'ftp://')
                  || (substr($value, 0, 7) == 'ssh2://')
                  || (substr($value, 0, 6) == 'ssl://')
                )
                {
                  $this->setState('error', 'Invalid archive location');
                }
              }
            }



            break;

          // Should I restore permissions?
          case 'restore_permissions':
            $this->flagRestorePermissions = $value;
            break;

          // Should I use FTP?
          case 'post_proc':
            $this->postProcEngine = AKFactory::getpostProc($value);
            break;

          // Path to add in the beginning
          case 'add_path':
            $this->addPath = $value;
            $this->addPath = str_replace('\\','/',$this->addPath);
            $this->addPath = rtrim($this->addPath,'/');
            if(!empty($this->addPath)) $this->addPath .= '/';
            break;

          // Which files to rename (hash array)
          case 'rename_files':
            $this->renameFiles = $value;
            break;

          // Which files to rename (hash array)
          case 'rename_dirs':
            $this->renameDirs = $value;
            break;

          // Which files to skip (indexed array)
          case 'skip_files':
            $this->skipFiles = $value;
            break;

          // Which directories to ignore when we can't write files in them (indexed array)
          case 'ignoredirectories':
            $this->ignoreDirectories = $value;
            break;
        }
      }
    }

    $this->scanArchives();

    $this->readArchiveHeader();
    $errMessage = $this->getError();
    if(!empty($errMessage))
    {
      $this->setState('error', $errMessage);
    }
    else
    {
      $this->runState = AK_STATE_NOFILE;
      $this->setState('prepared');
    }
  }

  protected function _run()
  {
    if($this->getState() == 'postrun') return;

    $this->setState('running');

    $timer = AKFactory::getTimer();

    $status = true;
    while( $status && ($timer->getTimeLeft() > 0) )
    {
      switch( $this->runState )
      {
        case AK_STATE_NOFILE:
          debugMsg(__CLASS__.'::_run() - Reading file header');
          $status = $this->readFileHeader();
          if($status)
          {
            debugMsg(__CLASS__.'::_run() - Preparing to extract '.$this->fileHeader->realFile);
            // Send start of file notification
            $message = new stdClass;
            $message->type = 'startfile';
            $message->content = new stdClass;
            if( array_key_exists('realfile', get_object_vars($this->fileHeader)) ) {
              $message->content->realfile = $this->fileHeader->realFile;
            } else {
              $message->content->realfile = $this->fileHeader->file;
            }
            $message->content->file = $this->fileHeader->file;
            if( array_key_exists('compressed', get_object_vars($this->fileHeader)) ) {
              $message->content->compressed = $this->fileHeader->compressed;
            } else {
              $message->content->compressed = 0;
            }
            $message->content->uncompressed = $this->fileHeader->uncompressed;
            $this->notify($message);
          } else {
            debugMsg(__CLASS__.'::_run() - Could not read file header');
          }
          break;

        case AK_STATE_HEADER:
        case AK_STATE_DATA:
          debugMsg(__CLASS__.'::_run() - Processing file data');
          $status = $this->processFileData();
          break;

        case AK_STATE_DATAREAD:
        case AK_STATE_POSTPROC:
          debugMsg(__CLASS__.'::_run() - Calling post-processing class');
          $this->postProcEngine->timestamp = $this->fileHeader->timestamp;
          $status = $this->postProcEngine->process();
          $this->propagateFromObject( $this->postProcEngine );
          $this->runState = AK_STATE_DONE;
          break;

        case AK_STATE_DONE:
        default:
          if($status)
          {
            debugMsg(__CLASS__.'::_run() - Finished extracting file');
            // Send end of file notification
            $message = new stdClass;
            $message->type = 'endfile';
            $message->content = new stdClass;
            if( array_key_exists('realfile', get_object_vars($this->fileHeader)) ) {
              $message->content->realfile = $this->fileHeader->realFile;
            } else {
              $message->content->realfile = $this->fileHeader->file;
            }
            $message->content->file = $this->fileHeader->file;
            if( array_key_exists('compressed', get_object_vars($this->fileHeader)) ) {
              $message->content->compressed = $this->fileHeader->compressed;
            } else {
              $message->content->compressed = 0;
            }
            $message->content->uncompressed = $this->fileHeader->uncompressed;
            $this->notify($message);
          }
          $this->runState = AK_STATE_NOFILE;
          continue;
      }
    }

    $error = $this->getError();
    if( !$status && ($this->runState == AK_STATE_NOFILE) && empty( $error ) )
    {
      debugMsg(__CLASS__.'::_run() - Just finished');
      // We just finished
      $this->setState('postrun');
    }
    elseif( !empty($error) )
    {
      debugMsg(__CLASS__.'::_run() - Halted with an error:');
      debugMsg($error);
      $this->setState( 'error', $error );
    }
  }

  protected function _finalize()
  {
    // Nothing to do
    $this->setState('finished');
  }

  /**
   * Returns the base extension of the file, e.g. '.jpa'
   * @return string
   */
  protected function getFilename()
  {
    return $this->filename;
  }

  /**
   * Returns the base extension of the file, e.g. '.jpa'
   * @return string
   */
  private function getBaseExtension()
  {
    static $baseextension;

    if(empty($baseextension))
    {
      $basename = basename($this->getFilename());
      $lastdot = strrpos($basename,'.');
      $baseextension = substr($basename, $lastdot);
    }

    return $baseextension;
  }

  /**
   * Scans for archive parts
   */
  protected function scanArchives()
  {
    if(defined('KSDEBUG')) {
      @unlink('debug.txt');
    }
    debugMsg('Preparing to scan archives');

    $privateArchiveList = array();

    // Get the components of the archive filename
    $dirname = dirname($this->getFilename());
    $base_extension = $this->getBaseExtension();
    $basename = basename($this->getFilename(), $base_extension);
    $this->totalSize = 0;
    if( !is_dir($this->getFilename()) ){

      // Scan for multiple parts until we don't find any more of them
      $count = 0;
      $found = true;
      $this->archiveList = array();
      while($found)
      {
        ++$count;
        $extension = substr($base_extension, 0, 2).sprintf('%02d', $count);
        $filename = $dirname.DIRECTORY_SEPARATOR.$basename.$extension;
        $found = file_exists($filename);
        if($found)
        {
          debugMsg('- Found archive '.$filename);
          // Add yet another part, with a numeric-appended filename
          $this->archiveList[] = $filename;

          $filesize = @filesize($filename);
          $this->totalSize += $filesize;

          $privateArchiveList[] = array($filename, $filesize);
        }
        else
        {
          debugMsg('- Found archive '.$this->getFilename());
          // Add the last part, with the regular extension
          $this->archiveList[] = $this->getFilename();

          $filename = $this->getFilename();
          $filesize = @filesize($filename);
          $this->totalSize += $filesize;

          $privateArchiveList[] = array($filename, $filesize);
        }
      }
      debugMsg('Total archive parts: '.$count);

    }
    $this->currentPartNumber = -1;
    $this->currentPartOffset = 0;
    $this->runState = AK_STATE_NOFILE;

    // Send start of file notification
    $message = new stdClass;
    $message->type = 'totalsize';
    $message->content = new stdClass;
    $message->content->totalsize = $this->totalSize;
    $message->content->filelist = $privateArchiveList;
    $this->notify($message);

  }

  /**
   * Opens the next part file for reading
   */
  protected function nextFile()
  {
    debugMsg('Current part is ' . $this->currentPartNumber . '; opening the next part');
    ++$this->currentPartNumber;

    if($this->currentPartNumber > (count($this->archiveList) - 1))
    {
      $this->setState('postrun');
      return false;
    }

    if(is_resource($this->fp))
    {
      @fclose($this->fp);
    }

    debugMsg('Opening file ' . $this->archiveList[$this->currentPartNumber]);
    $this->fp = @fopen($this->archiveList[$this->currentPartNumber], 'rb');

    if($this->fp === false)
    {
      debugMsg('Could not open file - crash imminent');
    }

    fseek($this->fp, 0);
    $this->currentPartOffset = 0;

    return true;
  }

  /**
   * Returns true if we have reached the end of file
   * @param $local bool True to return EOF of the local file, false (default) to return if we have reached the end of the archive set
   * @return bool True if we have reached End Of File
   */
  protected function isEOF($local = false)
  {
    $eof = @feof($this->fp);

    if(!$eof)
    {
      // Border case: right at the part's end (eeeek!!!). For the life of me, I don't understand why
      // feof() doesn't report true. It expects the fp to be positioned *beyond* the EOF to report
      // true. Incredible! :(
      $position = @ftell($this->fp);
      $filesize = @filesize($this->archiveList[$this->currentPartNumber]);

      if($filesize <= 0) {
        // 2Gb or more files on a 32 bit version of PHP tend to get screwed up. Meh.
        $eof = false;
      } elseif($position >= $filesize) {
        $eof = true;
      }
    }

    if($local)
    {
      return $eof;
    }

    return $eof && ($this->currentPartNumber >= (count($this->archiveList)-1));
  }

  /**
   * Tries to make a directory user-writable so that we can write a file to it
   * @param $path string A path to a file
   */
  protected function setCorrectPermissions($path)
  {
    static $rootDir = null;

    if(is_null($rootDir)) {
      $rootDir = rtrim(AKFactory::get('kickstart.setup.destdir',''),'/\\');
    }

    $directory = rtrim(dirname($path),'/\\');
    if($directory != $rootDir) {
      // Is this an unwritable directory?
      if(!is_writeable($directory)) {
        $this->postProcEngine->chmod( $directory, 0755 );
      }
    }
    $this->postProcEngine->chmod( $path, 0644 );
  }

  /**
   * Concrete classes are supposed to use this method in order to read the archive's header and
   * prepare themselves to the point of being ready to extract the first file.
   */
  protected abstract function readArchiveHeader();

  /**
   * Concrete classes must use this method to read the file header
   * @return bool True if reading the file was successful, false if an error occured or we reached end of archive
   */
  protected abstract function readFileHeader();

  /**
   * Concrete classes must use this method to process file data. It must set $runState to AK_STATE_DATAREAD when
   * it's finished processing the file data.
   * @return bool True if processing the file data was successful, false if an error occured
   */
  protected abstract function processFileData();

  /**
   * Reads data from the archive and notifies the observer with the 'reading' message
   * @param $fp
   * @param $length
   */
  protected function fread($fp, $length = null)
  {
    if(is_numeric($length))
    {
      if($length > 0) {
        $data = fread($fp, $length);
      } else {
        $data = fread($fp, PHP_INT_MAX);
      }
    }
    else
    {
      $data = fread($fp, PHP_INT_MAX);
    }
    if($data === false) $data = '';

    // Send start of file notification
    $message = new stdClass;
    $message->type = 'reading';
    $message->content = new stdClass;
    $message->content->length = strlen($data);
    $this->notify($message);

    return $data;
  }

  /**
   * Is this file or directory contained in a directory we've decided to ignore
   * write errors for? This is useful to let the extraction work despite write
   * errors in the log, logs and tmp directories which MIGHT be used by the system
   * on some low quality hosts and Plesk-powered hosts.
   *
   * @param   string  $shortFilename  The relative path of the file/directory in the package
   *
   * @return  boolean  True if it belongs in an ignored directory
   */
  public function isIgnoredDirectory($shortFilename)
  {
    return false;
    if (substr($shortFilename, -1) == '/')
    {
      $check = substr($shortFilename, 0, -1);
    }
    else
    {
      $check = dirname($shortFilename);
    }

    return in_array($check, $this->ignoreDirectories);
  }
}

