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
use Akeeba\Engine\Util\CRC32;
use RuntimeException;

class Zip extends BaseArchiver
{
	/** @var string Beginning of central directory record. */
	private $centralDirectoryRecordStartSignature = "\x50\x4b\x01\x02";

	/** @var string End of central directory record. */
	private $centralDirectoryRecordEndSignature = "\x50\x4b\x05\x06";

	/** @var string Beginning of file contents. */
	private $fileHeaderSignature = "\x50\x4b\x03\x04";

	/** @var string The name of the temporary file holding the ZIP's Central Directory */
	private $centralDirectoryFilename;

	/** @var integer The total number of files and directories stored in the ZIP archive */
	private $totalFilesCount;

	/** @var integer The total size of data in the archive. Note: On 32-bit versions of PHP, this will overflow for archives over 2Gb! */
	private $totalCompressedSize = 0;

	/** @var integer The chunk size for CRC32 calculations */
	private $AkeebaPackerZIP_CHUNK_SIZE;

	/** @var int Current part file number */
	private $currentPartNumber = 1;

	/** @var int Total number of part files */
	private $totalParts = 1;

	/** @var CRC32 The CRC32 calculations object */
	private $crcCalculator = null;

	/**
	 * Class constructor - initializes internal operating parameters
	 *
	 * @return  void
	 */
	public function __construct()
	{
		Factory::getLog()->debug(__CLASS__ . " :: New instance");

		// Find the optimal chunk size for ZIP archive processing
		$this->findOptimalChunkSize();

		Factory::getLog()->debug("Chunk size for CRC is now " . $this->AkeebaPackerZIP_CHUNK_SIZE . " bytes");

		// Should I use Symlink Target Storage?
		$this->enableSymlinkTargetStorage();

		parent::__construct();
	}

	/**
	 * Initialises the archiver class, creating the archive from an existent
	 * installer's JPA archive.
	 *
	 * @param   string  $sourceJPAPath      Absolute path to an installer's JPA archive
	 * @param   string  $targetArchivePath  Absolute path to the generated archive
	 * @param   array   $options            A named key array of options (optional). This is currently not supported
	 *
	 * @return void
	 */
	public function initialize($targetArchivePath, $options = [])
	{
		Factory::getLog()->debug(__CLASS__ . " :: initialize - archive $targetArchivePath");

		// Get names of temporary files
		$this->_dataFileName = $targetArchivePath;

		// Should we enable split archive feature?
		$this->enableSplitArchives();

		// Create the Central Directory temporary file
		$this->createCentralDirectoryTempFile();

		// Try to kill the archive if it exists
		$this->createNewBackupArchive();

		// On split archives, include the "Split ZIP" header, for PKZIP 2.50+ compatibility
		if ($this->useSplitArchive)
		{
			$this->openArchiveForOutput();
			$this->fwrite($this->fp, "\x50\x4b\x07\x08");
		}
	}

	public function finalize()
	{
		$this->finalizeZIPFile();
	}

	/**
	 * Glues the Central Directory of the ZIP file to the archive and takes care about the differences between single
	 * and multipart archives.
	 *
	 * Official ZIP file format: http://www.pkware.com/appnote.txt
	 *
	 * @return  void
	 */
	public function finalizeZIPFile()
	{
		// 1. Get size of central directory
		clearstatcache();
		$cdOffset                  = @filesize($this->_dataFileName);
		$this->totalCompressedSize += $cdOffset;
		$cdSize                    = @filesize($this->centralDirectoryFilename);

		// 2. Append Central Directory to data file and remove the CD temp file afterwards
		if (!is_null($this->fp))
		{
			$this->fclose($this->fp);
		}

		if (!is_null($this->cdfp))
		{
			$this->fclose($this->cdfp);
		}

		$this->openArchiveForOutput(true);

		/**
		 * Do not remove the fcloseByName line! This is required for post-processing multipart archives when for any
		 * reason $this->filePointers[$this->centralDirectoryFilename] contains null instead of boolean false. In this
		 * case the while loop would be stuck forever and the backup would fail. This HAS happened and I have been able
		 * to reproduce it but I did not have enough time to identify the real root cause. This workaround, however,
		 * works.
		 */
		$this->fcloseByName($this->centralDirectoryFilename);
		$this->cdfp = $this->fopen($this->centralDirectoryFilename, "rb");

		if ($this->cdfp === false)
		{
			// Already glued, return
			$this->fclose($this->fp);
			$this->fp   = null;
			$this->cdfp = null;

			return;
		}

		// Comment length (I need it before I start gluing the archive)
		$comment_length = akstrlen($this->_comment);

		// Special consideration for split ZIP files
		if ($this->useSplitArchive)
		{
			// Calculate size of Central Directory + EOCD records
			$total_cd_eocd_size = $cdSize + 22 + $comment_length;

			// Free space on the part
			$free_space = $this->getPartFreeSize();

			if (($free_space < $total_cd_eocd_size) && ($total_cd_eocd_size > 65536))
			{
				// Not enough space on archive for CD + EOCD, will go on separate part
				$this->createAndOpenNewPart(true);
			}
		}

		/**
		 * Write the CD record
		 *
		 * Note about is_resource: in some circumstances where multipart ZIP files are generated, the $this->cdfp will
		 * contain a null value. This seems to happen when $this->fopen returns null, i.e. $this->filePointers has a
		 * null value instead of a file pointer (resource). Why this happens is unclear but the workaround is to remove
		 * the null value from $this->filePointers and retry $this->fopen. Normally this should not be required since we
		 * already to the fcloseByName/fopen dance above. This if-block is our last hope to catch a potential issue
		 * which would either make the while loop go infinite (not anymore, I've patched it) or the Central Directory
		 * not get written to the archive, which results in a broken archive.
		 */
		if (!is_resource($this->cdfp))
		{
			$this->fcloseByName($this->centralDirectoryFilename);
			$this->cdfp = $this->fopen($this->centralDirectoryFilename, "rb");

			// We tried reopening the central directory file and failed again. Time to report a fatal error.
			if (!$this->cdfp)
			{
				throw new RuntimeException("Cannot open central directory temporary file {$this->centralDirectoryFilename} for reading.");
			}
		}

		while (!feof($this->cdfp) && is_resource($this->cdfp))
		{
			/**
			 * Why not split the Central Directory between parts?
			 *
			 * APPNOTE.TXT §8.5.2 "The central directory may span segment boundaries, but no single record in the
			 * central directory should be split across segments."
			 *
			 * This would require parsing the CD temp file to prevent any CD record from spanning across two parts.
			 * But how many bytes is each CD record? It's about 100 bytes per file which gives us about 10,400 files
			 * per MB. Even a 2MB part size holds more than 20,000 file records. A typical 10Mb part size holds more
			 * files than the largest backup I've ever seen. Therefore there is no need to waste computational power
			 * to see if we need to span the Central Directory between parts.
			 */
			$chunk = fread($this->cdfp, _AKEEBA_DIRECTORY_READ_CHUNK);
			$this->fwrite($this->fp, $chunk);
		}

		unset($chunk);

		// Delete the temporary CD file
		$this->fclose($this->cdfp);
		$this->cdfp = null;
		Factory::getTempFiles()->unregisterAndDeleteTempFile($this->centralDirectoryFilename);

		// 3. Write the rest of headers to the end of the ZIP file
		$this->fwrite($this->fp, $this->centralDirectoryRecordEndSignature);

		if ($this->useSplitArchive)
		{
			// Split ZIP files, enter relevant disk number information
			$this->fwrite($this->fp, pack('v', $this->totalParts - 1)); /* Number of this disk. */
			$this->fwrite($this->fp, pack('v', $this->totalParts - 1)); /* Disk with central directory start. */
		}
		else
		{
			// Non-split ZIP files, the disk number MUST be 0
			$this->fwrite($this->fp, pack('V', 0));
		}

		$this->fwrite($this->fp, pack('v', $this->totalFilesCount)); /* Total # of entries "on this disk". */
		$this->fwrite($this->fp, pack('v', $this->totalFilesCount)); /* Total # of entries overall. */
		$this->fwrite($this->fp, pack('V', $cdSize)); /* Size of central directory. */
		$this->fwrite($this->fp, pack('V', $cdOffset)); /* Offset to start of central dir. */

		// 2.0.b2 -- Write a ZIP file comment
		$this->fwrite($this->fp, pack('v', $comment_length)); /* ZIP file comment length. */
		$this->fwrite($this->fp, $this->_comment);
		$this->fclose($this->fp);

		// If Split ZIP and there is no .zip file, rename the last fragment to .ZIP
		if ($this->useSplitArchive)
		{
			$extension = substr($this->_dataFileName, -3);

			if ($extension != '.zip')
			{
				Factory::getLog()->debug('Renaming last ZIP part to .ZIP extension');

				$newName = $this->dataFileNameWithoutExtension . '.zip';

				if (!@rename($this->_dataFileName, $newName))
				{
					throw new RuntimeException('Could not rename last ZIP part to .ZIP extension.');
				}

				$this->_dataFileName = $newName;
			}

			// If Split ZIP and only one fragment, change the signature
			if ($this->totalParts == 1)
			{
				$this->fp = $this->fopen($this->_dataFileName, 'r+b');
				$this->fwrite($this->fp, "\x50\x4b\x30\x30");
			}
		}

		@chmod($this->_dataFileName, 0644);
	}

	/**
	 * Returns a string with the extension (including the dot) of the files produced
	 * by this class.
	 *
	 * @return string
	 */
	public function getExtension()
	{
		return '.zip';
	}

	/**
	 * Extend the bootstrap code to add some define's used by the ZIP format engine
	 *
	 * @return  void
	 */
	protected function __bootstrap_code()
	{
		if (!defined('_AKEEBA_COMPRESSION_THRESHOLD'))
		{
			$config = Factory::getConfiguration();
			define("_AKEEBA_COMPRESSION_THRESHOLD", $config->get('engine.archiver.common.big_file_threshold')); // Don't compress files over this size
			define("_AKEEBA_DIRECTORY_READ_CHUNK", $config->get('engine.archiver.zip.cd_glue_chunk_size')); // How much data to read at once when finalizing ZIP archives
		}

		$this->crcCalculator = Factory::getCRC32Calculator();

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
	 * @return bool True on success, false otherwise
	 */
	protected function _addFile($isVirtual, &$sourceNameOrData, $targetName)
	{
		$configuration = Factory::getConfiguration();

		// Note down the starting disk number for Split ZIP archives
		$starting_disk_number_for_this_file = 0;

		if ($this->useSplitArchive)
		{
			$starting_disk_number_for_this_file = $this->currentPartNumber - 1;
		}

		// Open data file for output
		$this->openArchiveForOutput();

		// Should I continue backing up a file from the previous step?
		$continueProcessingFile = $configuration->get('volatile.engine.archiver.processingfile', false);

		// Initialize with the default values. Why are *these* values default? If we are continuing file packing, by
		// definition we have an uncompressed, non-virtual file. Hence the default values.
		$isDir             = false;
		$isSymlink         = false;
		$compressionMethod = 1;
		$zdata             = null;
		// If we are continuing file packing we have an uncompressed, non-virtual file.
		$isVirtual = $continueProcessingFile ? false : $isVirtual;
		$resume    = $continueProcessingFile ? 0 : null;

		if (!$continueProcessingFile)
		{
			// Log the file being added
			$messageSource = $isVirtual ? '(virtual data)' : "(source: $sourceNameOrData)";
			Factory::getLog()->debug("-- Adding $targetName to archive $messageSource");

			$this->writeFileHeader($sourceNameOrData, $targetName, $isVirtual, $isSymlink, $isDir,
				$compressionMethod, $zdata, $unc_len,
				$storedName, $crc, $c_len, $hexdtime, $old_offset);
		}
		else
		{
			// Since we are continuing archiving, it's an uncompressed regular file. Set up the variables.
			$sourceNameOrData = $configuration->get('volatile.engine.archiver.sourceNameOrData', '');
			$resume           = $configuration->get('volatile.engine.archiver.resume', 0);
			$unc_len          = $configuration->get('volatile.engine.archiver.unc_len');
			$storedName       = $configuration->get('volatile.engine.archiver.storedName');
			$crc              = $configuration->get('volatile.engine.archiver.crc');
			$c_len            = $configuration->get('volatile.engine.archiver.c_len');
			$hexdtime         = $configuration->get('volatile.engine.archiver.hexdtime');
			$old_offset       = $configuration->get('volatile.engine.archiver.old_offset');

			// Log the file we continue packing
			Factory::getLog()->debug("-- Resuming adding file $sourceNameOrData to archive from position $resume (total size $unc_len)");
		}

		/* "File data" segment. */
		if ($compressionMethod == 8)
		{
			$this->putRawDataIntoArchive($zdata);
		}
		elseif ($isVirtual)
		{
			// Virtual data. Put into the archive.
			$this->putRawDataIntoArchive($sourceNameOrData);
		}
		elseif ($isSymlink)
		{
			$this->fwrite($this->fp, @readlink($sourceNameOrData));
		}
		elseif ((!$isDir) && (!$isSymlink))
		{
			// Uncompressed file.
			if ($this->putUncompressedFileIntoArchive($sourceNameOrData, $unc_len, $resume) === true)
			{
				// If it returns true we are doing a step break to resume packing in the next step. So we need to return
				// true here to avoid running the final bit of code which writes the central directory record and
				// uncaches the file resume data.
				return true;
			}
		}

		// Open the central directory file for append
		if (is_null($this->cdfp))
		{
			$this->cdfp = @$this->fopen($this->centralDirectoryFilename, "ab");
		}

		if ($this->cdfp === false)
		{
			throw new ErrorException("Could not open Central Directory temporary file for append!");
		}

		$this->fwrite($this->cdfp, $this->centralDirectoryRecordStartSignature);

		if (!$isSymlink)
		{
			$this->fwrite($this->cdfp, "\x14\x00"); /* Version made by (always set to 2.0). */
			$this->fwrite($this->cdfp, "\x14\x00"); /* Version needed to extract */
			$this->fwrite($this->cdfp, pack('v', 2048)); /* General purpose bit flag */
			$this->fwrite($this->cdfp, ($compressionMethod == 8) ? "\x08\x00" : "\x00\x00"); /* Compression method. */
		}
		else
		{
			// Symlinks get special treatment
			$this->fwrite($this->cdfp, "\x14\x03"); /* Version made by (version 2.0 with UNIX extensions). */
			$this->fwrite($this->cdfp, "\x0a\x03"); /* Version needed to extract */
			$this->fwrite($this->cdfp, pack('v', 2048)); /* General purpose bit flag */
			$this->fwrite($this->cdfp, "\x00\x00"); /* Compression method. */
		}

		$this->fwrite($this->cdfp, $hexdtime); /* Last mod time/date. */
		$this->fwrite($this->cdfp, pack('V', $crc)); /* CRC 32 information. */
		$this->fwrite($this->cdfp, pack('V', $c_len)); /* Compressed filesize. */

		if ($compressionMethod == 0)
		{
			// When we are not compressing, $unc_len is being reduced to 0 while backing up.
			// With this trick, we always store the correct length, as in this case the compressed
			// and uncompressed length is always the same.
			$this->fwrite($this->cdfp, pack('V', $c_len)); /* Uncompressed filesize. */
		}
		else
		{
			// When compressing, the uncompressed length differs from compressed length
			// and this line writes the correct value.
			$this->fwrite($this->cdfp, pack('V', $unc_len)); /* Uncompressed filesize. */
		}

		$fn_length = akstrlen($storedName);
		$this->fwrite($this->cdfp, pack('v', $fn_length)); /* Length of filename. */
		$this->fwrite($this->cdfp, pack('v', 0)); /* Extra field length. */
		$this->fwrite($this->cdfp, pack('v', 0)); /* File comment length. */
		$this->fwrite($this->cdfp, pack('v', $starting_disk_number_for_this_file)); /* Disk number start. */
		$this->fwrite($this->cdfp, pack('v', 0)); /* Internal file attributes. */

		/* External file attributes */
		if (!$isSymlink)
		{
			// Archive bit set
			$this->fwrite($this->cdfp, pack('V', $isDir ? 0x41FF0010 : 0xFE49FFE0));
		}
		else
		{
			// For SymLinks we store UNIX file attributes
			$this->fwrite($this->cdfp, "\x20\x80\xFF\xA1");
		}

		$this->fwrite($this->cdfp, pack('V', $old_offset)); /* Relative offset of local header. */
		$this->fwrite($this->cdfp, $storedName); /* File name. */

		/* Optional extra field, file comment goes here. */

		// Finally, increase the file counter by one
		$this->totalFilesCount++;

		// Uncache data
		$configuration->set('volatile.engine.archiver.sourceNameOrData', null);
		$configuration->set('volatile.engine.archiver.unc_len', null);
		$configuration->set('volatile.engine.archiver.resume', null);
		$configuration->set('volatile.engine.archiver.hexdtime', null);
		$configuration->set('volatile.engine.archiver.crc', null);
		$configuration->set('volatile.engine.archiver.c_len', null);
		$configuration->set('volatile.engine.archiver.fn_length', null);
		$configuration->set('volatile.engine.archiver.old_offset', null);
		$configuration->set('volatile.engine.archiver.storedName', null);
		$configuration->set('volatile.engine.archiver.sourceNameOrData', null);

		$configuration->set('volatile.engine.archiver.processingfile', false);

		// ... and return TRUE = success
		return true;
	}

	/**
	 * Write the file header before putting the file data into the archive
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
	 * @param   string  $storedName         The file path stored in the archive
	 * @param   string  $crc                CRC-32 for the file
	 * @param   int     $c_len              Compressed data length
	 * @param   string  $hexdtime           ZIP's hexadecimal notation if the file's modification date
	 * @param   int     $old_offset         Offset of the file header in the part file
	 */
	protected function writeFileHeader(&$sourceNameOrData, $targetName, &$isVirtual, &$isSymlink, &$isDir,
	                                   &$compressionMethod, &$zdata, &$unc_len, &$storedName, &$crc, &$c_len,
	                                   &$hexdtime, &$old_offset)
	{
		static $memLimit = null;

		if (is_null($memLimit))
		{
			$memLimit = $this->getMemoryLimit();
		}

		$configuration = Factory::getConfiguration();

		// See if it's a directory
		$isDir = $isVirtual ? false : is_dir($sourceNameOrData);

		// See if it's a symlink (w/out dereference)
		$isSymlink = false;

		if ($this->storeSymlinkTarget && !$isVirtual)
		{
			$isSymlink = is_link($sourceNameOrData);
		}

		// Get real size before compression
		[$unc_len, $fileModTime] =
			$this->getFileSizeAndModificationTime($sourceNameOrData, $isVirtual, $isSymlink, $isDir);

		// Decide if we will compress
		$compressionMethod = $this->getCompressionMethod($unc_len, $memLimit, $isDir, $isSymlink);

		if ($isVirtual)
		{
			Factory::getLog()->debug('  Virtual add:' . $targetName . ' (' . $unc_len . ') - ' . $compressionMethod);
		}

		/* "Local file header" segment. */

		$crc = $this->getCRCForEntity($sourceNameOrData, $isVirtual, $isDir, $isSymlink);

		$storedName = $targetName;

		if (!$isSymlink && $isDir)
		{
			$storedName .= "/";
			$unc_len    = 0;
		}

		// Test for non-existing or unreadable files
		$this->testIfFileExists($sourceNameOrData, $isVirtual, $isDir, $isSymlink);

		// Default compressed (archived) length = uncompressed length – valid unless we can actually compress the data.
		$c_len = $unc_len;

		// If we have to compress, read the data in memory and compress it
		if ($compressionMethod == 8)
		{
			$this->getZData($sourceNameOrData, $isVirtual, $compressionMethod, $zdata, $unc_len, $c_len);

			// The method modifies $compressionMethod to 0 (uncompressed) or 1 (Deflate) but the ZIP format needs it
			// to be 0 (uncompressed) or 8 (Deflate). So I just multiply by 8.
			$compressionMethod *= 8;
		}

		// Get the hex time.
		$dtime = dechex($this->unix2DOSTime($fileModTime));

		if (akstrlen($dtime) < 8)
		{
			$dtime = "00000000";
		}

		$hexdtime = chr(hexdec($dtime[6] . $dtime[7])) .
			chr(hexdec($dtime[4] . $dtime[5])) .
			chr(hexdec($dtime[2] . $dtime[3])) .
			chr(hexdec($dtime[0] . $dtime[1]));

		// If it's a split ZIP file, we've got to make sure that the header can fit in the part
		if ($this->useSplitArchive)
		{
			// Get header size, taking into account any extra header necessary
			$header_size = 30 + akstrlen($storedName);

			// Compare to free part space
			$free_space = $this->getPartFreeSize();

			if ($free_space <= $header_size)
			{
				// Not enough space on current part, create new part
				$this->createAndOpenNewPart();
			}
		}

		$old_offset = @ftell($this->fp);

		if ($this->useSplitArchive && ($old_offset == 0))
		{
			// Because in split ZIPs we have the split ZIP marker in the first four bytes.
			@fseek($this->fp, 4);
			$old_offset = @ftell($this->fp);
		}

		// Get the file name length in bytes
		$fn_length = akstrlen($storedName);

		$this->fwrite($this->fp, $this->fileHeaderSignature); /* Begin creating the ZIP data. */

		/* Version needed to extract. */
		if (!$isSymlink)
		{
			$this->fwrite($this->fp, "\x14\x00");
		}
		else
		{
			$this->fwrite($this->fp, "\x0a\x03");
		}

		$this->fwrite($this->fp, pack('v', 2048)); /* General purpose bit flag. Bit 11 set = use UTF-8 encoding for filenames & comments */
		$this->fwrite($this->fp, ($compressionMethod == 8) ? "\x08\x00" : "\x00\x00"); /* Compression method. */
		$this->fwrite($this->fp, $hexdtime); /* Last modification time/date. */
		$this->fwrite($this->fp, pack('V', $crc)); /* CRC 32 information. */
		$this->fwrite($this->fp, pack('V', $c_len)); /* Compressed filesize. */
		$this->fwrite($this->fp, pack('V', $unc_len)); /* Uncompressed filesize. */
		$this->fwrite($this->fp, pack('v', $fn_length)); /* Length of filename. */
		$this->fwrite($this->fp, pack('v', 0)); /* Extra field length. */
		$this->fwrite($this->fp, $storedName); /* File name. */

		// Cache useful information about the file
		if (!$isDir && !$isSymlink && !$isVirtual)
		{
			$configuration->set('volatile.engine.archiver.unc_len', $unc_len);
			$configuration->set('volatile.engine.archiver.hexdtime', $hexdtime);
			$configuration->set('volatile.engine.archiver.crc', $crc);
			$configuration->set('volatile.engine.archiver.c_len', $c_len);
			$configuration->set('volatile.engine.archiver.fn_length', $fn_length);
			$configuration->set('volatile.engine.archiver.old_offset', $old_offset);
			$configuration->set('volatile.engine.archiver.storedName', $storedName);
			$configuration->set('volatile.engine.archiver.sourceNameOrData', $sourceNameOrData);
		}
	}

	/**
	 * Get the preferred compression method for a file
	 *
	 * @param   int   $fileSize   File size in bytes
	 * @param   int   $memLimit   Memory limit in bytes
	 * @param   bool  $isDir      Is it a directory?
	 * @param   bool  $isSymlink  Is it a symlink?
	 *
	 * @return  int  Compression method to use
	 */
	protected function getCompressionMethod($fileSize, $memLimit, $isDir, $isSymlink)
	{
		// ZIP uses 0 for uncompressed and 8 for GZip Deflate whereas the parent method returns 0 and 1 respectively
		return 8 * parent::getCompressionMethod($fileSize, $memLimit, $isDir, $isSymlink);
	}

	/**
	 * Calculate the CRC-32 checksum
	 *
	 * @param   string  $sourceNameOrData  The path to the file being compressed, or the raw file data for virtual files
	 * @param   bool    $isVirtual         Is this a virtual file?
	 * @param   bool    $isSymlink         Is this a symlink?
	 * @param   bool    $isDir             Is this a directory?
	 *
	 * @return  int  The CRC-32
	 */
	protected function getCRCForEntity(&$sourceNameOrData, &$isVirtual, &$isDir, &$isSymlink)
	{
		if (!$isSymlink && $isDir)
		{
			// Dummy CRC for dirs
			$crc = 0;

			return $crc;
		}

		if ($isSymlink)
		{
			$crc = \crc32(@readlink($sourceNameOrData));

			return $crc;
		}

		if ($isVirtual)
		{
			$crc = \crc32($sourceNameOrData);

			return $crc;
		}

		// This is supposed to be the fast way to calculate CRC32 of a (large) file.
		$crc = $this->crcCalculator->crc32_file($sourceNameOrData, $this->AkeebaPackerZIP_CHUNK_SIZE);

		// If the file was unreadable, $crc will be false, so we skip the file
		if ($crc === false)
		{
			throw new WarningException('Could not calculate CRC32 for ' . $sourceNameOrData . '. Looks like it is an unreadable file.');
		}

		return $crc;
	}

	/**
	 * Converts a UNIX timestamp to a 4-byte DOS date and time format
	 * (date in high 2-bytes, time in low 2-bytes allowing magnitude
	 * comparison).
	 *
	 * @param   integer  $unixtime  The current UNIX timestamp.
	 *
	 * @return integer  The current date in a 4-byte DOS format.
	 */
	protected function unix2DOSTime($unixtime = null)
	{
		$timearray = (is_null($unixtime)) ? getdate() : getdate($unixtime);

		if ($timearray['year'] < 1980)
		{
			$timearray['year']    = 1980;
			$timearray['mon']     = 1;
			$timearray['mday']    = 1;
			$timearray['hours']   = 0;
			$timearray['minutes'] = 0;
			$timearray['seconds'] = 0;
		}

		return (($timearray['year'] - 1980) << 25) |
			($timearray['mon'] << 21) |
			($timearray['mday'] << 16) |
			($timearray['hours'] << 11) |
			($timearray['minutes'] << 5) |
			($timearray['seconds'] >> 1);
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
		if (is_resource($this->fp))
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
			$this->finishedPart[] = $this->_dataFileName;
		}

		// Add the part's size to our rolling sum
		clearstatcache();
		$this->totalCompressedSize += filesize($this->_dataFileName);
		$this->totalParts++;
		$this->currentPartNumber = $this->totalParts;

		if ($finalPart)
		{
			$this->_dataFileName = $this->dataFileNameWithoutExtension . '.zip';
		}
		else
		{
			$this->_dataFileName = $this->dataFileNameWithoutExtension . '.z' . sprintf('%02d', $this->currentPartNumber);
		}

		Factory::getLog()->info('Creating new ZIP part #' . $this->currentPartNumber . ', file ' . $this->_dataFileName);

		// Inform the backup engine that we have changed the multipart number
		$statistics = Factory::getStatistics();
		$statistics->updateMultipart($this->totalParts);

		// Try to remove any existing file
		@unlink($this->_dataFileName);

		// Touch the new file
		$result = @touch($this->_dataFileName);

		@chmod($this->_dataFileName, 0666);

		return $result;
	}

	/**
	 * Find the optimal chunk size for CRC32 calculations and file processing
	 *
	 * @return  void
	 */
	private function findOptimalChunkSize()
	{
		$configuration = Factory::getConfiguration();

		// The user has entered their own preference
		if ($configuration->get('engine.archiver.common.chunk_size', 0) > 0)
		{
			$this->AkeebaPackerZIP_CHUNK_SIZE = AKEEBA_CHUNK;

			return;
		}

		// Get the PHP memory limit
		$memLimit = $this->getMemoryLimit();

		// Can't get a PHP memory limit? Use 2Mb chunks (fairly large, right?)
		if (is_null($memLimit))
		{
			$this->AkeebaPackerZIP_CHUNK_SIZE = 2097152;

			return;
		}

		if (!function_exists("memory_get_usage"))
		{
			// PHP can't report memory usage, use a conservative 512Kb
			$this->AkeebaPackerZIP_CHUNK_SIZE = 524288;

			return;
		}

		// PHP *can* report memory usage, see if there's enough available memory
		$availableRAM = $memLimit - memory_get_usage();

		if ($availableRAM > 0)
		{
			$this->AkeebaPackerZIP_CHUNK_SIZE = $availableRAM * 0.5;

			return;
		}

		// NEGATIVE AVAILABLE MEMORY?!! Some borked PHP implementations also return the size of the httpd footprint.
		if (($memLimit - 6291456) > 0)
		{
			$this->AkeebaPackerZIP_CHUNK_SIZE = $memLimit - 6291456;

			return;
		}

		// If all else fails, use 2Mb and cross your fingers
		$this->AkeebaPackerZIP_CHUNK_SIZE = 2097152;
	}

	/**
	 * Create a Central Directory temporary file
	 *
	 * @return  void
	 *
	 * @throws  ErrorException
	 */
	private function createCentralDirectoryTempFile()
	{
		$configuration                  = Factory::getConfiguration();
		$this->centralDirectoryFilename = tempnam($configuration->get('akeeba.basic.output_directory'), 'akzcd');
		$this->centralDirectoryFilename = basename($this->centralDirectoryFilename);
		$pos                            = strrpos($this->centralDirectoryFilename, '/');

		if ($pos !== false)
		{
			$this->centralDirectoryFilename = substr($this->centralDirectoryFilename, $pos + 1);
		}

		$pos = strrpos($this->centralDirectoryFilename, '\\');

		if ($pos !== false)
		{
			$this->centralDirectoryFilename = substr($this->centralDirectoryFilename, $pos + 1);
		}

		$this->centralDirectoryFilename = Factory::getTempFiles()->registerTempFile($this->centralDirectoryFilename);

		Factory::getLog()->debug(__CLASS__ . " :: CntDir Tempfile = " . $this->centralDirectoryFilename);

		// Create temporary file
		if (!@touch($this->centralDirectoryFilename))
		{
			throw new ErrorException("Could not open temporary file for ZIP archiver. Please check your temporary directory's permissions!");
		}

		@chmod($this->centralDirectoryFilename, 0666);
	}
}
