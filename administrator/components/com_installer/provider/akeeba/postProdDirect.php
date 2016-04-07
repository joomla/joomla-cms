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
 * Direct file writer
 */
class AKPostprocDirect extends AKAbstractPostproc
{
  public function process()
  {
    $restorePerms = AKFactory::get('kickstart.setup.restoreperms', false);
    if($restorePerms)
    {
      @chmod($this->filename, $this->perms);
    }
    else
    {
      if(@is_file($this->filename))
      {
        @chmod($this->filename, 0644);
      }
      else
      {
        @chmod($this->filename, 0755);
      }
    }
    if($this->timestamp > 0)
    {
      @touch($this->filename, $this->timestamp);
    }
    return true;
  }

  public function processFilename($filename, $perms = 0755)
  {
    $this->perms = $perms;
    $this->filename = $filename;
    return $filename;
  }

  public function createDirRecursive( $dirName, $perms )
  {
    if( AKFactory::get('kickstart.setup.dryrun','0') ) return true;
    if (@mkdir($dirName, 0755, true)) {
      @chmod($dirName, 0755);
      return true;
    }

    $root = AKFactory::get('kickstart.setup.destdir');
    $root = rtrim(str_replace('\\','/',$root),'/');
    $dir = rtrim(str_replace('\\','/',$dirName),'/');
    if(strpos($dir, $root) === 0) {
      $dir = ltrim(substr($dir, strlen($root)), '/');
      $root .= '/';
    } else {
      $root = '';
    }

    if(empty($dir)) return true;

    $dirArray = explode('/', $dir);
    $path = '';
    foreach( $dirArray as $dir )
    {
      $path .= $dir . '/';
      $ret = is_dir($root.$path) ? true : @mkdir($root.$path);
      if( !$ret ) {
        // Is this a file instead of a directory?
        if(is_file($root.$path) )
        {
          @unlink($root.$path);
          $ret = @mkdir($root.$path);
        }
        if( !$ret ) {
          $this->setError( AKText::sprintf('COULDNT_CREATE_DIR',$path) );
          return false;
        }
      }
      // Try to set new directory permissions to 0755
      @chmod($root.$path, $perms);
    }
    return true;
  }

  public function chmod( $file, $perms )
  {
    if( AKFactory::get('kickstart.setup.dryrun','0') ) return true;

    return @chmod( $file, $perms );
  }

  public function unlink( $file )
  {
    return @unlink( $file );
  }

  public function rmdir( $directory )
  {
    return @rmdir( $directory );
  }

  public function rename( $from, $to )
  {
    return @rename($from, $to);
  }

}

