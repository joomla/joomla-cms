<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  dispatcher
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

class FOFDownload
{
	/**
	 * Parameters passed from the GUI when importing from URL
	 *
	 * @var  array
	 */
	private $params = array();

	/**
	 * The download adapter which will be used by this class
	 *
	 * @var  FOFDownloadInterface
	 */
	private $adapter = null;

	/**
	 * Additional params that will be passed to the adapter while performing the download
	 *
	 * @var  array
	 */
	private $adapterOptions = array();

	/**
	 * Creates a new download object and assigns it the most fitting download adapter
	 */
	public function __construct()
	{
		// Find the best fitting adapter
		$allAdapters = self::getFiles(__DIR__ . '/adapter', array(), array('abstract.php'));
		$priority    = 0;

		foreach ($allAdapters as $adapterInfo)
		{
			if (!class_exists($adapterInfo['classname'], true))
			{
				continue;
			}

			/** @var FOFDownloadAdapterAbstract $adapter */
			$adapter = new $adapterInfo['classname'];

			if ( !$adapter->isSupported())
			{
				continue;
			}

			if ($adapter->priority > $priority)
			{
				$this->adapter = $adapter;
				$priority      = $adapter->priority;
			}
		}

		// Load the language strings
		FOFPlatform::getInstance()->loadTranslations('lib_fof');
	}

	/**
	 * Forces the use of a specific adapter
	 *
	 * @param  string $className   The name of the class or the name of the adapter, e.g. 'FOFDownloadAdapterCurl' or
	 *                             'curl'
	 */
	public function setAdapter($className)
	{
		$adapter = null;

		if (class_exists($className, true))
		{
			$adapter = new $className;
		}
		elseif (class_exists('FOFDownloadAdapter' . ucfirst($className)))
		{
			$className = 'FOFDownloadAdapter' . ucfirst($className);
			$adapter   = new $className;
		}

		if (is_object($adapter) && ($adapter instanceof FOFDownloadInterface))
		{
			$this->adapter = $adapter;
		}
	}

	/**
	 * Returns the name of the current adapter
	 *
	 * @return string
	 */
	public function getAdapterName()
	{
		if(is_object($this->adapter))
		{
			$class = get_class($this->adapter);

			return strtolower(str_ireplace('FOFDownloadAdapter', '', $class));
		}

		return '';
	}

	/**
	 * Sets the additional options for the adapter
	 *
	 * @param array $options
	 */
	public function setAdapterOptions(array $options)
	{
		$this->adapterOptions = $options;
	}

	/**
	 * Returns the additional options for the adapter
	 *
	 * @return array
	 */
	public function getAdapterOptions()
	{
		return $this->adapterOptions;
	}

	/**
	 * Used to decode the $params array
	 *
	 * @param   string $key     The parameter key you want to retrieve the value for
	 * @param   mixed  $default The default value, if none is specified
	 *
	 * @return  mixed  The value for this parameter key
	 */
	private function getParam($key, $default = null)
	{
		if (array_key_exists($key, $this->params))
		{
			return $this->params[$key];
		}
		else
		{
			return $default;
		}
	}

	/**
	 * Download data from a URL and return it
	 *
	 * @param   string $url The URL to download from
	 *
	 * @return  bool|string  The downloaded data or false on failure
	 */
	public function getFromURL($url)
	{
		try
		{
			return $this->adapter->downloadAndReturn($url, null, null, $this->adapterOptions);
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Performs the staggered download of file. The downloaded file will be stored in Joomla!'s temp-path using the
	 * basename of the URL as a filename
	 *
	 * The $params array can have any of the following keys
	 * url			The file being downloaded
	 * frag			Rolling counter of the file fragment being downloaded
	 * totalSize	The total size of the file being downloaded, in bytes
	 * doneSize		How many bytes we have already downloaded
	 * maxExecTime	Maximum execution time downloading file fragments, in seconds
	 * length		How many bytes to download at once
	 *
	 * The array returned is in the following format:
	 *
	 * status		True if there are no errors, false if there are errors
	 * error		A string with the error message if there are errors
	 * frag			The next file fragment to download
	 * totalSize	The total size of the downloaded file in bytes, if the server supports HEAD requests
	 * doneSize		How many bytes have already been downloaded
	 * percent		% of the file already downloaded (if totalSize could be determined)
	 * localfile	The name of the local file, without the path
	 *
	 * @param   array $params A parameters array, as sent by the user interface
	 *
	 * @return  array  A return status array
	 */
	public function importFromURL($params)
	{
		$this->params = $params;

		// Fetch data
		$url         	= $this->getParam('url');
		$localFilename	= $this->getParam('localFilename');
		$frag        	= $this->getParam('frag', -1);
		$totalSize   	= $this->getParam('totalSize', -1);
		$doneSize    	= $this->getParam('doneSize', -1);
		$maxExecTime 	= $this->getParam('maxExecTime', 5);
		$runTimeBias 	= $this->getParam('runTimeBias', 75);
		$length      	= $this->getParam('length', 1048576);

		if (empty($localFilename))
		{
			$localFilename = basename($url);

			if (strpos($localFilename, '?') !== false)
			{
				$paramsPos = strpos($localFilename, '?');
				$localFilename = substr($localFilename, 0, $paramsPos - 1);
			}
		}

		$tmpDir        = JFactory::getConfig()->get('tmp_path', JPATH_ROOT . '/tmp');
		$tmpDir        = rtrim($tmpDir, '/\\');

		// Init retArray
		$retArray = array(
			"status"    => true,
			"error"     => '',
			"frag"      => $frag,
			"totalSize" => $totalSize,
			"doneSize"  => $doneSize,
			"percent"   => 0,
			"localfile"	=> $localFilename
		);

		try
		{
			$timer = new FOFUtilsTimer($maxExecTime, $runTimeBias);
			$start = $timer->getRunningTime(); // Mark the start of this download
			$break = false; // Don't break the step

			// Figure out where on Earth to put that file
			$local_file = $tmpDir . '/' . $localFilename;

			while (($timer->getTimeLeft() > 0) && !$break)
			{
				// Do we have to initialize the file?
				if ($frag == -1)
				{
					// Currently downloaded size
					$doneSize = 0;

					if (@file_exists($local_file))
					{
						@unlink($local_file);
					}

					// Delete and touch the output file
					$fp = @fopen($local_file, 'wb');

					if ($fp !== false)
					{
						@fclose($fp);
					}

					// Init
					$frag = 0;

					//debugMsg("-- First frag, getting the file size");
					$retArray['totalSize'] = $this->adapter->getFileSize($url);
					$totalSize             = $retArray['totalSize'];
				}

				// Calculate from and length
				$from = $frag * $length;
				$to   = $length + $from - 1;

				// Try to download the first frag
				$required_time = 1.0;

				try
				{
					$result = $this->adapter->downloadAndReturn($url, $from, $to, $this->adapterOptions);

					if ($result === false)
					{
						throw new Exception(JText::sprintf('LIB_FOF_DOWNLOAD_ERR_COULDNOTDOWNLOADFROMURL', $url), 500);
					}
				}
				catch (Exception $e)
				{
					$result = false;
					$error  = $e->getMessage();
				}

				if ($result === false)
				{
					// Failed download
					if ($frag == 0)
					{
						// Failure to download first frag = failure to download. Period.
						$retArray['status'] = false;
						$retArray['error']  = $error;

						//debugMsg("-- Download FAILED");

						return $retArray;
					}
					else
					{
						// Since this is a staggered download, consider this normal and finish
						$frag = -1;
						//debugMsg("-- Import complete");
						$totalSize = $doneSize;
						$break     = true;
					}
				}

				// Add the currently downloaded frag to the total size of downloaded files
				if ($result)
				{
					$filesize = strlen($result);
					//debugMsg("-- Successful download of $filesize bytes");
					$doneSize += $filesize;

					// Append the file
					$fp = @fopen($local_file, 'ab');

					if ($fp === false)
					{
						//debugMsg("-- Can't open local file $local_file for writing");
						// Can't open the file for writing
						$retArray['status'] = false;
						$retArray['error']  = JText::sprintf('LIB_FOF_DOWNLOAD_ERR_COULDNOTWRITELOCALFILE', $local_file);

						return $retArray;
					}

					fwrite($fp, $result);
					fclose($fp);

					//debugMsg("-- Appended data to local file $local_file");

					$frag++;

					//debugMsg("-- Proceeding to next fragment, frag $frag");

					if (($filesize < $length) || ($filesize > $length))
					{
						// A partial download or a download larger than the frag size means we are done
						$frag = -1;
						//debugMsg("-- Import complete (partial download of last frag)");
						$totalSize = $doneSize;
						$break     = true;
					}
				}

				// Advance the frag pointer and mark the end
				$end = $timer->getRunningTime();

				// Do we predict that we have enough time?
				$required_time = max(1.1 * ($end - $start), $required_time);

				if ($required_time > (10 - $end + $start))
				{
					$break = true;
				}

				$start = $end;
			}

			if ($frag == -1)
			{
				$percent = 100;
			}
			elseif ($doneSize <= 0)
			{
				$percent = 0;
			}
			else
			{
				if ($totalSize > 0)
				{
					$percent = 100 * ($doneSize / $totalSize);
				}
				else
				{
					$percent = 0;
				}
			}

			// Update $retArray
			$retArray = array(
				"status"    => true,
				"error"     => '',
				"frag"      => $frag,
				"totalSize" => $totalSize,
				"doneSize"  => $doneSize,
				"percent"   => $percent,
			);
		}
		catch (Exception $e)
		{
			//debugMsg("EXCEPTION RAISED:");
			//debugMsg($e->getMessage());
			$retArray['status'] = false;
			$retArray['error']  = $e->getMessage();
		}

		return $retArray;
	}

	/**
	 * This method will crawl a starting directory and get all the valid files
	 * that will be analyzed by __construct. Then it organizes them into an
	 * associative array.
	 *
	 * @param   string $path          Folder where we should start looking
	 * @param   array  $ignoreFolders Folder ignore list
	 * @param   array  $ignoreFiles   File ignore list
	 *
	 * @return  array   Associative array, where the `fullpath` key contains the path to the file,
	 *                  and the `classname` key contains the name of the class
	 */
	protected static function getFiles($path, array $ignoreFolders = array(), array $ignoreFiles = array())
	{
		$return = array();

		$files = self::scanDirectory($path, $ignoreFolders, $ignoreFiles);

		// Ok, I got the files, now I have to organize them
		foreach ($files as $file)
		{
			$clean = str_replace($path, '', $file);
			$clean = trim(str_replace('\\', '/', $clean), '/');

			$parts = explode('/', $clean);

			$return[] = array(
				'fullpath'  => $file,
				'classname' => 'FOFDownloadAdapter' . ucfirst(basename($parts[0], '.php'))
			);
		}

		return $return;
	}

	/**
	 * Recursive function that will scan every directory unless it's in the
	 * ignore list. Files that aren't in the ignore list are returned.
	 *
	 * @param   string $path          Folder where we should start looking
	 * @param   array  $ignoreFolders Folder ignore list
	 * @param   array  $ignoreFiles   File ignore list
	 *
	 * @return  array   List of all the files
	 */
	protected static function scanDirectory($path, array $ignoreFolders = array(), array $ignoreFiles = array())
	{
		$return = array();

		$handle = @opendir($path);

		if ( !$handle)
		{
			return $return;
		}

		while (($file = readdir($handle)) !== false)
		{
			if ($file == '.' || $file == '..')
			{
				continue;
			}

			$fullpath = $path . '/' . $file;

			if ((is_dir($fullpath) && in_array($file, $ignoreFolders)) || (is_file($fullpath) && in_array($file, $ignoreFiles)))
			{
				continue;
			}

			if (is_dir($fullpath))
			{
				$return = array_merge(self::scanDirectory($fullpath, $ignoreFolders, $ignoreFiles), $return);
			}
			else
			{
				$return[] = $path . '/' . $file;
			}
		}

		return $return;
	}
}