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
use RuntimeException;

/**
 * JPA creation class
 *
 * JPA Format 1.2 implemented, minus BZip2 compression support
 */
class Jpa extends BaseArchiver
{
	/** @var integer How many files are contained in the archive */
	private $totalFilesCount = 0;

	/** @var integer The total size of files contained in the archive as they are stored */
	private $totalCompressedSize = 0;

	/** @var integer The total size of files contained in the archive when they are extracted to disk. */
	private $totalUncompressedSize = 0;

	/** @var string Standard Header signature */
	private $archiveSignature = "\x4A\x50\x41";

	/** @var string Entity Block signature */
	private $fileHeaderSignature = "\x4A\x50\x46";

	/** @var string Marks the split archive's extra header */
	private $splitArchiveExtraHeader = "\x4A\x50\x01\x01"; //

	/** @var int Current part file number */
	private $currentPartNumber = 1;

	/** @var int Total number of part files */
	private $totalParts = 1;

	/**
	 * Initialises the archiver class, creating the archive from an existent
	 * installer's JPA archive.
	 *
	 * @param   string  $targetArchivePath  Absolute path to the generated archive
	 * @param   array   $options            A named key array of options (optional)
	 *
	 * @return  void
	 */
	public function initialize($targetArchivePath, $options = [])
	{
		Factory::getLog()->debug(__CLASS__ . " :: new instance - archive $targetArchivePath");
		$this->_dataFileName = $targetArchivePath;

		// Should we enable Split ZIP feature?
		$this->enableSplitArchives();

		// Should I use Symlink Target Storage?
		$this->enableSymlinkTargetStorage();

		// Try to kill the archive if it exists
		$this->createNewBackupArchive();

		// Write the initial instance of the archive header
		$this->_writeArchiveHeader();
	}

	/**
	 * Updates the Standard Header with current information
	 *
	 * @return  void
	 */
	public function finalize()
	{
		if (is_resource($this->fp))
		{
			$this->fclose($this->fp);
		}

		if (is_resource($this->cdfp))
		{
			$this->fclose($this->cdfp);
		}

		$this->_closeAllFiles();

		// If Spanned JPA and there is no .jpa file, rename the last fragment to .jpa
		if ($this->useSplitArchive)
		{
			$extension = substr($this->_dataFileName, -4);

			if ($extension != '.jpa')
			{
				Factory::getLog()->debug('Renaming last JPA part to .JPA extension');

				$newName = $this->dataFileNameWithoutExtension . '.jpa';

				if (!@rename($this->_dataFileName, $newName))
				{
					throw new RuntimeException('Could not rename last JPA part to .JPA extension.');
				}

				$this->_dataFileName = $newName;
			}

			// Finally, point to the first part so that we can re-write the correct header information
			if ($this->totalParts > 1)
			{
				$this->_dataFileName = $this->dataFileNameWithoutExtension . '.j01';
			}
		}

		// Re-write the archive header
		$this->_writeArchiveHeader();
	}

	/**
	 * Returns a string with the extension (including the dot) of the files produced
	 * by this class.
	 *
	 * @return string
	 */
	public function getExtension()
	{
		return '.jpa';
	}

	/**
	 * Outputs a Standard Header at the top of the file
	 *
	 * @return  void
	 */
	protected function _writeArchiveHeader()
	{
		if (!is_null($this->fp))
		{
			$this->fclose($this->fp);
			$this->fp = null;
		}

		$this->fp = $this->fopen($this->_dataFileName, 'cb');

		if ($this->fp === false)
		{
			throw new ErrorException('Could not open ' . $this->_dataFileName . ' for writing. Check permissions and open_basedir restrictions.');
		}

		// Calculate total header size
		$headerSize = 19; // Standard Header

		if ($this->useSplitArchive)
		{
			// Spanned JPA header
			$headerSize += 8;
		}

		$this->fwrite($this->fp, $this->archiveSignature); // ID string (JPA)
		$this->fwrite($this->fp, pack('v', $headerSize)); // Header length; fixed to 19 bytes
		$this->fwrite($this->fp, pack('C', _JPA_MAJOR)); // Major version
		$this->fwrite($this->fp, pack('C', _JPA_MINOR)); // Minor version
		$this->fwrite($this->fp, pack('V', $this->totalFilesCount)); // File count
		$this->fwrite($this->fp, pack('V', $this->totalUncompressedSize)); // Size of files when extracted
		$this->fwrite($this->fp, pack('V', $this->totalCompressedSize)); // Size of files when stored

		// Do I need to add a split archive's header too?
		if ($this->useSplitArchive)
		{
			$this->fwrite($this->fp, $this->splitArchiveExtraHeader); // Signature
			$this->fwrite($this->fp, pack('v', 4)); // Extra field length
			$this->fwrite($this->fp, pack('v', $this->totalParts)); // Number of parts
		}

		$this->fclose($this->fp);

		@chmod($this->_dataFileName, 0644);
	}

	/**
	 * Extend the bootstrap code to add some define's used by the JPA format engine
	 *
	 * @codeCoverageIgnore
	 *
	 * @return  void
	 */
	protected function __bootstrap_code()
	{
		if (!defined('_AKEEBA_COMPRESSION_THRESHOLD'))
		{
			$config = Factory::getConfiguration();
			define("_AKEEBA_COMPRESSION_THRESHOLD", $config->get('engine.archiver.common.big_file_threshold')); // Don't compress files over this size

			/**
			 * Akeeba Backup and JPA Format version change chart:
			 * Akeeba Backup 3.0: JPA Format 1.1 is used
			 * Akeeba Backup 3.1: JPA Format 1.2 with file modification timestamp is used
			 */
			define('_JPA_MAJOR', 1); // JPA Format major version number
			define('_JPA_MINOR', 2); // JPA Format minor version number

		}
		parent::__bootstrap_code();
	}

	/**
	 * The most basic file transaction: add a single entry (file or directory) to
	 * the archive.
	 *
	 * @param   bool    $isVirtual         If true, the next parameter contains file data instead of a file name
	 * @param   string  $sourceNameOrData  Absolute file name to read data from or the file data itself is $isVirtual is
	 *                                     true
	 * @param   string  $targetName        The (relative) file name under which to store the file in the archive
	 *
	 * @return boolean True on success, false otherwise
	 *
	 * @since  1.2.1
	 */
	protected function _addFile($isVirtual, &$sourceNameOrData, $targetName)
	{
		// Get references to engine objects we're going to be using
		$configuration = Factory::getConfiguration();

		// Is this a virtual file?
		$isVirtual = (bool) $isVirtual;

		// Open data file for output
		$this->openArchiveForOutput();

		// Should I continue backing up a file from the previous step?
		$continueProcessingFile = $configuration->get('volatile.engine.archiver.processingfile', false);

		// Initialize with the default values. Why are *these* values default? If we are continuing file packing, by
		// definition we have an uncompressed, non-virtual file. Hence the default values.
		$isDir             = false;
		$isSymlink         = false;
		$compressionMethod = 0;
		$zdata             = null;
		// If we are continuing file packing we have an uncompressed, non-virtual file.
		$isVirtual = $continueProcessingFile ? false : $isVirtual;
		$resume    = $continueProcessingFile ? 0 : null;

		if (!$continueProcessingFile)
		{
			// Log the file being added
			$messageSource = $isVirtual ? '(virtual data)' : "(source: $sourceNameOrData)";
			Factory::getLog()->debug("-- Adding $targetName to archive $messageSource");

			// Write a file header
			$this->writeFileHeader($sourceNameOrData, $targetName, $isVirtual, $isSymlink, $isDir, $compressionMethod, $zdata, $unc_len);
		}
		else
		{
			$sourceNameOrData = $configuration->get('volatile.engine.archiver.sourceNameOrData', '');
			$unc_len          = $configuration->get('volatile.engine.archiver.unc_len', 0);
			$resume           = $configuration->get('volatile.engine.archiver.resume', 0);

			// Log the file we continue packing
			Factory::getLog()->debug("-- Resuming adding file $sourceNameOrData to archive from position $resume (total size $unc_len)");
		}

		/* "File data" segment. */
		if ($compressionMethod == 1)
		{
			// Compressed data. Put into the archive.
			$this->putRawDataIntoArchive($zdata);
		}
		elseif ($isVirtual)
		{
			// Virtual data. Put into the archive.
			$this->putRawDataIntoArchive($sourceNameOrData);
		}
		elseif ($isSymlink)
		{
			// Symlink. Just put the link target into the archive.
			$this->fwrite($this->fp, @readlink($sourceNameOrData));
		}
		elseif ((!$isDir) && (!$isSymlink))
		{
			// Uncompressed file.
			if ($this->putUncompressedFileIntoArchive($sourceNameOrData, $unc_len, $resume) === true)
			{
				// If it returns true we are doing a step break to resume packing in the next step. So we need to return
				// true here to avoid running the final bit of code which uncaches the file resume data.
				return true;
			}
		}

		// Factory::getLog()->debug("DEBUG -- Added $targetName to archive");

		// Uncache data
		$configuration->set('volatile.engine.archiver.sourceNameOrData', null);
		$configuration->set('volatile.engine.archiver.unc_len', null);
		$configuration->set('volatile.engine.archiver.resume', null);
		$configuration->set('volatile.engine.archiver.processingfile', false);

		// ... and return TRUE = success
		return true;
	}

	/**
	 * Write the file header to the backup archive.
	 *
	 * Only the first three parameters are input. All other are ignored for input and are overwritten.
	 *
	 * @param   string  $sourceNameOrData   The path to the file being compressed, or the raw file data for virtual files
	 * @param   string  $targetName         The target path to be stored inside the archive
	 * @param   bool    $isVirtual          Is this a virtual file?
	 * @param   bool    $isSymlink          Is this a symlink?
	 * @param   bool    $isDir              Is this a directory?
	 * @param   int     $compressionMethod  The compression method chosen for this file
	 * @param   string  $zdata              If we have compression method other than 0 this holds the compressed data.
	 *                                      We return that from this method to avoid having to compress the same data
	 *                                      twice (once to write the compressed data length in the header and once to
	 *                                      write the compressed data to the archive).
	 * @param   int     $unc_len            The uncompressed size of the file / source data
	 *
	 * @return  void
	 */
	protected function writeFileHeader(&$sourceNameOrData, &$targetName, &$isVirtual, &$isSymlink, &$isDir, &$compressionMethod, &$zdata, &$unc_len)
	{
		static $memLimit = null;

		if (is_null($memLimit))
		{
			$memLimit = $this->getMemoryLimit();
		}

		$configuration = Factory::getConfiguration();

		// Uncache data -- WHY DO THAT?!
		/**
		 * $configuration->set('volatile.engine.archiver.sourceNameOrData', null);
		 * $configuration->set('volatile.engine.archiver.unc_len', null);
		 * $configuration->set('volatile.engine.archiver.resume', null);
		 * $configuration->set('volatile.engine.archiver.processingfile',false);
		 * /**/

		// See if it's a directory
		$isDir = $isVirtual ? false : is_dir($sourceNameOrData);

		// See if it's a symlink (w/out dereference)
		$isSymlink = false;

		if ($this->storeSymlinkTarget && !$isVirtual)
		{
			$isSymlink = is_link($sourceNameOrData);
		}

		// Get real size before compression
		[$fileSize, $fileModTime] =
			$this->getFileSizeAndModificationTime($sourceNameOrData, $isVirtual, $isSymlink, $isDir);

		// Decide if we will compress
		$compressionMethod = $this->getCompressionMethod($fileSize, $memLimit, $isDir, $isSymlink);

		$storedName = $targetName;

		/* "Entity Description Block" segment. */
		$unc_len    = $fileSize; // File size
		$storedName .= ($isDir) ? "/" : "";

		/**
		 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		 * !!!! WARNING!!! DO NOT MOVE THIS BLOCK OF CODE AFTER THE testIfFileExists OR getZData!!!!       !!!!
		 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		 *
		 * PHP 5.6.3 IS BROKEN. Possibly the same applies for all old versions of PHP. If you try to get the file
		 * permissions after reading its contents PHP segfaults.
		 */
		// Get file permissions
		$perms = 0644;

		if (!$isVirtual)
		{
			$perms = @fileperms($sourceNameOrData);
		}

		// Test for non-existing or unreadable files
		$this->testIfFileExists($sourceNameOrData, $isVirtual, $isDir, $isSymlink);

		// Default compressed (archived) length = uncompressed length – valid unless we can actually compress the data.
		$c_len = $unc_len;

		if ($compressionMethod == 1)
		{
			$this->getZData($sourceNameOrData, $isVirtual, $compressionMethod, $zdata, $unc_len, $c_len);
		}

		$this->totalCompressedSize   += $c_len; // Update global data
		$this->totalUncompressedSize += $fileSize; // Update global data
		$this->totalFilesCount++;

		// Calculate Entity Description Block length
		$blockLength = 21 + akstrlen($storedName);

		// If we need to store the file mod date
		if ($fileModTime > 0)
		{
			$blockLength += 8;
		}

		// Get file type
		$fileType = 1;

		if ($isSymlink)
		{
			$fileType = 2;
		}
		elseif ($isDir)
		{
			$fileType = 0;
		}

		// If it's a split JPA file, we've got to make sure that the header can fit in the part
		if ($this->useSplitArchive)
		{
			// Compare to free part space
			$free_space = $this->getPartFreeSize();

			if ($free_space <= $blockLength)
			{
				// Not enough space on current part, create new part
				$this->createAndOpenNewPart();
			}
		}

		$this->fwrite($this->fp, $this->fileHeaderSignature); // Entity Description Block header
		$this->fwrite($this->fp, pack('v', $blockLength)); // Entity Description Block header length
		$this->fwrite($this->fp, pack('v', akstrlen($storedName))); // Length of entity path
		$this->fwrite($this->fp, $storedName); // Entity path
		$this->fwrite($this->fp, pack('C', $fileType)); // Entity type
		$this->fwrite($this->fp, pack('C', $compressionMethod)); // Compression method
		$this->fwrite($this->fp, pack('V', $c_len)); // Compressed size
		$this->fwrite($this->fp, pack('V', $unc_len)); // Uncompressed size
		$this->fwrite($this->fp, pack('V', $perms)); // Entity permissions

		// Timestamp Extra Field, only for files
		if ($fileModTime > 0)
		{
			$this->fwrite($this->fp, "\x00\x01"); // Extra Field Identifier
			$this->fwrite($this->fp, pack('v', 8)); // Extra Field Length
			$this->fwrite($this->fp, pack('V', $fileModTime)); // Timestamp
		}

		// Cache useful information about the file
		if (!$isDir && !$isSymlink && !$isVirtual)
		{
			$configuration->set('volatile.engine.archiver.unc_len', $unc_len);
			$configuration->set('volatile.engine.archiver.sourceNameOrData', $sourceNameOrData);
		}
	}

	/**
	 * Creates a new part for the spanned archive
	 *
	 * @param   bool  $finalPart  Is this the final archive part?
	 *
	 * @return  bool  True on success
	 */
	protected function createNewPartFile($finalPart = false)
	{
		// Close any open file pointers
		if (!is_resource($this->fp))
		{
			$this->fclose($this->fp);
		}

		if (is_resource($this->cdfp))
		{
			$this->fclose($this->cdfp);
		}

		// Remove the just finished part from the list of resumable offsets
		$this->removeFromOffsetsList($this->_dataFileName);

		// Set the file pointers to null
		$this->fp   = null;
		$this->cdfp = null;

		// Push the previous part if we have to post-process it immediately
		$configuration = Factory::getConfiguration();

		if ($configuration->get('engine.postproc.common.after_part', 0))
		{
			// The first part needs its header overwritten during archive
			// finalization. Skip it from immediate processing.
			if ($this->currentPartNumber != 1)
			{
				$this->finishedPart[] = $this->_dataFileName;
			}
		}

		$this->totalParts++;
		$this->currentPartNumber = $this->totalParts;

		if ($finalPart)
		{
			$this->_dataFileName = $this->dataFileNameWithoutExtension . '.jpa';
		}
		else
		{
			$this->_dataFileName = $this->dataFileNameWithoutExtension . '.j' . sprintf('%02d', $this->currentPartNumber);
		}

		Factory::getLog()->info('Creating new JPA part #' . $this->currentPartNumber . ', file ' . $this->_dataFileName);
		$statistics = Factory::getStatistics();
		$statistics->updateMultipart($this->totalParts);

		// Try to remove any existing file
		@unlink($this->_dataFileName);

		// Touch the new file
		$result = @touch($this->_dataFileName);

		chmod($this->_dataFileName, 0666);

		// Try to write 6 bytes to it
		if ($result)
		{
			$result = @file_put_contents($this->_dataFileName, 'AKEEBA') == 6;
		}

		if ($result)
		{
			@unlink($this->_dataFileName);

			$result = @touch($this->_dataFileName);
			@chmod($this->_dataFileName, 0666);
		}

		return $result;
	}
}
