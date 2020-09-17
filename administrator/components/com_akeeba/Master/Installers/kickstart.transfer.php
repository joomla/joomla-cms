<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('KICKSTART') or die;

/**
 * Akeeba Kickstart Site Transfer Helper add-on feature
 *
 * This file allows to remotely transfer files and perform other tasks required during site transfer using Akeeba
 * Backup's Site Transfer Wizard. The features inside this file can only be accessed through Kickstart. Trying to access
 * this file directly will of course fail.
 */
class AKFeatureTransfer
{
	/**
	 * Returns information about the server we're running on.
	 *
	 * @param   array  $params
	 *
	 * @return  array
	 */
	public function serverInfo($params)
	{
		$maxExecTime    = 5;
		$memLimit       = '8M';
		$baseDir        = '';
		$disabled       = '';
		$maxPostSize    = '2M';
		$uploadMaxSize  = '2M';

		if (function_exists('ini_get'))
		{
			$maxExecTime    = ini_get("max_execution_time");
			$memLimit       = ini_get("memory_limit");
			$baseDir        = ini_get('open_basedir');
			$disabled       = ini_get("disable_functions");
			$maxPostSize    = ini_get("post_max_size");
			$uploadMaxSize  = ini_get("upload_max_filesize");

			if (empty($maxExecTime))
			{
				$maxExecTime = 5;
			}
		}

		$server = 'n/a';

		if (isset($_SERVER['SERVER_SOFTWARE']))
		{
			$server = $_SERVER['SERVER_SOFTWARE'];
		}
		elseif (($sf = getenv('SERVER_SOFTWARE')))
		{
			$server = $sf;
		}

		$infoArray = array(
			'freeSpace'     => disk_free_space(dirname(__FILE__)),
			'phpVersion'    => PHP_VERSION,
			'phpSAPI'       => PHP_SAPI,
			'phpOS'         => PHP_OS,
			'osVersion'     => php_uname('s'),
			'server'        => $server,
			'canWrite'      => $this->canWriteToFiles(),
			'canWriteTemp'  => $this->canWriteToFiles('kicktemp'),
			'maxExecTime'   => $maxExecTime,
			'memLimit'      => $this->memoryToBytes($memLimit),
			'maxPost'       => $this->memoryToBytes($maxPostSize),
			'maxUpload'     => $this->memoryToBytes($uploadMaxSize),
			'baseDir'       => $baseDir,
			'disabledFuncs' => $disabled,
		);

		return $infoArray;
	}

	public function uploadFile($params)
	{
		// Get the parameters describing the upload
		$file      = isset($_GET['file']) ? $_GET['file'] : '';
		$directory = isset($_GET['directory']) ? $_GET['directory'] : '';
		$frag      = isset($_GET['frag']) ? $_GET['frag'] : 0;
		$fragSize  = isset($_GET['fragSize']) ? $_GET['fragSize'] : 1048576;
		$data      = isset($_POST['data']) ? $_POST['data'] : '';
		$dataFile  = isset($_GET['dataFile']) ? $_GET['dataFile'] : '';

		// We need a file
		if (empty($file))
		{
			return array(
				'status'    => false,
				'message'   => 'You have not specified a file'
			);
		}

		// Let's make sure the remote end is not trying to do something nasty
		$file = basename($file);
		$pos = strrpos($file, '.');

		if ($pos === false)
		{
			return array(
				'status'    => false,
				'message'   => 'Invalid file name specified'
			);
		}

		$extension = substr($file, $pos + 1);

		if (empty($extension))
		{
			return array(
				'status'    => false,
				'message'   => 'Invalid file name specified'
			);
		}

		if (!preg_match('(jpa|zip|jps|j[\d]{2,}|z[\d]{2,})', $extension))
		{
			return array(
				'status'    => false,
				'message'   => 'Invalid file name specified'
			);
		}

		// We only allow very specific directories
		$directory = trim($directory, '/');

		if (!in_array($directory, array('', 'kicktemp')))
		{
			return array(
				'status'    => false,
				// Yes, the message is intentionally vague
				'message'   => 'Invalid file name specified'
			);
		}

		// If a data file was given, read it to memory
		if (empty($data) && !empty($dataFile))
		{
			// Do not remove the basename(). It makes sure we won't try to read a file outside our directory.
			$data = @file_get_contents(__DIR__ . '/' . basename($dataFile));
		}

		// We need some data to write, yes?
		if (empty($data))
		{
			return array(
				'status'    => false,
				'message'   => 'No data specified'
			);
		}

		if (!empty($directory))
		{
			$directory = '/' . $directory;
		}

		$filename = __DIR__ . $directory . '/' . $file;

		// Open the file for writing or append
		$mode = ($frag == 0) ? 'w' : 'a';
		$fp = @fopen($filename, $mode);

		if ($fp === false)
		{
			$modeHuman = ($mode == 'w') ? 'write' : 'append';

			return array(
				'status'    => false,
				'message'   => "Cannot open $file for $modeHuman"
			);
		}

		// Seek to the correct offset
		$offset = $frag * $fragSize;
		@fseek($fp, $offset);

		// Write to the file
		$written = @fwrite($fp, $data);

		@fclose($fp);

		if (!$written || ($written != strlen($data)))
		{
			return array(
				'status'    => false,
				'message'   => "Cannot write to $file"
			);
		}

		return array(
			'status'    => true,
			'message'   => ''
		);
	}

	/**
	 * Can I write to arbitrary files in the Kickstart directory?
	 *
	 * @return   bool
	 */
	private function canWriteToFiles($directory = '')
	{
		// Try to create a temporary file
		$directory = dirname(__FILE__) . '/' . $directory;
		$directory = rtrim($directory, '/');

		$testFilename = tempnam($directory, 'kst');

		// Failed completely?
		if ($testFilename === false)
		{
			return false;
		}

		// File created in another directory?
		if (dirname($testFilename) != $directory)
		{
			@unlink($testFilename);

			return false;
		}

		@unlink($testFilename);

		return true;
	}

	/**
	 * Converts a human formatted size to integer representation of bytes,
	 * e.g. 1M to 1024768
	 *
	 * @param   string  $setting  The value in human readable format, e.g. "1M"
	 *
	 * @return  integer  The value in bytes
	 */
	private function memoryToBytes($setting)
	{
		$val = trim($setting);
		$last = strtolower($val{strlen($val) - 1});

		if (is_numeric($last))
		{
			return $setting;
		}

		switch ($last)
		{
			case 't':
				$val *= 1024;
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return (int) $val;
	}
}
