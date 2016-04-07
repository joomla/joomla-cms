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
 * FTP file writer
 */
class AKPostprocSFTP extends AKAbstractPostproc
{
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

    /** @var resource SFTP resource handle */
  private $handle = null;

    /** @var resource SSH2 connection resource handle */
    private $_connection = null;

    /** @var string Current remote directory, including the remote directory string */
    private $_currentdir;

  /** @var string The temporary directory where the data will be stored */
  private $tempDir = '';

  public function __construct()
  {
    parent::__construct();

    $this->host     = AKFactory::get('kickstart.ftp.host', '');
    $this->port     = AKFactory::get('kickstart.ftp.port', 22);

    if(trim($this->port) == '') $this->port = 22;

    $this->user     = AKFactory::get('kickstart.ftp.user', '');
    $this->pass     = AKFactory::get('kickstart.ftp.pass', '');
    $this->dir      = AKFactory::get('kickstart.ftp.dir', '');
    $this->tempDir  = AKFactory::get('kickstart.ftp.tempdir', '');

    $connected = $this->connect();

    if($connected)
    {
      if(!empty($this->tempDir))
      {
        $tempDir = rtrim($this->tempDir, '/\\').'/';
        $writable = $this->isDirWritable($tempDir);
      }
      else
      {
        $tempDir = '';
        $writable = false;
      }

      if(!$writable) {
        // Default temporary directory is the current root
        $tempDir = KSROOTDIR;
        if(empty($tempDir))
        {
          // Oh, we have no directory reported!
          $tempDir = '.';
        }
        $absoluteDirToHere = $tempDir;
        $tempDir = rtrim(str_replace('\\','/',$tempDir),'/');
        if(!empty($tempDir)) $tempDir .= '/';
        $this->tempDir = $tempDir;
        // Is this directory writable?
        $writable = $this->isDirWritable($tempDir);
      }

      if(!$writable)
      {
        // Nope. Let's try creating a temporary directory in the site's root.
        $tempDir = $absoluteDirToHere.'/kicktemp';
        $this->createDirRecursive($tempDir, 0777);
        // Try making it writable...
        $this->fixPermissions($tempDir);
        $writable = $this->isDirWritable($tempDir);
      }

      // Was the new directory writable?
      if(!$writable)
      {
        // Let's see if the user has specified one
        $userdir = AKFactory::get('kickstart.ftp.tempdir', '');
        if(!empty($userdir))
        {
          // Is it an absolute or a relative directory?
          $absolute = false;
          $absolute = $absolute || ( substr($userdir,0,1) == '/' );
          $absolute = $absolute || ( substr($userdir,1,1) == ':' );
          $absolute = $absolute || ( substr($userdir,2,1) == ':' );
          if(!$absolute)
          {
            // Make absolute
            $tempDir = $absoluteDirToHere.$userdir;
          }
          else
          {
            // it's already absolute
            $tempDir = $userdir;
          }
          // Does the directory exist?
          if( is_dir($tempDir) )
          {
            // Yeah. Is it writable?
            $writable = $this->isDirWritable($tempDir);
          }
        }
      }
      $this->tempDir = $tempDir;

      if(!$writable)
      {
        // No writable directory found!!!
        $this->setError(AKText::_('SFTP_TEMPDIR_NOT_WRITABLE'));
      }
      else
      {
        AKFactory::set('kickstart.ftp.tempdir', $tempDir);
        $this->tempDir = $tempDir;
      }
    }
  }

  function __wakeup()
  {
    $this->connect();
  }

  public function connect()
  {
        $this->_connection = false;

        if(!function_exists('ssh2_connect'))
        {
            $this->setError(AKText::_('SFTP_NO_SSH2'));
            return false;
        }

        $this->_connection = @ssh2_connect($this->host, $this->port);

        if (!@ssh2_auth_password($this->_connection, $this->user, $this->pass))
        {
            $this->setError(AKText::_('SFTP_WRONG_USER'));

            $this->_connection = false;

            return false;
        }

        $this->handle = @ssh2_sftp($this->_connection);

        // I must have an absolute directory
        if(!$this->dir)
        {
            $this->setError(AKText::_('SFTP_WRONG_STARTING_DIR'));
            return false;
        }

        // Change to initial directory
        if(!$this->sftp_chdir('/'))
        {
            $this->setError(AKText::_('SFTP_WRONG_STARTING_DIR'));

            unset($this->_connection);
            unset($this->handle);

            return false;
        }

        // Try to download ourselves
        $testFilename = defined('KSSELFNAME') ? KSSELFNAME : basename(__FILE__);
        $basePath     = '/'.trim($this->dir, '/');

        if(@fopen("ssh2.sftp://{$this->handle}$basePath/$testFilename",'r+') === false)
        {
            $this->setError(AKText::_('SFTP_WRONG_STARTING_DIR'));

            unset($this->_connection);
            unset($this->handle);

            return false;
        }

        return true;
  }

  public function process()
  {
    if( is_null($this->tempFilename) )
    {
      // If an empty filename is passed, it means that we shouldn't do any post processing, i.e.
      // the entity was a directory or symlink
      return true;
    }

    $remotePath      = dirname($this->filename);
    $absoluteFSPath  = dirname($this->filename);
    $absoluteFTPPath = '/'.trim( $this->dir, '/' ).'/'.trim($remotePath, '/');
    $onlyFilename    = basename($this->filename);

    $remoteName = $absoluteFTPPath.'/'.$onlyFilename;

        $ret = $this->sftp_chdir($absoluteFTPPath);

    if($ret === false)
    {
      $ret = $this->createDirRecursive( $absoluteFSPath, 0755);

      if($ret === false)
            {
        $this->setError(AKText::sprintf('SFTP_COULDNT_UPLOAD', $this->filename));
        return false;
      }

      $ret = $this->sftp_chdir($absoluteFTPPath);

      if($ret === false)
            {
        $this->setError(AKText::sprintf('SFTP_COULDNT_UPLOAD', $this->filename));
        return false;
      }
    }

        // Create the file
        $ret = $this->write($this->tempFilename, $remoteName);

        // If I got a -1 it means that I wasn't able to open the file, so I have to stop here
        if($ret === -1)
        {
            $this->setError(AKText::sprintf('SFTP_COULDNT_UPLOAD', $this->filename));
            return false;
        }

    if($ret === false)
    {
      // If we couldn't create the file, attempt to fix the permissions in the PHP level and retry!
      $this->fixPermissions($this->filename);
      $this->unlink($this->filename);

            $ret = $this->write($this->tempFilename, $remoteName);
    }

    @unlink($this->tempFilename);

    if($ret === false)
    {
      $this->setError(AKText::sprintf('SFTP_COULDNT_UPLOAD', $this->filename));
      return false;
    }
    $restorePerms = AKFactory::get('kickstart.setup.restoreperms', false);

    if($restorePerms)
    {
            $this->chmod($remoteName, $this->perms);
    }
    else
    {
            $this->chmod($remoteName, 0644);
    }
    return true;
  }

  public function processFilename($filename, $perms = 0755)
  {
    // Catch some error conditions...
    if($this->getError())
    {
      return false;
    }

    // If a null filename is passed, it means that we shouldn't do any post processing, i.e.
    // the entity was a directory or symlink
    if(is_null($filename))
    {
      $this->filename = null;
      $this->tempFilename = null;
      return null;
    }

        // Strip absolute filesystem path to website's root
        $removePath = AKFactory::get('kickstart.setup.destdir','');
        if(!empty($removePath))
        {
            $left = substr($filename, 0, strlen($removePath));
            if($left == $removePath)
            {
                $filename = substr($filename, strlen($removePath));
            }
        }

        // Trim slash on the left
        $filename = ltrim($filename, '/');

    $this->filename = $filename;
    $this->tempFilename = tempnam($this->tempDir, 'kickstart-');
    $this->perms = $perms;

    if( empty($this->tempFilename) )
    {
      // Oops! Let's try something different
      $this->tempFilename = $this->tempDir.'/kickstart-'.time().'.dat';
    }

    return $this->tempFilename;
  }

  private function isDirWritable($dir)
  {
    if(@fopen("ssh2.sftp://{$this->handle}$dir/kickstart.dat",'wb') === false)
    {
      return false;
    }
    else
    {
            @ssh2_sftp_unlink($this->handle, $dir.'/kickstart.dat');
      return true;
    }
  }

  public function createDirRecursive( $dirName, $perms )
  {
        // Strip absolute filesystem path to website's root
        $removePath = AKFactory::get('kickstart.setup.destdir','');
        if(!empty($removePath))
        {
            // UNIXize the paths
            $removePath = str_replace('\\','/',$removePath);
            $dirName = str_replace('\\','/',$dirName);
            // Make sure they both end in a slash
            $removePath = rtrim($removePath,'/\\').'/';
            $dirName = rtrim($dirName,'/\\').'/';
            // Process the path removal
            $left = substr($dirName, 0, strlen($removePath));
            if($left == $removePath)
            {
                $dirName = substr($dirName, strlen($removePath));
            }
        }
        if(empty($dirName)) $dirName = ''; // 'cause the substr() above may return FALSE.

    $check = '/'.trim($this->dir,'/ ').'/'.trim($dirName, '/');

    if($this->is_dir($check))
        {
            return true;
        }

    $alldirs = explode('/', $dirName);
    $previousDir = '/'.trim($this->dir, '/ ');

    foreach($alldirs as $curdir)
    {
            if(!$curdir)
            {
                continue;
            }

      $check = $previousDir.'/'.$curdir;

      if(!$this->is_dir($check))
      {
        // Proactively try to delete a file by the same name
                @ssh2_sftp_unlink($this->handle, $check);

        if(@ssh2_sftp_mkdir($this->handle, $check) === false)
        {
          // If we couldn't create the directory, attempt to fix the permissions in the PHP level and retry!
          $this->fixPermissions($check);

          if(@ssh2_sftp_mkdir($this->handle, $check) === false)
          {
            // Can we fall back to pure PHP mode, sire?
            if(!@mkdir($check))
            {
              $this->setError(AKText::sprintf('FTP_CANT_CREATE_DIR', $check));
              return false;
            }
            else
            {
              // Since the directory was built by PHP, change its permissions
              @chmod($check, "0777");
              return true;
            }
          }
        }

        @ssh2_sftp_chmod($this->handle, $check, $perms);
      }

      $previousDir = $check;
    }

    return true;
  }

  public function close()
  {
    unset($this->_connection);
    unset($this->handle);
  }

  /*
   * Tries to fix directory/file permissions in the PHP level, so that
   * the FTP operation doesn't fail.
   * @param $path string The full path to a directory or file
   */
  private function fixPermissions( $path )
  {
    // Turn off error reporting
    if(!defined('KSDEBUG')) {
      $oldErrorReporting = @error_reporting(E_NONE);
    }

    // Get UNIX style paths
    $relPath  = str_replace('\\','/',$path);
    $basePath = rtrim(str_replace('\\','/',KSROOTDIR),'/');
    $basePath = rtrim($basePath,'/');

    if(!empty($basePath))
        {
            $basePath .= '/';
        }

    // Remove the leading relative root
    if( substr($relPath,0,strlen($basePath)) == $basePath )
        {
            $relPath = substr($relPath,strlen($basePath));
        }

    $dirArray  = explode('/', $relPath);
    $pathBuilt = rtrim($basePath,'/');

    foreach( $dirArray as $dir )
    {
      if(empty($dir))
            {
                continue;
            }

      $oldPath = $pathBuilt;
      $pathBuilt .= '/'.$dir;

      if(is_dir($oldPath.'/'.$dir))
      {
        @chmod($oldPath.'/'.$dir, 0777);
      }
      else
      {
        if(@chmod($oldPath.'/'.$dir, 0777) === false)
        {
          @unlink($oldPath.$dir);
        }
      }
    }

    // Restore error reporting
    if(!defined('KSDEBUG')) {
      @error_reporting($oldErrorReporting);
    }
  }

  public function chmod( $file, $perms )
  {
        return @ssh2_sftp_chmod($this->handle, $file, $perms);
  }

  private function is_dir( $dir )
  {
        return $this->sftp_chdir($dir);
  }

    private function write($local, $remote)
    {
        $fp      = @fopen("ssh2.sftp://{$this->handle}$remote",'w');
        $localfp = @fopen($local,'rb');

        if($fp === false)
        {
            return -1;
        }

        if($localfp === false)
        {
            @fclose($fp);
            return -1;
        }

        $res = true;

        while(!feof($localfp) && ($res !== false))
        {
            $buffer = @fread($localfp, 65567);
            $res    = @fwrite($fp, $buffer);
        }

        @fclose($fp);
        @fclose($localfp);

        return $res;
    }

  public function unlink( $file )
  {
    $check    = '/'.trim($this->dir,'/').'/'.trim($file, '/');

        return @ssh2_sftp_unlink($this->handle, $check);
  }

  public function rmdir( $directory )
  {
    $check    = '/'.trim($this->dir,'/').'/'.trim($directory, '/');

    return @ssh2_sftp_rmdir( $this->handle, $check);
  }

  public function rename( $from, $to )
  {
        $from     = '/'.trim($this->dir,'/').'/'.trim($from, '/');
        $to       = '/'.trim($this->dir,'/').'/'.trim($to, '/');

        $result =  @ssh2_sftp_rename($this->handle, $from, $to);

    if($result !== true)
    {
      return @rename($from, $to);
    }
    else
    {
      return true;
    }
  }

    /**
     * Changes to the requested directory in the remote server. You give only the
     * path relative to the initial directory and it does all the rest by itself,
     * including doing nothing if the remote directory is the one we want.
     *
     * @param   string  $dir    The (realtive) remote directory
     *
     * @return  bool True if successful, false otherwise.
     */
    private function sftp_chdir($dir)
    {
        // Strip absolute filesystem path to website's root
        $removePath = AKFactory::get('kickstart.setup.destdir','');
        if(!empty($removePath))
        {
            // UNIXize the paths
            $removePath = str_replace('\\','/',$removePath);
            $dir        = str_replace('\\','/',$dir);

            // Make sure they both end in a slash
            $removePath = rtrim($removePath,'/\\').'/';
            $dir        = rtrim($dir,'/\\').'/';

            // Process the path removal
            $left = substr($dir, 0, strlen($removePath));

            if($left == $removePath)
            {
                $dir = substr($dir, strlen($removePath));
            }
        }

        if(empty($dir))
        {
            // Because the substr() above may return FALSE.
            $dir = '';
        }

        // Calculate "real" (absolute) SFTP path
        $realdir = substr($this->dir, -1) == '/' ? substr($this->dir, 0, strlen($this->dir) - 1) : $this->dir;
        $realdir .= '/'.$dir;
        $realdir = substr($realdir, 0, 1) == '/' ? $realdir : '/'.$realdir;

        if($this->_currentdir == $realdir)
        {
            // Already there, do nothing
            return true;
        }

        $result = @ssh2_sftp_stat($this->handle, $realdir);

        if($result === false)
        {
            return false;
        }
        else
        {
            // Update the private "current remote directory" variable
            $this->_currentdir = $realdir;

            return true;
        }
    }

}


