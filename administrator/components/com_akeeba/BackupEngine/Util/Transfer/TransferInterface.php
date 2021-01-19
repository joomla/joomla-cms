<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Util\Transfer;

defined('AKEEBAENGINE') || die();

use RuntimeException;

/**
 * An interface for Transfer adapters, used to transfer files to remote servers over FTP, FTPS, SFTP and possibly other
 * file transfer methods we might implement.
 *
 * @package   Akeeba\Engine\Util\Transfer
 */
interface TransferInterface
{
	/**
	 * Creates the uploader
	 *
	 * @param   array  $config
	 */
	public function __construct(array $config);

	/**
	 * Is this transfer method blocked by a server firewall?
	 *
	 * @param   array  $params  Any additional parameters you might need to pass
	 *
	 * @return  boolean  True if the firewall blocks connections to a known host
	 */
	public static function isFirewalled(array $params = []);

	/**
	 * Write the contents into the file
	 *
	 * @param   string  $fileName  The full path to the remote file
	 * @param   string  $contents  The contents to write to the file
	 *
	 * @return  boolean  True on success
	 */
	public function write($fileName, $contents);

	/**
	 * Uploads a local file to the remote storage
	 *
	 * @param   string  $localFilename   The full path to the local file
	 * @param   string  $remoteFilename  The full path to the remote file
	 * @param   bool    $useExceptions   Throw an exception instead of returning "false" on connection error.
	 *
	 * @return  boolean  True on success
	 */
	public function upload($localFilename, $remoteFilename, $useExceptions = true);

	/**
	 * Read the contents of a remote file into a string
	 *
	 * @param   string  $fileName  The full path to the remote file
	 *
	 * @return  string  The contents of the remote file
	 */
	public function read($fileName);

	/**
	 * Download a remote file into a local file
	 *
	 * @param   string  $remoteFilename  The remote file path to download from
	 * @param   string  $localFilename   The local file path to download to
	 * @param   bool    $useExceptions   Throw an exception instead of returning "false" on connection error.
	 *
	 * @return  boolean  True on success
	 */
	public function download($remoteFilename, $localFilename, $useExceptions = true);

	/**
	 * Delete a remote file
	 *
	 * @param   string  $fileName  The full path to the remote file
	 *
	 * @return  boolean  True on success
	 */
	public function delete($fileName);

	/**
	 * Create a copy of the remote file
	 *
	 * @param   string  $from  The full path of the remote file to copy from
	 * @param   string  $to    The full path of the remote file that will hold the copy
	 *
	 * @return  boolean  True on success
	 */
	public function copy($from, $to);

	/**
	 * Move or rename a file
	 *
	 * @param   string  $from  The full remote path of the file to move
	 * @param   string  $to    The full remote path of the target file
	 *
	 * @return  boolean  True on success
	 */
	public function move($from, $to);

	/**
	 * Change the permissions of a file
	 *
	 * @param   string   $fileName     The full path of the remote file whose permissions will change
	 * @param   integer  $permissions  The new permissions, e.g. 0644 (remember the leading zero in octal numbers!)
	 *
	 * @return  boolean  True on success
	 */
	public function chmod($fileName, $permissions);

	/**
	 * Create a directory if it doesn't exist. The operation is implicitly recursive, i.e. it will create all
	 * intermediate directories if they do not already exist.
	 *
	 * @param   string   $dirName      The full path of the remote directory to create
	 * @param   integer  $permissions  The permissions of the created directory
	 *
	 * @return  boolean  True on success
	 */
	public function mkdir($dirName, $permissions = 0755);

	/**
	 * Checks if the given directory exists
	 *
	 * @param   string  $path  The full path of the remote directory to check
	 *
	 * @return  boolean  True if the directory exists
	 */
	public function isDir($path);

	/**
	 * Get the current working directory
	 *
	 * @return  string
	 */
	public function cwd();

	/**
	 * Returns the absolute remote path from a path relative to the initial directory configured when creating the
	 * transfer object.
	 *
	 * @param   string  $fileName  The relative path of a file or directory
	 *
	 * @return  string  The absolute path for use by the transfer object
	 */
	public function getPath($fileName);

	/**
	 * Lists the subdirectories inside a directory
	 *
	 * @param   null|string  $dir  The directory to scan. Skip to use the current directory.
	 *
	 * @return  array|bool  A list of folders, or false if we could not get a listing
	 *
	 * @throws  RuntimeException  When the server is incompatible with our folder scanner
	 */
	public function listFolders($dir = null);
}
