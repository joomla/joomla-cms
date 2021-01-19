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
use Akeeba\Engine\Base\Exceptions\WarningException;
use Akeeba\Engine\Factory;

if (!defined('AKEEBA_CHUNK'))
{
	$configuration = Factory::getConfiguration();
	$chunksize     = $configuration->get('engine.archiver.common.chunk_size', 1048576);
	define('AKEEBA_CHUNK', $chunksize);
}

if (!function_exists('aksubstr'))
{
	/**
	 * Attempt to use mbstring for getting parts of strings
	 *
	 * @param   string    $string
	 * @param   int       $start
	 * @param   int|null  $length
	 *
	 * @return  string
	 */
	function aksubstr($string, $start, $length = null)
	{
		return function_exists('mb_substr') ? mb_substr($string, $start, $length, '8bit') :
			substr($string, $start, $length);
	}
}

/**
 * Abstract class for custom archiver implementations
 */
abstract class BaseArchiver extends BaseFileManagement
{
	/** @var   array  The last part which has been finalized and waits to be post-processed */
	public $finishedPart = [];

	/** @var resource File pointer to the archive being currently written to */
	protected $fp = null;

	/** @var resource File pointer to the archive's central directory file (for ZIP) */
	protected $cdfp = null;

	/** @var string The name of the file holding the archive's data, which becomes the final archive */
	protected $_dataFileName;

	/** @var string Archive full path without extension */
	protected $dataFileNameWithoutExtension = '';

	/** @var bool Should I store symlinks as such (no dereferencing?) */
	protected $storeSymlinkTarget = false;

	/** @var int Part size for split archives, in bytes */
	protected $partSize = 0;

	/** @var bool Should I use Split ZIP? */
	protected $useSplitArchive = false;

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
	 * Create a new archive part file (but does NOT open it for writing)
	 *
	 * @param   bool  $finalPart  True if this is the final part
	 *
	 * @return  bool  False if creating a new part fails
	 */
	abstract protected function createNewPartFile($finalPart = false);

	/**
	 * Create a new part file and open it for writing
	 *
	 * @param   bool  $finalPart  Is this the final part?
	 *
	 * @return  void
	 */
	protected function createAndOpenNewPart($finalPart = false)
	{
		@$this->fclose($this->fp);
		$this->fp = null;

		// Not enough space on current part, create new part
		if (!$this->createNewPartFile($finalPart))
		{
			$extension = $this->getExtension();
			$extension = ltrim(strtoupper($extension), '.');

			throw new ErrorException("Could not create new $extension part file " . basename($this->_dataFileName));
		}

		$this->openArchiveForOutput(true);
	}

	/**
	 * Create a new backup archive
	 *
	 * @return  void
	 *
	 * @throws  ErrorException
	 */
	protected function createNewBackupArchive()
	{
		Factory::getLog()->debug(__CLASS__ . " :: Killing old archive");

		$this->fp = $this->fopen($this->_dataFileName, "wb");

		if ($this->fp === false)
		{
			if (file_exists($this->_dataFileName))
			{
				@unlink($this->_dataFileName);
			}

			@touch($this->_dataFileName);
			@chmod($this->_dataFileName, 0666);

			$this->fp = $this->fopen($this->_dataFileName, "wb");

			if ($this->fp !== false)
			{
				throw new ErrorException("Could not open archive file '{$this->_dataFileName}' for append!");
			}
		}

		@ftruncate($this->fp, 0);
	}

	/**
	 * Opens the backup archive file for output. Returns false if the archive file cannot be opened in binary append
	 * mode.
	 *
	 * @param   bool  $force  Should I forcibly reopen the file? If false, I'll only open the file if the current
	 *                        file pointer is null.
	 *
	 * @return  void
	 */
	protected function openArchiveForOutput($force = false)
	{
		if (is_null($this->fp) || $force)
		{
			$this->fp = $this->fopen($this->_dataFileName, "ab");
		}

		if ($this->fp === false)
		{
			$this->fp = null;

			throw new ErrorException("Could not open archive file '{$this->_dataFileName}' for append!");
		}
	}

	/**
	 * Converts a human formatted size to integer representation of bytes,
	 * e.g. 1M to 1024768
	 *
	 * @param   string  $setting  The value in human readable format, e.g. "1M"
	 *
	 * @return  integer  The value in bytes
	 */
	protected function humanToIntegerBytes($setting)
	{
		$val  = trim($setting);
		$last = strtolower($val[strlen($val) - 1]);

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

	/**
	 * Get the PHP memory limit in bytes
	 *
	 * @return int|null  Memory limit in bytes or null if we can't figure it out.
	 */
	protected function getMemoryLimit()
	{
		if (!function_exists('ini_get'))
		{
			return null;
		}

		$memLimit = ini_get("memory_limit");

		if ((is_numeric($memLimit) && ($memLimit < 0)) || !is_numeric($memLimit))
		{
			$memLimit = 0; // 1.2a3 -- Rare case with memory_limit < 0, e.g. -1Mb!
		}

		$memLimit = $this->humanToIntegerBytes($memLimit);

		return $memLimit;
	}

	/**
	 * Enable storing of symlink target if we are not on Windows
	 *
	 * @return  void
	 */
	protected function enableSymlinkTargetStorage()
	{
		$configuration       = Factory::getConfiguration();
		$dereferenceSymlinks = $configuration->get('engine.archiver.common.dereference_symlinks', true);

		if ($dereferenceSymlinks)
		{
			return;
		}

		// We are told not to dereference symlinks. Are we on Windows?
		$isWindows = (DIRECTORY_SEPARATOR == '\\');

		if (function_exists('php_uname'))
		{
			$isWindows = stristr(php_uname(), 'windows');
		}

		// If we are not on Windows, enable symlink target storage
		$this->storeSymlinkTarget = !$isWindows;
	}

	/**
	 * Gets the file size and last modification time (also works on virtual files and symlinks)
	 *
	 * @param   string  $sourceNameOrData  File path to the source file or source data (if $isVirtual is true)
	 * @param   bool    $isVirtual         Is this a virtual file?
	 * @param   bool    $isSymlink         Is this a symlink?
	 * @param   bool    $isDir             Is this a directory?
	 *
	 * @return  array
	 */
	protected function getFileSizeAndModificationTime(&$sourceNameOrData, $isVirtual, $isSymlink, $isDir)
	{
		// Get real size before compression
		if ($isVirtual)
		{
			$fileSize    = akstrlen($sourceNameOrData);
			$fileModTime = time();

			return [$fileSize, $fileModTime];
		}


		if ($isSymlink)
		{
			$fileSize    = akstrlen(@readlink($sourceNameOrData));
			$fileModTime = 0;

			return [$fileSize, $fileModTime];
		}

		// Is the file readable?
		if (!is_readable($sourceNameOrData) && !$isDir)
		{
			// Really, REALLY check if it is readable (PHP sometimes lies, dammit!)
			$myFP = @$this->fopen($sourceNameOrData, 'rb');

			if ($myFP === false)
			{
				// Unreadable file, skip it.
				throw new WarningException('Unreadable file ' . $sourceNameOrData . '. Check permissions');
			}

			@$this->fclose($myFP);
		}

		// Get the file size
		$fileSize    = $isDir ? 0 : @filesize($sourceNameOrData);
		$fileModTime = $isDir ? 0 : @filemtime($sourceNameOrData);

		return [$fileSize, $fileModTime];
	}

	/**
	 * Get the preferred compression method for a file
	 *
	 * @param   int   $fileSize   File size in bytes
	 * @param   int   $memLimit   Memory limit in bytes
	 * @param   bool  $isDir      Is it a directory?
	 * @param   bool  $isSymlink  Is it a symlink?
	 *
	 * @return  int  Compression method to use: 0 (uncompressed) or 1 (gzip deflate)
	 */
	protected function getCompressionMethod($fileSize, $memLimit, $isDir, $isSymlink)
	{
		// If we don't have gzip installed we can't compress anything
		if (!function_exists("gzcompress"))
		{
			return 0;
		}

		// Don't compress directories or symlinks
		if ($isDir || $isSymlink)
		{
			return 0;
		}

		// Do not compress files over the compression threshold
		if ($fileSize >= _AKEEBA_COMPRESSION_THRESHOLD)
		{
			return 0;
		}

		// No memory limit, file smaller than the compression threshold: always compress.
		if (is_numeric($memLimit) && ($memLimit == 0))
		{
			return 1;
		}

		// Non-zero memory limit, PHP can report memory usage, see if there's enough memory.
		if (is_numeric($memLimit) && function_exists("memory_get_usage"))
		{
			$availableRAM = $memLimit - memory_get_usage();
			// Conservative approach: if the file size is over 40% of the available memory we won't compress.
			$compressionMethod = (($availableRAM / 2.5) >= $fileSize) ? 1 : 0;

			return $compressionMethod;
		}

		// Non-zero memory limit, PHP can't report memory usage, compress only files up to 512Kb (very conservative)
		return ($fileSize <= 524288) ? 1 : 0;
	}

	/**
	 * Checks if the file exists and is readable
	 *
	 * @param   string  $sourceNameOrData  The path to the file being compressed, or the raw file data for virtual files
	 * @param   bool    $isVirtual         Is this a virtual file?
	 * @param   bool    $isSymlink         Is this a symlink?
	 * @param   bool    $isDir             Is this a directory?
	 *
	 * @return  void
	 *
	 * @throws  WarningException
	 */
	protected function testIfFileExists(&$sourceNameOrData, &$isVirtual, &$isDir, &$isSymlink)
	{
		if ($isVirtual || $isDir)
		{
			return;
		}

		if (!@file_exists($sourceNameOrData))
		{
			if ($isSymlink)
			{
				throw new WarningException('The symlink ' . $sourceNameOrData . ' points to a file or folder that no longer exists and will NOT be backed up.');
			}

			throw new WarningException('The file ' . $sourceNameOrData . ' no longer exists and will NOT be backed up. Are you backing up temporary or cache data?');
		}

		if (!@is_readable($sourceNameOrData))
		{
			throw new WarningException('Unreadable file ' . $sourceNameOrData . '. Check permissions.');
		}
	}

	/**
	 * Try to get the compressed data for a file
	 *
	 * @param   string  $sourceNameOrData
	 * @param   bool    $isVirtual
	 * @param   int     $compressionMethod
	 * @param   string  $zdata
	 * @param   int     $unc_len
	 * @param   int     $c_len
	 *
	 * @return  void
	 */
	protected function getZData(&$sourceNameOrData, &$isVirtual, &$compressionMethod, &$zdata, &$unc_len, &$c_len)
	{
		// Get uncompressed data
		$udata =& $sourceNameOrData;

		if (!$isVirtual)
		{
			$udata = @file_get_contents($sourceNameOrData);
		}

		// If the compression fails, we will let it behave like no compression was available
		$c_len             = $unc_len;
		$compressionMethod = 0;

		// Proceed with compression
		$zdata = @gzcompress($udata);

		if ($zdata !== false)
		{
			// The compression succeeded
			unset($udata);
			$compressionMethod = 1;
			$zdata             = aksubstr($zdata, 2, -4);
			$c_len             = akstrlen($zdata);
		}
	}

	/**
	 * Returns the bytes available for writing data to the current part file (i.e. part size minus current offset)
	 *
	 * @return  int
	 */
	protected function getPartFreeSize()
	{
		clearstatcache();
		$current_part_size = @filesize($this->_dataFileName);

		return (int) $this->partSize - ($current_part_size === false ? 0 : $current_part_size);
	}

	/**
	 * Enable split archive creation where possible
	 *
	 * @return  void
	 */
	protected function enableSplitArchives()
	{
		$configuration = Factory::getConfiguration();
		$partSize      = $configuration->get('engine.archiver.common.part_size', 0);

		// If the part size is less than 64Kb we won't enable split archives
		if ($partSize < 65536)
		{
			return;
		}

		$extension            = $this->getExtension();
		$altExtension         = substr($extension, 0, 2) . '01';
		$archiveTypeUppercase = strtoupper(substr($extension, 1));

		Factory::getLog()->info(__CLASS__ . " :: Split $archiveTypeUppercase creation enabled");

		$this->useSplitArchive              = true;
		$this->partSize                     = $partSize;
		$this->dataFileNameWithoutExtension =
			dirname($this->_dataFileName) . '/' . basename($this->_dataFileName, $extension);
		$this->_dataFileName                = $this->dataFileNameWithoutExtension . $altExtension;

		// Indicate that we have at least 1 part
		$statistics = Factory::getStatistics();
		$statistics->updateMultipart(1);
	}

	/**
	 * Write a file's GZip compressed data to the archive, taking into account archive splitting
	 *
	 * @param   string  $zdata  The compressed data to write to the archive
	 *
	 * @return  void
	 */
	protected function putRawDataIntoArchive(&$zdata)
	{
		// Single part archive. Just dump the compressed data.
		if (!$this->useSplitArchive)
		{
			$this->fwrite($this->fp, $zdata);

			return;
		}

		// Split JPA. Check if we need to split the part in the middle of the data.
		$freeSpaceInPart = $this->getPartFreeSize();

		// Nope. We have enough space to write all of the data in this part.
		if ($freeSpaceInPart >= akstrlen($zdata))
		{
			$this->fwrite($this->fp, $zdata);

			return;
		}

		$bytesLeftInData = akstrlen($zdata);

		while ($bytesLeftInData > 0)
		{
			// Try to write to the archive. We can only write as much bytes as the free space in the backup archive OR
			// the total data bytes left, whichever is lower.
			$bytesWritten = $this->fwrite($this->fp, $zdata, min($bytesLeftInData, $freeSpaceInPart));

			// Since we may have written fewer bytes than anticipated we use the real bytes written for calculations
			$freeSpaceInPart -= $bytesWritten;
			$bytesLeftInData -= $bytesWritten;

			// If we still have data to write, remove the part already written and keep the rest
			if ($bytesLeftInData > 0)
			{
				$zdata = aksubstr($zdata, -$bytesLeftInData);
			}

			// If the part file is full create a new one
			if ($freeSpaceInPart <= 0)
			{
				// Create new part
				$this->createAndOpenNewPart();

				// Get its free space
				$freeSpaceInPart = $this->getPartFreeSize();
			}
		}

		// Tell PHP to free up some memory
		$zdata = null;
	}

	/**
	 * Begin or resume adding an uncompressed file into the archive.
	 *
	 * IMPORTANT! Only this case can be spanned across steps: uncompressed, non-virtual data
	 *
	 * @param   string  $sourceNameOrData  The path to the file we are reading from.
	 * @param   int     $fileLength        The file size we are supposed to read, in bytes.
	 * @param   int     $resumeOffset      Offset in the file to resume reading from
	 *
	 * @return  bool  True to indicate more processing is required in the next step
	 */
	protected function putUncompressedFileIntoArchive(&$sourceNameOrData, $fileLength = 0, $resumeOffset = null)
	{
		// Copy the file contents, ignore directories
		$sourceFilePointer = @fopen($sourceNameOrData, "rb");

		if ($sourceFilePointer === false)
		{
			// If we have already written the file header and can't read the data your archive is busted.
			throw new ErrorException('Unreadable file ' . $sourceNameOrData . '. Check permissions. Your archive is corrupt!');
		}

		// Seek to the resume point if required
		if (!is_null($resumeOffset))
		{
			// Seek to new offset
			$seek_result = @fseek($sourceFilePointer, $resumeOffset);

			if ($seek_result === -1)
			{
				// What?! We can't resume!
				@fclose($sourceFilePointer);

				throw new ErrorException(sprintf('Could not resume packing of file %s. Your archive is damaged!', $sourceNameOrData));
			}

			// Change the uncompressed size to reflect the remaining data
			$fileLength -= $resumeOffset;
		}

		$mustBreak = $this->putDataFromFileIntoArchive($sourceFilePointer, $fileLength);

		@fclose($sourceFilePointer);

		return $mustBreak;
	}

	/**
	 * Put up to $fileLength bytes of the file pointer $sourceFilePointer into the backup archive. Returns true if we
	 * ran out of time and need to perform a step break. Returns false when the whole quantity of data has been copied.
	 * Throws an ErrorException if soemthing really bad happens.
	 *
	 * @param   resource  $sourceFilePointer  The pointer to the input file
	 * @param   int       $fileLength         How many bytes to copy
	 *
	 * @return  bool  True to indicate we need to resume packing the file in the next step
	 */
	private function putDataFromFileIntoArchive(&$sourceFilePointer, &$fileLength)
	{
		// Get references to engine objects we're going to be using
		$configuration = Factory::getConfiguration();
		$timer         = Factory::getTimer();

		// Quick copy data into the archive, AKEEBA_CHUNK bytes at a time
		while (!feof($sourceFilePointer) && ($timer->getTimeLeft() > 0) && ($fileLength > 0))
		{
			// Normally I read up to AKEEBA_CHUNK bytes at a time
			$chunkSize = AKEEBA_CHUNK;

			// Do I have a split ZIP?
			if ($this->useSplitArchive)
			{
				// I must only read up to the free space in the part file if it's less than AKEEBA_CHUNK.
				$free_space = $this->getPartFreeSize();
				$chunkSize  = min($free_space, AKEEBA_CHUNK);

				// If I ran out of free space I have to create a new part file.
				if ($free_space <= 0)
				{
					$this->createAndOpenNewPart();

					// We have created the part. If the user asked for immediate post-proc, break step now.
					if ($configuration->get('engine.postproc.common.after_part', 0))
					{
						$resumeOffset = @ftell($sourceFilePointer);
						@fclose($sourceFilePointer);

						$configuration->set('volatile.engine.archiver.resume', $resumeOffset);
						$configuration->set('volatile.engine.archiver.processingfile', true);
						$configuration->set('volatile.breakflag', true);

						// Always close the open part when immediate post-processing is requested
						@$this->fclose($this->fp);
						$this->fp = null;

						return true;
					}

					// No immediate post-proc. Recalculate the optimal chunk size.
					$free_space = $this->getPartFreeSize();
					$chunkSize  = min($free_space, AKEEBA_CHUNK);
				}
			}

			// Read some data and write it to the backup archive part file
			$data         = fread($sourceFilePointer, $chunkSize);
			$bytesWritten = $this->fwrite($this->fp, $data, akstrlen($data));

			// Subtract the written bytes from the bytes left to write
			$fileLength -= $bytesWritten;
		}

		/**
		 * According to the file size we read when we were writing the file header we have more data to write. However,
		 * we reached the end of the file. This means the file went away or shrunk. We can't reliably go back and
		 * change the file header since it may be in a previous part file that's already been post-processed. All we can
		 * do is try to warn the user.
		 */
		while (feof($sourceFilePointer) && ($timer->getTimeLeft() > 0) && ($fileLength > 0))
		{
			throw new ErrorException('The file shrunk or went away while putting it in the backup archive. Your archive is damaged! If this is a temporary or cache file we advise you to exclude the contents of the temporary / cache folder it is contained in.');
		}

		// WARNING!!! The extra $unc_len != 0 check is necessary as PHP won't reach EOF for 0-byte files.
		if (!feof($sourceFilePointer) && ($fileLength != 0))
		{
			// We have to break, or we'll time out!
			$resumeOffset = @ftell($sourceFilePointer);
			@fclose($sourceFilePointer);

			$configuration->set('volatile.engine.archiver.resume', $resumeOffset);
			$configuration->set('volatile.engine.archiver.processingfile', true);

			return true;
		}

		return false;
	}
}
