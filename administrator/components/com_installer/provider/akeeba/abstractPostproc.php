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
 * File post processor engines base class
 */
abstract class AKAbstractPostproc extends AKAbstractObject
{
  /** @var string The current (real) file path we'll have to process */
  protected $filename = null;

  /** @var int The requested permissions */
  protected $perms = 0755;

  /** @var string The temporary file path we gave to the unarchiver engine */
  protected $tempFilename = null;

  /** @var int The UNIX timestamp of the file's desired modification date */
  public $timestamp = 0;

  /**
   * Processes the current file, e.g. moves it from temp to final location by FTP
   */
  abstract public function process();

  /**
   * The unarchiver tells us the path to the filename it wants to extract and we give it
   * a different path instead.
   * @param string $filename The path to the real file
   * @param int $perms The permissions we need the file to have
   * @return string The path to the temporary file
   */
  abstract public function processFilename($filename, $perms = 0755);

  /**
   * Recursively creates a directory if it doesn't exist
   * @param string $dirName The directory to create
   * @param int $perms The permissions to give to that directory
   */
  abstract public function createDirRecursive( $dirName, $perms );

  abstract public function chmod( $file, $perms );

  abstract public function unlink( $file );

  abstract public function rmdir( $directory );

  abstract public function rename( $from, $to );
}


