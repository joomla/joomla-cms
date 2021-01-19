<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Archiver;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Base\Exceptions\ErrorException;
use Akeeba\Engine\Factory;

if (!function_exists('akstrlen'))
{
	/**
	 * Attempt to use mbstring for calculating the binary string length.
	 *
	 * @param $string
	 *
	 * @return int
	 */
	function akstrlen($string)
	{
		return function_exists('mb_strlen') ? mb_strlen($string, '8bit') : strlen($string);
	}
}

/**
 * Abstract class for an archiver using managed file pointers
 */
abstract class BaseFileManagement extends Base
{
	/** @var resource File pointer to the archive being currently written to */
	protected $fp = null;

	/** @var resource File pointer to the archive's central directory file (for ZIP) */
	protected $cdfp = null;

	/** @var   array  An array of open file pointers */
	private $filePointers = [];

	/** @var   array  An array of the last open files for writing and their last written to offsets */
	private $fileOffsets = [];

	/**
	 * Release file pointers when the object is being serialized
	 *
	 * @codeCoverageIgnore
	 *
	 * @return  void
	 */
	public function _onSerialize()
	{
		$this->_closeAllFiles();

		$this->fp   = null;
		$this->cdfp = null;
	}

	/**
	 * Release file pointers when the object is being destroyed
	 *
	 * @codeCoverageIgnore
	 *
	 * @return  void
	 */
	public function __destruct()
	{
		$this->_closeAllFiles();

		$this->fp   = null;
		$this->cdfp = null;
	}

	/**
	 * Opens a file, if it's not already open, or returns its cached file pointer if it's already open
	 *
	 * @param   string  $file  The filename to open
	 * @param   string  $mode  File open mode, defaults to binary write
	 *
	 * @return  resource
	 */
	protected function fopen($file, $mode = 'wb')
	{
		if (!array_key_exists($file, $this->filePointers))
		{
			//Factory::getLog()->debug("Opening backup archive $file with mode $mode");
			$this->filePointers[$file] = @fopen($file, $mode);

			// If we open a file for append we have to seek to the correct offset
			if (substr($mode, 0, 1) == 'a')
			{
				if (isset($this->fileOffsets[$file]))
				{
					Factory::getLog()->debug("Truncating backup archive file $file to " . $this->fileOffsets[$file] . " bytes");
					@ftruncate($this->filePointers[$file], $this->fileOffsets[$file]);
				}

				fseek($this->filePointers[$file], 0, SEEK_END);
			}
		}

		return $this->filePointers[$file];
	}

	/**
	 * Closes an already open file
	 *
	 * @param   resource  $fp  The file pointer to close
	 *
	 * @return  boolean
	 */
	protected function fclose(&$fp)
	{
		$offset = array_search($fp, $this->filePointers, true);

		$result = @fclose($fp);

		if ($offset !== false)
		{
			unset($this->filePointers[$offset]);
		}

		$fp = null;

		return $result;
	}

	protected function fcloseByName($file)
	{
		if (!array_key_exists($file, $this->filePointers))
		{
			return true;
		}

		$ret = $this->fclose($this->filePointers[$file]);

		if (array_key_exists($file, $this->filePointers))
		{
			unset($this->filePointers[$file]);
		}

		return $ret;
	}

	/**
	 * Write to file, defeating magic_quotes_runtime settings (pure binary write)
	 *
	 * @param   resource  $fp     Handle to a file
	 * @param   string    $data   The data to write to the file
	 * @param   integer   $p_len  Maximum length of data to write
	 *
	 * @return  int  The number of bytes written
	 *
	 * @throws  ErrorException  When writing to the file is not possible
	 */
	protected function fwrite($fp, $data, $p_len = null)
	{
		static $lastFp = null;
		static $filename = null;

		if ($fp !== $lastFp)
		{
			$lastFp   = $fp;
			$filename = array_search($fp, $this->filePointers, true);
		}

		$len = is_null($p_len) ? (akstrlen($data)) : $p_len;
		$ret = fwrite($fp, $data, $len);

		if (($ret === false) || (abs(($ret - $len)) >= 1))
		{
			// Log debug information about the archive file's existence and current size. This helps us figure out if
			// there is a server-imposed maximum file size limit.
			clearstatcache();
			$fileExists  = @file_exists($filename) ? 'exists' : 'does NOT exist';
			$currentSize = @filesize($filename);

			Factory::getLog()->debug(__CLASS__ . "::_fwrite() ERROR!! Cannot write to archive file $filename. The file $fileExists. File size $currentSize bytes after writing $ret of $len bytes. Please check the output directory permissions and make sure you have enough disk space available. If this does not help, please set up a Part Size for Split Archives LOWER than this size and retry backing up.");

			throw new ErrorException('Couldn\'t write to the archive file; check the output directory permissions and make sure you have enough disk space available.' . "[len=$ret / $len]");
		}

		if ($filename !== false)
		{
			$this->fileOffsets[$filename] = @ftell($fp);
		}

		return $ret;
	}

	/**
	 * Removes a file path from the list of resumable offsets
	 *
	 * @param $filename
	 */
	protected function removeFromOffsetsList($filename)
	{
		if (isset($this->fileOffsets[$filename]))
		{
			unset($this->fileOffsets[$filename]);
		}
	}

	/**
	 * Closes all open files known to this archiver object
	 *
	 * @return  void
	 */
	protected function _closeAllFiles()
	{
		if (!empty($this->filePointers))
		{
			foreach ($this->filePointers as $file => $fp)
			{
				@fclose($fp);

				unset($this->filePointers[$file]);
			}
		}
	}

}
