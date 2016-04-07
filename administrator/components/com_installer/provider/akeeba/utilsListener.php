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
 * A filesystem scanner which uses opendir()
 */
class AKUtilsLister extends AKAbstractObject
{
  public function &getFiles($folder, $pattern = '*')
  {
    // Initialize variables
    $arr = array();
    $false = false;

    if(!is_dir($folder)) return $false;

    $handle = @opendir($folder);
    // If directory is not accessible, just return FALSE
    if ($handle === FALSE) {
      $this->setWarning( 'Unreadable directory '.$folder);
      return $false;
    }

    while (($file = @readdir($handle)) !== false)
    {
      if( !fnmatch($pattern, $file) ) continue;

      if (($file != '.') && ($file != '..'))
      {
        $ds = ($folder == '') || ($folder == '/') || (@substr($folder, -1) == '/') || (@substr($folder, -1) == DIRECTORY_SEPARATOR) ? '' : DIRECTORY_SEPARATOR;
        $dir = $folder . $ds . $file;
        $isDir = is_dir($dir);
        if (!$isDir) {
          $arr[] = $dir;
        }
      }
    }
    @closedir($handle);

    return $arr;
  }

  public function &getFolders($folder, $pattern = '*')
  {
    // Initialize variables
    $arr = array();
    $false = false;

    if(!is_dir($folder)) return $false;

    $handle = @opendir($folder);
    // If directory is not accessible, just return FALSE
    if ($handle === FALSE) {
      $this->setWarning( 'Unreadable directory '.$folder);
      return $false;
    }

    while (($file = @readdir($handle)) !== false)
    {
      if( !fnmatch($pattern, $file) ) continue;

      if (($file != '.') && ($file != '..'))
      {
        $ds = ($folder == '') || ($folder == '/') || (@substr($folder, -1) == '/') || (@substr($folder, -1) == DIRECTORY_SEPARATOR) ? '' : DIRECTORY_SEPARATOR;
        $dir = $folder . $ds . $file;
        $isDir = is_dir($dir);
        if ($isDir) {
          $arr[] = $dir;
        }
      }
    }
    @closedir($handle);

    return $arr;
  }
}

