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
 * Hybrid direct / FTP mode file writer
 */
class AKPostprocHybrid extends AKAbstractPostproc
{

  /** @var bool Should I use the FTP layer? */
  public $useFTP = false;

  /** @var bool Should I use FTP over implicit SSL? */
  public $useSSL = false;

  /** @var bool use Passive mode? */
  public $passive = true;

  /** @var string FTP host name */
  public $host = '';

  /** @var int FTP port */
  public $port = 21;

  /** @var string FTP user name */
  public $user = '';

  /** @var string FTP password */
  public $pass = '';

  /** @var string FTP initial directory */
  public $dir = '';

  /** @var resource The FTP handle */
  private $handle = null;

  /** @var string The temporary directory where the data will be stored */
  private $tempDir = '';

  /** @var null The FTP connection handle */
  private $_handle = null;

  /**
   * Public constructor. Tries to connect to the FTP server.
   */
  public function __construct()
  {
    parent::__construct();

    $this->useFTP = true;
    $this->useSSL = AKFactory::get('kickstart.ftp.ssl', false);
    $this->passive = AKFactory::get('kickstart.ftp.passive', true);
    $this->host = AKFactory::get('kickstart.ftp.host', '');
    $this->port = AKFactory::get('kickstart.ftp.port', 21);
    $this->user = AKFactory::get('kickstart.ftp.user', '');
    $this->pass = AKFactory::get('kickstart.ftp.pass', '');
    $this->dir = AKFactory::get('kickstart.ftp.dir', '');
    $this->tempDir = AKFactory::get('kickstart.ftp.tempdir', '');

    if (trim($this->port) == '')
    {
      $this->port = 21;
    }

    // If FTP is not configured, skip it altogether
    if (empty($this->host) || empty($this->user) || empty($this->pass))
    {
      $this->useFTP = false;
    }

    // Try to connect to the FTP server
    $connected = $this->connect();

    // If the connection fails, skip FTP altogether
    if (!$connected)
    {
      $this->useFTP = false;
    }

    if ($connected)
    {
      if (!empty($this->tempDir))
      {
        $tempDir = rtrim($this->tempDir, '/\\') . '/';
        $writable = $this->isDirWritable($tempDir);
      }
      else
      {
        $tempDir = '';
        $writable = false;
      }

      if (!$writable)
      {
        // Default temporary directory is the current root
        $tempDir = KSROOTDIR;
        if (empty($tempDir))
        {
          // Oh, we have no directory reported!
          $tempDir = '.';
        }
        $absoluteDirToHere = $tempDir;
        $tempDir = rtrim(str_replace('\\', '/', $tempDir), '/');
        if (!empty($tempDir))
        {
          $tempDir .= '/';
        }
        $this->tempDir = $tempDir;
        // Is this directory writable?
        $writable = $this->isDirWritable($tempDir);
      }

      if (!$writable)
      {
        // Nope. Let's try creating a temporary directory in the site's root.
        $tempDir = $absoluteDirToHere . '/kicktemp';
        $this->createDirRecursive($tempDir, 0777);
        // Try making it writable...
        $this->fixPermissions($tempDir);
        $writable = $this->isDirWritable($tempDir);
      }

      // Was the new directory writable?
      if (!$writable)
      {
        // Let's see if the user has specified one
        $userdir = AKFactory::get('kickstart.ftp.tempdir', '');
        if (!empty($userdir))
        {
          // Is it an absolute or a relative directory?
          $absolute = false;
          $absolute = $absolute || (substr($userdir, 0, 1) == '/');
          $absolute = $absolute || (substr($userdir, 1, 1) == ':');
          $absolute = $absolute || (substr($userdir, 2, 1) == ':');
          if (!$absolute)
          {
            // Make absolute
            $tempDir = $absoluteDirToHere . $userdir;
          }
          else
          {
            // it's already absolute
            $tempDir = $userdir;
          }
          // Does the directory exist?
          if (is_dir($tempDir))
          {
            // Yeah. Is it writable?
            $writable = $this->isDirWritable($tempDir);
          }
        }
      }
      $this->tempDir = $tempDir;

      if (!$writable)
      {
        // No writable directory found!!!
        $this->setError(AKText::_('FTP_TEMPDIR_NOT_WRITABLE'));
      }
      else
      {
        AKFactory::set('kickstart.ftp.tempdir', $tempDir);
        $this->tempDir = $tempDir;
      }
    }
  }

  /**
   * Called after unserialisation, tries to reconnect to FTP
   */
  function __wakeup()
  {
    if ($this->useFTP)
    {
      $this->connect();
    }
  }

  function __destruct()
  {
    if (!$this->useFTP)
    {
      @ftp_close($this->handle);
    }
  }

  /**
   * Tries to connect to the FTP server
   *
   * @return bool
   */
  public function connect()
  {
    if (!$this->useFTP)
    {
      return false;
    }

    // Connect to server, using SSL if so required
    if ($this->useSSL)
    {
      $this->handle = @ftp_ssl_connect($this->host, $this->port);
    }
    else
    {
      $this->handle = @ftp_connect($this->host, $this->port);
    }
    if ($this->handle === false)
    {
      $this->setError(AKText::_('WRONG_FTP_HOST'));

      return false;
    }

    // Login
    if (!@ftp_login($this->handle, $this->user, $this->pass))
    {
      $this->setError(AKText::_('WRONG_FTP_USER'));
      @ftp_close($this->handle);

      return false;
    }

    // Change to initial directory
    if (!@ftp_chdir($this->handle, $this->dir))
    {
      $this->setError(AKText::_('WRONG_FTP_PATH1'));
      @ftp_close($this->handle);

      return false;
    }

    // Enable passive mode if the user requested it
    if ($this->passive)
    {
      @ftp_pasv($this->handle, true);
    }
    else
    {
      @ftp_pasv($this->handle, false);
    }

    // Try to download ourselves
    $testFilename = defined('KSSELFNAME') ? KSSELFNAME : basename(__FILE__);
    $tempHandle = fopen('php://temp', 'r+');

    if (@ftp_fget($this->handle, $tempHandle, $testFilename, FTP_ASCII, 0) === false)
    {
      $this->setError(AKText::_('WRONG_FTP_PATH2'));
      @ftp_close($this->handle);
      fclose($tempHandle);

      return false;
    }

    fclose($tempHandle);

    return true;
  }

  /**
   * Post-process an extracted file, using FTP or direct file writes to move it
   *
   * @return bool
   */
  public function process()
  {
    if (is_null($this->tempFilename))
    {
      // If an empty filename is passed, it means that we shouldn't do any post processing, i.e.
      // the entity was a directory or symlink
      return true;
    }

    $remotePath = dirname($this->filename);
    $removePath = AKFactory::get('kickstart.setup.destdir', '');
    $root = rtrim($removePath, '/\\');

    if (!empty($removePath))
    {
      $removePath = ltrim($removePath, "/");
      $remotePath = ltrim($remotePath, "/");
      $left = substr($remotePath, 0, strlen($removePath));

      if ($left == $removePath)
      {
        $remotePath = substr($remotePath, strlen($removePath));
      }
    }

    $absoluteFSPath = dirname($this->filename);
    $relativeFTPPath = trim($remotePath, '/');
    $absoluteFTPPath = '/' . trim($this->dir, '/') . '/' . trim($remotePath, '/');
    $onlyFilename = basename($this->filename);

    $remoteName = $absoluteFTPPath . '/' . $onlyFilename;

    // Does the directory exist?
    if (!is_dir($root . '/' . $absoluteFSPath))
    {
      $ret = $this->createDirRecursive($absoluteFSPath, 0755);

      if (($ret === false) && ($this->useFTP))
      {
        $ret = @ftp_chdir($this->handle, $absoluteFTPPath);
      }

      if ($ret === false)
      {
        $this->setError(AKText::sprintf('FTP_COULDNT_UPLOAD', $this->filename));

        return false;
      }
    }

    if ($this->useFTP)
    {
      $ret = @ftp_chdir($this->handle, $absoluteFTPPath);
    }

    // Try copying directly
    $ret = @copy($this->tempFilename, $root . '/' . $this->filename);

    if ($ret === false)
    {
      $this->fixPermissions($this->filename);
      $this->unlink($this->filename);

      $ret = @copy($this->tempFilename, $root . '/' . $this->filename);
    }

    if ($this->useFTP && ($ret === false))
    {
      $ret = @ftp_put($this->handle, $remoteName, $this->tempFilename, FTP_BINARY);

      if ($ret === false)
      {
        // If we couldn't create the file, attempt to fix the permissions in the PHP level and retry!
        $this->fixPermissions($this->filename);
        $this->unlink($this->filename);

        $fp = @fopen($this->tempFilename, 'rb');
        if ($fp !== false)
        {
          $ret = @ftp_fput($this->handle, $remoteName, $fp, FTP_BINARY);
          @fclose($fp);
        }
        else
        {
          $ret = false;
        }
      }
    }

    @unlink($this->tempFilename);

    if ($ret === false)
    {
      $this->setError(AKText::sprintf('FTP_COULDNT_UPLOAD', $this->filename));

      return false;
    }

    $restorePerms = AKFactory::get('kickstart.setup.restoreperms', false);
    $perms = $restorePerms ? $this->perms : 0644;

    $ret = @chmod($root . '/' . $this->filename, $perms);

    if ($this->useFTP && ($ret === false))
    {
      @ftp_chmod($this->_handle, $perms, $remoteName);
    }

    return true;
  }

  /**
   * Create a temporary filename
   *
   * @param string $filename The original filename
   * @param int    $perms    The file permissions
   *
   * @return string
   */
  public function processFilename($filename, $perms = 0755)
  {
    // Catch some error conditions...
    if ($this->getError())
    {
      return false;
    }

    // If a null filename is passed, it means that we shouldn't do any post processing, i.e.
    // the entity was a directory or symlink
    if (is_null($filename))
    {
      $this->filename = null;
      $this->tempFilename = null;

      return null;
    }

    // Strip absolute filesystem path to website's root
    $removePath = AKFactory::get('kickstart.setup.destdir', '');

    if (!empty($removePath))
    {
      $left = substr($filename, 0, strlen($removePath));

      if ($left == $removePath)
      {
        $filename = substr($filename, strlen($removePath));
      }
    }

    // Trim slash on the left
    $filename = ltrim($filename, '/');

    $this->filename = $filename;
    $this->tempFilename = tempnam($this->tempDir, 'kickstart-');
    $this->perms = $perms;

    if (empty($this->tempFilename))
    {
      // Oops! Let's try something different
      $this->tempFilename = $this->tempDir . '/kickstart-' . time() . '.dat';
    }

    return $this->tempFilename;
  }

  /**
   * Is the directory writeable?
   *
   * @param string $dir The directory ti check
   *
   * @return bool
   */
  private function isDirWritable($dir)
  {
    $fp = @fopen($dir . '/kickstart.dat', 'wb');

    if ($fp === false)
    {
      return false;
    }

    @fclose($fp);
    unlink($dir . '/kickstart.dat');

    return true;
  }

  /**
   * Create a directory, recursively
   *
   * @param string $dirName The directory to create
   * @param int    $perms   The permissions to give to the directory
   *
   * @return bool
   */
  public function createDirRecursive($dirName, $perms)
  {
    // Strip absolute filesystem path to website's root
    $removePath = AKFactory::get('kickstart.setup.destdir', '');

    if (!empty($removePath))
    {
      // UNIXize the paths
      $removePath = str_replace('\\', '/', $removePath);
      $dirName = str_replace('\\', '/', $dirName);
      // Make sure they both end in a slash
      $removePath = rtrim($removePath, '/\\') . '/';
      $dirName = rtrim($dirName, '/\\') . '/';
      // Process the path removal
      $left = substr($dirName, 0, strlen($removePath));

      if ($left == $removePath)
      {
        $dirName = substr($dirName, strlen($removePath));
      }
    }

    // 'cause the substr() above may return FALSE.
    if (empty($dirName))
    {
      $dirName = '';
    }

    $check = '/' . trim($this->dir, '/') . '/' . trim($dirName, '/');
    $checkFS = $removePath . trim($dirName, '/');

    if ($this->is_dir($check))
    {
      return true;
    }

    $alldirs = explode('/', $dirName);
    $previousDir = '/' . trim($this->dir);
    $previousDirFS = rtrim($removePath, '/\\');

    foreach ($alldirs as $curdir)
    {
      $check = $previousDir . '/' . $curdir;
      $checkFS = $previousDirFS . '/' . $curdir;

      if (!is_dir($checkFS) && !$this->is_dir($check))
      {
        // Proactively try to delete a file by the same name
        if (!@unlink($checkFS) && $this->useFTP)
        {
          @ftp_delete($this->handle, $check);
        }

        $createdDir = @mkdir($checkFS, 0755);

        if (!$createdDir && $this->useFTP)
        {
          $createdDir = @ftp_mkdir($this->handle, $check);
        }

        if ($createdDir === false)
        {
          // If we couldn't create the directory, attempt to fix the permissions in the PHP level and retry!
          $this->fixPermissions($checkFS);

          $createdDir = @mkdir($checkFS, 0755);
          if (!$createdDir && $this->useFTP)
          {
            $createdDir = @ftp_mkdir($this->handle, $check);
          }

          if ($createdDir === false)
          {
            $this->setError(AKText::sprintf('FTP_CANT_CREATE_DIR', $check));

            return false;
          }
        }

        if (!@chmod($checkFS, $perms) && $this->useFTP)
        {
          @ftp_chmod($this->handle, $perms, $check);
        }
      }

      $previousDir = $check;
      $previousDirFS = $checkFS;
    }

    return true;
  }

  /**
   * Closes the FTP connection
   */
  public function close()
  {
    if (!$this->useFTP)
    {
      @ftp_close($this->handle);
    }
  }

  /**
   * Tries to fix directory/file permissions in the PHP level, so that
   * the FTP operation doesn't fail.
   *
   * @param $path string The full path to a directory or file
   */
  private function fixPermissions($path)
  {
    // Turn off error reporting
    if (!defined('KSDEBUG'))
    {
      $oldErrorReporting = @error_reporting(E_NONE);
    }

    // Get UNIX style paths
    $relPath = str_replace('\\', '/', $path);
    $basePath = rtrim(str_replace('\\', '/', KSROOTDIR), '/');
    $basePath = rtrim($basePath, '/');

    if (!empty($basePath))
    {
      $basePath .= '/';
    }

    // Remove the leading relative root
    if (substr($relPath, 0, strlen($basePath)) == $basePath)
    {
      $relPath = substr($relPath, strlen($basePath));
    }

    $dirArray = explode('/', $relPath);
    $pathBuilt = rtrim($basePath, '/');

    foreach ($dirArray as $dir)
    {
      if (empty($dir))
      {
        continue;
      }

      $oldPath = $pathBuilt;
      $pathBuilt .= '/' . $dir;

      if (is_dir($oldPath . $dir))
      {
        @chmod($oldPath . $dir, 0777);
      }
      else
      {
        if (@chmod($oldPath . $dir, 0777) === false)
        {
          @unlink($oldPath . $dir);
        }
      }
    }

    // Restore error reporting
    if (!defined('KSDEBUG'))
    {
      @error_reporting($oldErrorReporting);
    }
  }

  public function chmod($file, $perms)
  {
    if (AKFactory::get('kickstart.setup.dryrun', '0'))
    {
      return true;
    }

    $ret = @chmod($file, $perms);

    if (!$ret && $this->useFTP)
    {
      // Strip absolute filesystem path to website's root
      $removePath = AKFactory::get('kickstart.setup.destdir', '');

      if (!empty($removePath))
      {
        $left = substr($file, 0, strlen($removePath));

        if ($left == $removePath)
        {
          $file = substr($file, strlen($removePath));
        }
      }

      // Trim slash on the left
      $file = ltrim($file, '/');

      $ret = @ftp_chmod($this->handle, $perms, $file);
    }

    return $ret;
  }

  private function is_dir($dir)
  {
    if ($this->useFTP)
    {
      return @ftp_chdir($this->handle, $dir);
    }

    return false;
  }

  public function unlink($file)
  {
    $ret = @unlink($file);

    if (!$ret && $this->useFTP)
    {
      $removePath = AKFactory::get('kickstart.setup.destdir', '');
      if (!empty($removePath))
      {
        $left = substr($file, 0, strlen($removePath));
        if ($left == $removePath)
        {
          $file = substr($file, strlen($removePath));
        }
      }

      $check = '/' . trim($this->dir, '/') . '/' . trim($file, '/');

      $ret = @ftp_delete($this->handle, $check);
    }

    return $ret;
  }

  public function rmdir($directory)
  {
    $ret = @rmdir($directory);

    if (!$ret && $this->useFTP)
    {
      $removePath = AKFactory::get('kickstart.setup.destdir', '');
      if (!empty($removePath))
      {
        $left = substr($directory, 0, strlen($removePath));
        if ($left == $removePath)
        {
          $directory = substr($directory, strlen($removePath));
        }
      }

      $check = '/' . trim($this->dir, '/') . '/' . trim($directory, '/');

      $ret = @ftp_rmdir($this->handle, $check);
    }

    return $ret;
  }

  public function rename($from, $to)
  {
    $ret = @rename($from, $to);

    if (!$ret && $this->useFTP)
    {
      $originalFrom = $from;
      $originalTo = $to;

      $removePath = AKFactory::get('kickstart.setup.destdir', '');
      if (!empty($removePath))
      {
        $left = substr($from, 0, strlen($removePath));
        if ($left == $removePath)
        {
          $from = substr($from, strlen($removePath));
        }
      }
      $from = '/' . trim($this->dir, '/') . '/' . trim($from, '/');

      if (!empty($removePath))
      {
        $left = substr($to, 0, strlen($removePath));
        if ($left == $removePath)
        {
          $to = substr($to, strlen($removePath));
        }
      }
      $to = '/' . trim($this->dir, '/') . '/' . trim($to, '/');

      $ret = @ftp_rename($this->handle, $from, $to);
    }

    return $ret;
  }
}

