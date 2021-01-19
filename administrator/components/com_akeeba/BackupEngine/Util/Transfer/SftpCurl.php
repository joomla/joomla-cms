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
 * SFTP transfer object, using cURL as the transport backend
 */
class SftpCurl extends Sftp implements TransferInterface
{
	/**
	 * SFTP server's hostname or IP address
	 *
	 * @var  string
	 */
	private $host = 'localhost';

	/**
	 * SFTP server's port, default: 21
	 *
	 * @var  integer
	 */
	private $port = 22;

	/**
	 * Username used to authenticate to the SFTP server
	 *
	 * @var  string
	 */
	private $username = '';

	/**
	 * Password used to authenticate to the SFTP server
	 *
	 * @var  string
	 */
	private $password = '';

	/**
	 * SFTP initial directory
	 *
	 * @var  string
	 */
	private $directory = '/';

	/**
	 * The absolute filesystem path to a private key file used for authentication instead of a password.
	 *
	 * @var  string
	 */
	private $privateKey = '';

	/**
	 * The absolute filesystem path to a public key file used for authentication instead of a password.
	 *
	 * @var  string
	 */
	private $publicKey = '';

	/**
	 * Timeout for connecting to the SFTP server, default: 10 minutes
	 *
	 * @var  integer
	 */
	private $timeout = 600;

	/**
	 * Should we enable verbose output to STDOUT? Useful for debugging.
	 *
	 * @var   bool
	 */
	private $verbose = false;

	/**
	 * Should I enabled the passive IP workaround for cURL?
	 *
	 * @var   bool
	 */
	private $skipPassiveIP = false;

	/**
	 * Public constructor
	 *
	 * @param   array  $options  Configuration options
	 *
	 * @return  self
	 *
	 * @throws  RuntimeException
	 */
	public function __construct(array $options)
	{
		if (isset($options['host']))
		{
			$this->host = $options['host'];
		}

		if (isset($options['port']))
		{
			$this->port = (int) $options['port'];
		}

		if (isset($options['username']))
		{
			$this->username = $options['username'];
		}

		if (isset($options['password']))
		{
			$this->password = $options['password'];
		}

		if (isset($options['directory']))
		{
			$this->directory = '/' . ltrim(trim($options['directory']), '/');
		}

		if (isset($options['privateKey']))
		{
			$this->privateKey = $options['privateKey'];
		}

		if (isset($options['publicKey']))
		{
			$this->publicKey = $options['publicKey'];
		}

		if (isset($options['timeout']))
		{
			$this->timeout = max(1, (int) $options['timeout']);
		}

		if (isset($options['passive_fix']))
		{
			$this->skipPassiveIP = $options['passive_fix'] ? true : false;
		}

		if (isset($options['verbose']))
		{
			$this->verbose = $options['verbose'] ? true : false;
		}
	}

	/**
	 * Save all parameters on serialization except the connection resource
	 *
	 * @return  array
	 */
	public function __sleep()
	{
		return [
			'host',
			'port',
			'username',
			'password',
			'directory',
			'privateKey',
			'publicKey',
			'timeout',
			'skipPassiveIP',
			'verbose',
		];
	}

	/**
	 * Test the connection to the SFTP server and whether the initial directory is correct. This is done by attempting to
	 * list the contents of the initial directory. The listing is not parsed (we don't really care!) and we do NOT check
	 * if we can upload files to that remote folder.
	 *
	 * @throws  RuntimeException
	 */
	public function connect()
	{
		$ch = $this->getCurlHandle($this->directory . '/');
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$listing = curl_exec($ch);
		$errNo   = curl_errno($ch);
		$error   = curl_error($ch);
		curl_close($ch);

		if ($errNo)
		{
			throw new RuntimeException("cURL Error $errNo connecting to remote SFTP server: $error", 500);
		}
	}

	/**
	 * Write the contents into the file
	 *
	 * @param   string  $fileName  The full path to the file
	 * @param   string  $contents  The contents to write to the file
	 *
	 * @return  boolean  True on success
	 */
	public function write($fileName, $contents)
	{
		// Make sure the buffer:// wrapper is loaded
		class_exists('\\Akeeba\\Engine\\Util\\Buffer', true);

		$handle = fopen('buffer://akeeba_engine_transfer_ftp_curl', 'r+');
		fwrite($handle, $contents);

		// Note: don't manually close the file pointer, it's closed automatically by uploadFromHandle
		try
		{
			$this->uploadFromHandle($fileName, $handle);
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Uploads a local file to the remote storage
	 *
	 * @param   string  $localFilename   The full path to the local file
	 * @param   string  $remoteFilename  The full path to the remote file
	 * @param   bool    $useExceptions   Throw an exception instead of returning "false" on connection error.
	 *
	 * @return  boolean  True on success
	 */
	public function upload($localFilename, $remoteFilename, $useExceptions = true)
	{
		$fp = @fopen($localFilename, 'rb');

		if ($fp === false)
		{
			throw new RuntimeException("Unreadable local file $localFilename");
		}

		// Note: don't manually close the file pointer, it's closed automatically by uploadFromHandle
		try
		{
			$this->uploadFromHandle($remoteFilename, $fp);
		}
		catch (RuntimeException $e)
		{
			if ($useExceptions)
			{
				throw $e;
			}

			return false;
		}

		return true;
	}

	/**
	 * Read the contents of a remote file into a string
	 *
	 * @param   string  $fileName  The full path to the remote file
	 *
	 * @return  string  The contents of the remote file
	 */
	public function read($fileName)
	{
		try
		{
			return $this->downloadToString($fileName);
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException("Can not download remote file $fileName", 500, $e);
		}
	}

	/**
	 * Download a remote file into a local file
	 *
	 * @param   string  $remoteFilename  The remote file path to download from
	 * @param   string  $localFilename   The local file path to download to
	 * @param   bool    $useExceptions   Throw an exception instead of returning "false" on connection error.
	 *
	 * @return  boolean  True on success
	 */
	public function download($remoteFilename, $localFilename, $useExceptions = true)
	{
		$fp = @fopen($localFilename, 'wb');

		if ($fp === false)
		{
			if ($useExceptions)
			{
				throw new RuntimeException(sprintf('Download from FTP failed. Can not open local file %s for writing.', $localFilename));
			}

			return false;
		}

		// Note: don't manually close the file pointer, it's closed automatically by downloadToHandle
		try
		{
			$this->downloadToHandle($remoteFilename, $fp);
		}
		catch (RuntimeException $e)
		{
			if ($useExceptions)
			{
				throw $e;
			}

			return false;
		}

		return true;
	}

	/**
	 * Delete a file (remove it from the disk)
	 *
	 * @param   string  $fileName  The full path to the file
	 *
	 * @return  boolean  True on success
	 */
	public function delete($fileName)
	{
		$commands = [
			'rm ' . $this->getPath($fileName),
		];

		try
		{
			$this->executeServerCommands($commands);
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Create a copy of the file. Actually, we have to read it in memory and upload it again.
	 *
	 * @param   string  $from  The full path of the file to copy from
	 * @param   string  $to    The full path of the file that will hold the copy
	 *
	 * @return  boolean  True on success
	 */
	public function copy($from, $to)
	{
		// Make sure the buffer:// wrapper is loaded
		class_exists('\\Akeeba\\Engine\\Util\\Buffer', true);

		$handle = fopen('buffer://akeeba_engine_transfer_ftp', 'r+');

		try
		{
			$this->downloadToHandle($from, $handle, false);
			$this->uploadFromHandle($to, $handle);
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Move or rename a file
	 *
	 * @param   string  $from  The full path of the file to move
	 * @param   string  $to    The full path of the target file
	 *
	 * @return  boolean  True on success
	 */
	public function move($from, $to)
	{
		$from = $this->getPath($from);
		$to   = $this->getPath($to);

		$commands = [
			'rename ' . $from . ' ' . $to,
		];

		try
		{
			$this->executeServerCommands($commands);
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Change the permissions of a file
	 *
	 * @param   string   $fileName     The full path of the file whose permissions will change
	 * @param   integer  $permissions  The new permissions, e.g. 0644 (remember the leading zero in octal numbers!)
	 *
	 * @return  boolean  True on success
	 */
	public function chmod($fileName, $permissions)
	{
		// Make sure permissions are in an octal string representation
		if (!is_string($permissions))
		{
			$permissions = decoct($permissions);
		}

		$commands = [
			'chmod ' . $permissions . ' ' . $this->getPath($fileName),
		];

		try
		{
			$this->executeServerCommands($commands);
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Create a directory if it doesn't exist. The operation is implicitly recursive, i.e. it will create all
	 * intermediate directories if they do not already exist.
	 *
	 * @param   string   $dirName      The full path of the directory to create
	 * @param   integer  $permissions  The permissions of the created directory
	 *
	 * @return  boolean  True on success
	 */
	public function mkdir($dirName, $permissions = 0755)
	{
		$targetDir = rtrim($dirName, '/');

		$directories = explode('/', $targetDir);

		$remoteDir = '';

		foreach ($directories as $dir)
		{
			if (!$dir)
			{
				continue;
			}

			$remoteDir .= '/' . $dir;

			// Continue if the folder already exists. Otherwise I'll get a an error even if everything is fine
			if ($this->isDir($remoteDir))
			{
				continue;
			}

			$commands = [
				'mkdir ' . $remoteDir,
			];

			try
			{
				$this->executeServerCommands($commands);
			}
			catch (RuntimeException $e)
			{
				return false;
			}
		}

		$this->chmod($dirName, $permissions);

		return true;
	}

	/**
	 * Checks if the given directory exists
	 *
	 * @param   string  $path  The full path of the remote directory to check
	 *
	 * @return  boolean  True if the directory exists
	 */
	public function isDir($path)
	{
		$ch = $this->getCurlHandle($path . '/');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$list = curl_exec($ch);

		$errNo = curl_errno($ch);
		curl_close($ch);

		if ($errNo)
		{
			return false;
		}

		return true;
	}

	/**
	 * Get the current working directory. NOT IMPLEMENTED.
	 *
	 * @return  string
	 */
	public function cwd()
	{
		return '';
	}

	/**
	 * Returns the absolute remote path from a path relative to the initial directory configured when creating the
	 * transfer object.
	 *
	 * @param   string  $fileName  The relative path of a file or directory
	 *
	 * @return  string  The absolute path for use by the transfer object
	 */
	public function getPath($fileName)
	{
		$fileName = str_replace('\\', '/', $fileName);

		if (strpos($fileName, $this->directory) === 0)
		{
			return $fileName;
		}

		$fileName = trim($fileName, '/');
		$fileName = rtrim($this->directory, '/') . '/' . $fileName;

		return $fileName;
	}

	/**
	 * Lists the subdirectories inside an SFTP directory
	 *
	 * @param   null|string  $dir  The directory to scan. Skip to use the current directory.
	 *
	 * @return  array|bool  A list of folders, or false if we could not get a listing
	 *
	 * @throws  RuntimeException  When the server is incompatible with our SFTP folder scanner
	 */
	public function listFolders($dir = null)
	{
		if (empty($dir))
		{
			$dir = $this->directory;
		}

		$dir = rtrim($dir, '/');

		$ch = $this->getCurlHandle($dir . '/');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$list = curl_exec($ch);

		$errNo = curl_errno($ch);
		$error = curl_error($ch);
		curl_close($ch);

		if ($errNo)
		{
			throw new RuntimeException(sprintf("cURL Error $errNo ($error) while listing contents of directory \"%s\" – make sure the folder exists and that you have adequate permissions to it", $dir), 500);
		}

		if (empty($list))
		{
			throw new RuntimeException("Sorry, your SFTP server doesn't support our SFTP directory browser.");
		}

		$folders = [];

		// Convert the directory listing into an array of lines without *NIX/Windows/Mac line ending characters
		$list = explode("\n", $list);
		$list = array_map('rtrim', $list);

		foreach ($list as $v)
		{
			$vInfo = preg_split("/[\s]+/", $v, 9);

			if ($vInfo[0] !== "total")
			{
				$perms = $vInfo[0];

				if (substr($perms, 0, 1) == 'd')
				{
					$folders[] = $vInfo[8];
				}
			}
		}

		asort($folders);

		return $folders;
	}

	/**
	 * Is the verbose debug option set?
	 *
	 * @return  boolean
	 */
	public function isVerbose()
	{
		return $this->verbose;
	}

	/**
	 * Set the verbose debug option
	 *
	 * @param   boolean  $verbose
	 *
	 * @return  void
	 */
	public function setVerbose($verbose)
	{
		$this->verbose = $verbose;
	}

	/**
	 * Returns a cURL resource handler for the remote SFTP server
	 *
	 * @param   string  $remoteFile  Optional. The remote file / folder on the SFTP server you'll be manipulating with cURL.
	 *
	 * @return  resource
	 */
	protected function getCurlHandle($remoteFile = '')
	{
		// Remember, the username has to be URL encoded as it's part of a URI!
		$authentication = urlencode($this->username);

		// We will only use username and password authentication if there are no certificates configured.
		if (empty($this->publicKey))
		{
			// Remember, both the username and password have to be URL encoded as they're part of a URI!
			$password       = urlencode($this->password);
			$authentication .= ':' . $password;
		}

		$ftpUri = 'sftp://' . $authentication . '@' . $this->host;

		if (!empty($this->port))
		{
			$ftpUri .= ':' . (int) $this->port;
		}

		// Relative path? Append the initial directory.
		if (substr($remoteFile, 0, 1) != '/')
		{
			$ftpUri .= $this->directory;
		}

		// Add a remote file if necessary. The filename must be URL encoded since we're creating a URI.
		if (!empty($remoteFile))
		{
			$suffix = '';

			$dirname = dirname($remoteFile);

			// Windows messing up dirname('/'). KILL ME.
			if ($dirname == '\\')
			{
				$dirname = '';
			}

			$dirname  = trim($dirname, '/');
			$basename = basename($remoteFile);

			if ((substr($remoteFile, -1) == '/') && !empty($basename))
			{
				$suffix = '/' . $suffix;
			}

			$ftpUri .= '/' . $dirname . (empty($dirname) ? '' : '/') . urlencode($basename) . $suffix;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $ftpUri);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

		// Do I have to use certificate authentication?
		if (!empty($this->publicKey))
		{
			// We always need to provide a public key file
			curl_setopt($ch, CURLOPT_SSH_PUBLIC_KEYFILE, $this->publicKey);

			// Since SSH certificates are self-signed we cannot have cURL verify their signatures against a CA.
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYSTATUS, 0);

			/**
			 * This is optional because newer versions of cURL can extract the private key file from a combined
			 * certificate file.
			 */
			if (!empty($this->privateKey))
			{
				curl_setopt($ch, CURLOPT_SSH_PRIVATE_KEYFILE, $this->privateKey);
			}

			/**
			 * In case of encrypted (a.k.a. password protected) private key files you need to also specify the
			 * certificate decryption key in the password field. However, if libcurl is compiled against the GnuTLS
			 * library (instead of OpenSSL) this will NOT work because of bugs / missing features in GnuTLS. It's the
			 * same problem you get when libssh is compiled against GnuTLS. The solution to that is having an
			 * unencrypted private key file.
			 */
			if (!empty($this->password))
			{
				curl_setopt($ch, CURLOPT_KEYPASSWD, $this->password);
			}
		}

		// Should I enable verbose output? Useful for debugging.
		if ($this->verbose)
		{
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
		}

		// Automatically create missing directories
		curl_setopt($ch, CURLOPT_FTP_CREATE_MISSING_DIRS, 1);

		return $ch;
	}

	/**
	 * Uploads a file using file contents provided through a file handle
	 *
	 * @param   string    $remoteFilename
	 * @param   resource  $fp
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 */
	protected function uploadFromHandle($remoteFilename, $fp)
	{
		// We need the file size. We can do that by getting the file position at EOF
		fseek($fp, 0, SEEK_END);
		$filesize = ftell($fp);
		rewind($fp);

		$ch = $this->getCurlHandle($remoteFilename);
		curl_setopt($ch, CURLOPT_UPLOAD, 1);
		curl_setopt($ch, CURLOPT_INFILE, $fp);
		curl_setopt($ch, CURLOPT_INFILESIZE, $filesize);

		curl_exec($ch);

		$error_no = curl_errno($ch);
		$error    = curl_error($ch);

		curl_close($ch);
		fclose($fp);

		if ($error_no)
		{
			throw new RuntimeException($error, $error_no);
		}
	}

	/**
	 * Downloads a remote file to the provided file handle
	 *
	 * @param   string    $remoteFilename  Filename on the remote server
	 * @param   resource  $fp              File handle where the downloaded content will be written to
	 * @param   bool      $close           Optional. Should I close the file handle when I'm done? (Default: true)
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 */
	protected function downloadToHandle($remoteFilename, $fp, $close = true)
	{
		$ch = $this->getCurlHandle($remoteFilename);

		curl_setopt($ch, CURLOPT_FILE, $fp);

		curl_exec($ch);

		$error_no = curl_errno($ch);
		$error    = curl_error($ch);

		curl_close($ch);

		if ($close)
		{
			fclose($fp);
		}

		if ($error_no)
		{
			throw new RuntimeException($error, $error_no);
		}
	}

	/**
	 * Downloads a remote file and returns it as a string
	 *
	 * @param   string  $remoteFilename  Filename on the remote server
	 *
	 * @return  string
	 *
	 * @throws  RuntimeException
	 */
	protected function downloadToString($remoteFilename)
	{
		$ch = $this->getCurlHandle($remoteFilename);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);

		$ret = curl_exec($ch);

		$error_no = curl_errno($ch);
		$error    = curl_error($ch);

		curl_close($ch);

		if ($error_no)
		{
			throw new RuntimeException($error, $error_no);
		}

		return $ret;
	}

	/**
	 * Executes arbitrary SFTP commands
	 *
	 * @param   array  $commands  An array with the SFTP commands to be executed
	 *
	 * @return  string  The output of the executed commands
	 *
	 * @throws  RuntimeException
	 */
	protected function executeServerCommands($commands)
	{
		$ch = $this->getCurlHandle($this->directory . '/');

		curl_setopt($ch, CURLOPT_QUOTE, $commands);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$listing = curl_exec($ch);
		$errNo   = curl_errno($ch);
		$error   = curl_error($ch);
		curl_close($ch);

		if ($errNo)
		{
			throw new RuntimeException($error, $errNo);
		}

		return $listing;
	}
}
