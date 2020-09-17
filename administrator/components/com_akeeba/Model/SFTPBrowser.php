<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Model;

// Protect from unauthorized access
defined('_JEXEC') || die();

use FOF30\Model\Model;
use Joomla\CMS\Language\Text;
use RuntimeException;

class SFTPBrowser extends Model
{
	/**
	 * The SFTP server hostname
	 *
	 * @var  string
	 */
	public $host = '';

	/**
	 * The SFTP server port number (default: 22)
	 *
	 * @var  int
	 */
	public $port = 22;

	/**
	 * Username for logging in
	 *
	 * @var  string
	 */
	public $username = '';

	/**
	 * Password for logging in
	 *
	 * @var  string
	 */
	public $password = '';

	/**
	 * Private key file for connection
	 *
	 * @var  string
	 */
	public $privkey = '';

	/**
	 * Public key file for connection
	 *
	 * @var string
	 */
	public $pubkey = '';

	/**
	 * The directory to browse
	 *
	 * @var  string
	 */
	public $directory = '';

	/**
	 * Breadcrumbs to the current directory
	 *
	 * @var  array
	 */
	public $parts = [];

	/**
	 * Path to the parent directory
	 *
	 * @var  string
	 */
	public $parent_directory = null;

	/**
	 * Gets the folders contained in the remote FTP root directory defined in $this->directory
	 *
	 * @return  array
	 */
	public function getListing()
	{
		$dir = $this->directory;

		// Parse directory to parts
		$parsed_dir  = trim($dir, '/');
		$this->parts = empty($parsed_dir) ? [] : explode('/', $parsed_dir);

		// Find the path to the parent directory
		$this->parent_directory = '';

		if (!empty($this->parts))
		{
			$copy_of_parts = $this->parts;
			array_pop($copy_of_parts);

			$this->parent_directory = '/';

			if (!empty($copy_of_parts))
			{
				$this->parent_directory = '/' . implode('/', $copy_of_parts);
			}
		}

		// Initialise
		$connection = null;
		$sftphandle = null;

		// Open a connection
		if (!function_exists('ssh2_connect'))
		{
			throw new RuntimeException("Your web server does not have the SSH2 PHP module, therefore can not connect and upload archives to SFTP servers.");
		}

		$connection = ssh2_connect($this->host, $this->port);

		if ($connection === false)
		{
			throw new RuntimeException("Invalid SFTP hostname or port ({$this->host}:{$this->port}) or the connection is blocked by your web server's firewall.");
		}

		// Connect to the server

		if (!empty($this->pubkey) && !empty($this->privkey))
		{
			if (!ssh2_auth_pubkey_file($connection, $this->username, $this->pubkey, $this->privkey, $this->password))
			{
				throw new RuntimeException('Certificate error');
			}
		}
		else
		{
			if (!ssh2_auth_password($connection, $this->username, $this->password))
			{
				throw new RuntimeException('Could not authenticate access to SFTP server; check your username and password.');
			}
		}

		$sftphandle = ssh2_sftp($connection);

		if ($sftphandle === false)
		{
			throw new RuntimeException("Your SSH server does not allow SFTP connections");
		}

		// Get a raw directory listing (hoping it's a UNIX server!)
		$list = [];
		$dir  = ltrim($dir, '/');

		if (empty($dir))
		{
			$dir = ssh2_sftp_realpath($sftphandle, ".");

			$this->directory = $dir;

			// Parse directory to parts
			$parsed_dir  = trim($dir, '/');
			$this->parts = empty($parsed_dir) ? [] : explode('/', $parsed_dir);

			// Find the path to the parent directory
			$this->parent_directory = '';

			if (!empty($this->parts))
			{
				$copy_of_parts = $this->parts;
				array_pop($copy_of_parts);

				$this->parent_directory = '/';

				if (!empty($copy_of_parts))
				{
					$this->parent_directory = '/' . implode('/', $copy_of_parts);
				}
			}
		}

		$handle = opendir("ssh2.sftp://$sftphandle/$dir");

		if (!is_resource($handle))
		{
			throw new RuntimeException(Text::_('COM_AKEEBA_SFTPBROWSER_ERROR_NOACCESS'));
		}

		while (($entry = readdir($handle)) !== false)
		{
			if (substr($entry, 0, 1) == '.')
			{
				continue;
			}

			if (!is_dir("ssh2.sftp://$sftphandle/$dir/$entry"))
			{
				continue;
			}

			$list[] = $entry;
		}

		closedir($handle);

		if (!empty($list))
		{
			asort($list);
		}

		return $list;
	}

	/**
	 * Perform the actual folder browsing. Returns an array that's usable by the UI.
	 *
	 * @return  array
	 */
	public function doBrowse()
	{
		$error = '';
		$list  = [];

		try
		{
			$list = $this->getListing();
		}
		catch (RuntimeException $e)
		{
			$error = $e->getMessage();
		}

		$response_array = [
			'error'       => $error,
			'list'        => $list,
			'breadcrumbs' => $this->parts,
			'directory'   => $this->directory,
			'parent'      => $this->parent_directory,
		];

		return $response_array;
	}
}
