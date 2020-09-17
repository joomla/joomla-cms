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

class FTPBrowser extends Model
{
	/**
	 * The FTP server hostname
	 *
	 * @var  string
	 */
	public $host = '';

	/**
	 * The FTP server port number (default: 21)
	 *
	 * @var  int
	 */
	public $port = 21;

	/**
	 * Should I use passive mode (default: yes)
	 *
	 * @var  bool
	 */
	public $passive = true;

	/**
	 * Should I use FTP over SSL (default: no)
	 *
	 * @var  bool
	 */
	public $ssl = false;

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

		// Connect to the server
		if ($this->ssl)
		{
			$con = @ftp_ssl_connect($this->host, $this->port);
		}
		else
		{
			$con = @ftp_connect($this->host, $this->port);
		}

		if ($con === false)
		{
			throw new RuntimeException(Text::_('COM_AKEEBA_FTPBROWSER_ERROR_HOSTNAME'));
		}

		// Login
		$result = @ftp_login($con, $this->username, $this->password);

		if ($result === false)
		{
			throw new RuntimeException(Text::_('COM_AKEEBA_FTPBROWSER_ERROR_USERPASS'));
		}

		// Set the passive mode -- don't care if it fails, though!
		@ftp_pasv($con, $this->passive);

		// Try to chdir to the specified directory
		if (!empty($dir))
		{
			$result = @ftp_chdir($con, $dir);

			if ($result === false)
			{
				throw new RuntimeException(Text::_('COM_AKEEBA_FTPBROWSER_ERROR_NOACCESS'));
			}
		}
		else
		{
			$this->directory = @ftp_pwd($con);

			$parsed_dir             = trim($this->directory, '/');
			$this->parts            = empty($parsed_dir) ? [] : explode('/', $parsed_dir);
			$this->parent_directory = $this->directory;
		}

		// Get a raw directory listing (hoping it's a UNIX server!)
		$list = @ftp_rawlist($con, '.');

		ftp_close($con);

		if ($list === false)
		{
			throw new RuntimeException(Text::_('COM_AKEEBA_FTPBROWSER_ERROR_UNSUPPORTED'));
		}

		// Parse the raw listing into an array
		$folders = $this->parse_rawlist($list);

		return $folders;
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

	/**
	 * Parse the raw list of folders returned by the server into a usable simple array of folders
	 *
	 * @param   array  $list  The raw folder list returned by ftp_rawlist
	 *
	 * @return  array  The parsed list of folders
	 */
	private function parse_rawlist(array $list)
	{
		$folders = [];

		foreach ($list as $v)
		{

			$vinfo = preg_split("/[\s]+/", $v, 9);

			if ($vinfo[0] !== "total")
			{
				$perms = $vinfo[0];

				if (substr($perms, 0, 1) == 'd')
				{
					$folders[] = $vinfo[8];
				}
			}
		}

		asort($folders);

		return $folders;
	}
}
