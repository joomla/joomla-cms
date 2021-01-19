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
use Akeeba\Engine\Platform;
use Akeeba\Engine\Util\FileSystem;
use Exception;
use RuntimeException;

/**
 * Abstract parent class of all archiver engines
 */
abstract class Base
{
	/** @var Filesystem Filesystem utilities object */
	protected $fsUtils = null;

	/** @var   string  The archive's comment. It's currently used ONLY in the ZIP file format */
	protected $_comment;

	/** @var   resource  JPA transformation source handle */
	private $_xform_fp;

	/**
	 * Public constructor
	 *
	 * @codeCoverageIgnore
	 *
	 * @return  void
	 */
	public function __construct()
	{
		$this->__bootstrap_code();
	}

	/**
	 * Wakeup (unserialization) function
	 *
	 * @codeCoverageIgnore
	 *
	 * @return  void
	 */
	public function __wakeup()
	{
		$this->__bootstrap_code();
	}

	/**
	 * Notifies the engine on the backup comment and converts it to plain text for
	 * inclusion in the archive file, if applicable.
	 *
	 * @param   string  $comment  The archive's comment
	 *
	 * @return  void
	 */
	public function setComment($comment)
	{
		// First, sanitize the comment in a text-only format
		$comment        = str_replace("\n", " ", $comment); // Replace newlines with spaces
		$comment        = str_replace("<br>", "\n", $comment); // Replace HTML4 <br> with single newlines
		$comment        = str_replace("<br/>", "\n", $comment); // Replace HTML4 <br> with single newlines
		$comment        = str_replace("<br />", "\n", $comment); // Replace HTML <br /> with single newlines
		$comment        = str_replace("</p>", "\n\n", $comment); // Replace paragraph endings with double newlines
		$comment        = str_replace("<b>", "*", $comment); // Replace bold with star notation
		$comment        = str_replace("</b>", "*", $comment); // Replace bold with star notation
		$comment        = str_replace("<i>", "_", $comment); // Replace italics with underline notation
		$comment        = str_replace("</i>", "_", $comment); // Replace italics with underline notation
		$this->_comment = strip_tags($comment, '');
	}

	/**
	 * Adds a single file in the archive
	 *
	 * @param   string  $file        The absolute path to the file to add
	 * @param   string  $removePath  Path to remove from $file
	 * @param   string  $addPath     Path to prepend to $file
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 */
	public final function addFile($file, $removePath = '', $addPath = '')
	{
		$storedName = $this->addRemovePaths($file, $removePath, $addPath);

		$this->addFileRenamed($file, $storedName);
	}

	/**
	 * Adds a file to the archive, given the stored name and its contents
	 *
	 * @param   string  $fileName        The base file name
	 * @param   string  $addPath         The relative path to prepend to file name
	 * @param   string  $virtualContent  The contents of the file to be archived
	 *
	 * @return  void
	 */
	public final function addFileVirtual($fileName, $addPath, &$virtualContent)
	{
		$storedName  = $this->addRemovePaths($fileName, '', $addPath);
		$mb_encoding = '8bit';

		if (function_exists('mb_internal_encoding'))
		{
			$mb_encoding = mb_internal_encoding();
			mb_internal_encoding('ISO-8859-1');
		}

		try
		{
			$this->_addFile(true, $virtualContent, $storedName);
		}
		catch (WarningException $e)
		{
			Factory::getLog()->warning($e->getMessage());
		}
		finally
		{
			if (function_exists('mb_internal_encoding'))
			{
				mb_internal_encoding($mb_encoding);
			}
		}
	}

	/**
	 * Adds a file to the archive, with a name that's different from the source
	 * filename
	 *
	 * @param   string  $sourceFile  Absolute path to the source file
	 * @param   string  $targetFile  Relative filename to store in archive
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 */
	public function addFileRenamed($sourceFile, $targetFile)
	{
		$mb_encoding = '8bit';

		if (function_exists('mb_internal_encoding'))
		{
			$mb_encoding = mb_internal_encoding();
			mb_internal_encoding('ISO-8859-1');
		}

		try
		{
			$this->_addFile(false, $sourceFile, $targetFile);
		}
		catch (WarningException $e)
		{
			Factory::getLog()->warning($e->getMessage());
		}
		finally
		{
			if (function_exists('mb_internal_encoding'))
			{
				mb_internal_encoding($mb_encoding);
			}
		}
	}

	/**
	 * Adds a list of files into the archive, removing $removePath from the
	 * file names and adding $addPath to them.
	 *
	 * @param   array   $fileList    A simple string array of filepaths to include
	 * @param   string  $removePath  Paths to remove from the filepaths
	 * @param   string  $addPath     Paths to add in front of the filepaths
	 *
	 * @return  void
	 *
	 * @throws Exception
	 */
	public final function addFileList(&$fileList, $removePath = '', $addPath = '')
	{
		if (!is_array($fileList))
		{
			Factory::getLog()->warning('addFileList called without a file list array');

			return;
		}

		foreach ($fileList as $file)
		{
			$this->addFile($file, $removePath, $addPath);
		}
	}

	/**
	 * Adds a file to the archive, given the stored name and its contents
	 *
	 * @param   string  $fileName        The base file name
	 * @param   string  $addPath         The relative path to prepend to file name
	 * @param   string  $virtualContent  The contents of the file to be archived
	 *
	 * @return  void
	 *
	 * @deprecated 7.0.0
	 */
	public final function addVirtualFile($fileName, $addPath, &$virtualContent)
	{
		Factory::getLog()->debug('DEPRECATED: addVirtualFile() has been renamed to addFileVirtual().');

		$this->addFileVirtual($fileName, $addPath, $virtualContent);
	}

	/**
	 * Initialises the archiver class, creating the archive from an existent
	 * installer's JPA archive. MUST BE OVERRIDEN BY CHILDREN CLASSES.
	 *
	 * @param   string  $targetArchivePath  Absolute path to the generated archive
	 * @param   array   $options            A named key array of options (optional)
	 *
	 * @return  void
	 */
	abstract public function initialize($targetArchivePath, $options = []);

	/**
	 * Makes whatever finalization is needed for the archive to be considered
	 * complete and useful (or, generally, clean up)
	 *
	 * @return  void
	 */
	abstract public function finalize();

	/**
	 * Transforms a JPA archive (containing an installer) to the native archive format
	 * of the class. It actually extracts the source JPA in memory and instructs the
	 * class to include each extracted file.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param   integer  $index   The index in the source JPA archive's list currently in use
	 * @param   integer  $offset  The source JPA archive's offset to use
	 *
	 * @return  array|bool  False if an error occurred, return array otherwise
	 */
	public function transformJPA($index, $offset)
	{
		static $totalSize = 0;

		$xform_source = null;

		// Do we have to open the file?
		if (!$this->_xform_fp)
		{
			// Get the source path
			$registry           = Factory::getConfiguration();
			$embedded_installer = $registry->get('akeeba.advanced.embedded_installer');

			// Fetch the name of the installer image
			$installerDescriptors = Factory::getEngineParamsProvider()->getInstallerList();
			$xform_source         = Platform::getInstance()->get_installer_images_path() .
				'/foobar.jpa'; // We need this as a "safe fallback"

			// Try to find a sane default if we are not given a valid embedded installer
			if (!array_key_exists($embedded_installer, $installerDescriptors))
			{
				$embedded_installer = 'angie';

				if (!array_key_exists($embedded_installer, $installerDescriptors))
				{
					$allInstallers = array_keys($installerDescriptors);

					foreach ($allInstallers as $anInstaller)
					{
						if ($anInstaller == 'none')
						{
							continue;
						}

						$embedded_installer = $anInstaller;
						break;
					}
				}
			}

			if (array_key_exists($embedded_installer, $installerDescriptors))
			{
				$packages  = $installerDescriptors[$embedded_installer]['package'];
				$langPacks = $installerDescriptors[$embedded_installer]['language'];

				if (empty($packages))
				{
					// No installer package specified. Pretend we are done!
					$retArray = [
						"filename" => '', // File name extracted
						"data"     => '', // File data
						"index"    => 0, // How many source JPA files I have
						"offset"   => 0, // Offset in JPA file
						"skip"     => false, // Skip this?
						"done"     => true, // Are we done yet?
						"filesize" => 0,
					];

					return $retArray;
				}

				$packages   = explode(',', $packages);
				$langPacks  = explode(',', $langPacks);
				$totalSize  = 0;
				$pathPrefix = Platform::getInstance()->get_installer_images_path() . '/';

				foreach ($packages as $package)
				{
					$filePath  = $pathPrefix . $package;
					$totalSize += (int) @filesize($filePath);
				}

				foreach ($langPacks as $langPack)
				{
					$filePath = $pathPrefix . $langPack;

					if (!is_file($filePath))
					{
						continue;
					}

					$packages[] = $langPack;
					$totalSize  += (int) @filesize($filePath);
				}

				if (count($packages) < $index)
				{
					throw new RuntimeException(__CLASS__ . ":: Installer package index $index not found for embedded installer $embedded_installer");
				}

				$package = $packages[$index];

				// A package is specified, use it!
				$xform_source = $pathPrefix . $package;
			}

			// 2.3: Try to use sane default if the indicated installer doesn't exist
			if (!is_null($xform_source) && !file_exists($xform_source) && (basename($xform_source) != 'angie.jpa'))
			{
				throw new RuntimeException(__CLASS__ . ":: Installer package $xform_source of embedded installer $embedded_installer not found. Please go to the configuration page, select an Embedded Installer, save the configuration and try backing up again.");
			}

			// Try opening the file
			if (!is_null($xform_source) && file_exists($xform_source))
			{
				$this->_xform_fp = @fopen($xform_source, 'r');

				if ($this->_xform_fp === false)
				{
					throw new RuntimeException(__CLASS__ . ":: Can't seed archive with installer package " . $xform_source);
				}
			}
			else
			{
				throw new RuntimeException(__CLASS__ . ":: Installer package " . $xform_source . " does not exist!");
			}
		}

		$headerDataLength = 0;

		if (!$offset)
		{
			// First run detected!
			Factory::getLog()->debug('Initializing with JPA package ' . $xform_source);

			// Skip over the header and check no problem exists
			$offset = $this->_xformReadHeader();

			if ($offset === false)
			{
				throw new RuntimeException('JPA package file was not read');
			}

			$headerDataLength = $offset;
		}

		$ret = $this->_xformExtract($offset);

		$ret['index'] = $index;

		if (is_array($ret))
		{
			$ret['chunkProcessed'] = $headerDataLength + $ret['offset'] - $offset;
			$offset                = $ret['offset'];

			if (!$ret['skip'] && !$ret['done'])
			{
				Factory::getLog()->debug('  Adding ' . $ret['filename'] . '; Next offset:' . $offset);

				$this->addFileVirtual($ret['filename'], '', $ret['data']);
			}
			elseif ($ret['done'])
			{
				$registry             = Factory::getConfiguration();
				$embedded_installer   = $registry->get('akeeba.advanced.embedded_installer');
				$installerDescriptors = Factory::getEngineParamsProvider()->getInstallerList();
				$packages             = $installerDescriptors[$embedded_installer]['package'];
				$packages             = explode(',', $packages);
				$pathPrefix           = Platform::getInstance()->get_installer_images_path() . '/';
				$langPacks            = $installerDescriptors[$embedded_installer]['language'];
				$langPacks            = explode(',', $langPacks);

				foreach ($langPacks as $langPack)
				{
					$filePath = $pathPrefix . $langPack;

					if (!is_file($filePath))
					{
						continue;
					}

					$packages[] = $langPack;
				}

				Factory::getLog()->debug('  Done with package ' . $packages[$index]);

				if (count($packages) > ($index + 1))
				{
					$ret['done']     = false;
					$ret['index']    = $index + 1;
					$ret['offset']   = 0;
					$this->_xform_fp = null;
				}
				else
				{
					Factory::getLog()->debug('  Done with installer seeding.');
				}
			}
			else
			{
				$reason = '  Skipping ' . $ret['filename'];
				Factory::getLog()->debug($reason);
			}
		}
		else
		{
			throw new RuntimeException('JPA extraction returned FALSE. The installer image is corrupt.');
		}

		if ($ret['done'])
		{
			// We are finished! Close the file
			fclose($this->_xform_fp);
			Factory::getLog()->debug('Initializing with JPA package has finished');
		}

		$ret['filesize'] = $totalSize;

		return $ret;
	}

	/**
	 * Returns a string with the extension (including the dot) of the files produced
	 * by this class.
	 *
	 * @return  string
	 */
	abstract public function getExtension();

	/**
	 * Common code which gets called on instance creation or wake-up (unserialization)
	 *
	 * @codeCoverageIgnore
	 *
	 * @return  void
	 */
	protected function __bootstrap_code()
	{
		$this->fsUtils = Factory::getFilesystemTools();
	}

	/**
	 * The most basic file transaction: add a single entry (file or directory) to
	 * the archive.
	 *
	 * @param   boolean  $isVirtual         If true, the next parameter contains file data instead of a file name
	 * @param   string   $sourceNameOrData  Absolute file name to read data from or the file data itself is $isVirtual
	 *                                      is true
	 * @param   string   $targetName        The (relative) file name under which to store the file in the archive
	 *
	 * @return  boolean  True on success, false otherwise. DEPRECATED: Use exceptions instead.
	 *
	 * @throws  WarningException  When there's a warning (the backup integrity is NOT compromised)
	 * @throws  ErrorException    When there's an error (the backup integrity is compromised – backup dead)
	 */
	abstract protected function _addFile($isVirtual, &$sourceNameOrData, $targetName);

	/**
	 * Removes the $p_remove_dir from $p_filename, while prepending it with $p_add_dir.
	 * Largely based on code from the pclZip library.
	 *
	 * @param   string  $p_filename    The absolute file name to treat
	 * @param   string  $p_remove_dir  The path to remove
	 * @param   string  $p_add_dir     The path to prefix the treated file name with
	 *
	 * @return  string  The treated file name
	 */
	private function addRemovePaths($p_filename, $p_remove_dir, $p_add_dir)
	{
		$p_filename   = $this->fsUtils->TranslateWinPath($p_filename);
		$p_remove_dir = ($p_remove_dir == '') ? '' :
			$this->fsUtils->TranslateWinPath($p_remove_dir); //should fix corrupt backups, fix by nicholas

		$v_stored_filename = $p_filename;

		if (!($p_remove_dir == ""))
		{
			if (substr($p_remove_dir, -1) != '/')
			{
				$p_remove_dir .= "/";
			}

			if ((substr($p_filename, 0, 2) == "./") || (substr($p_remove_dir, 0, 2) == "./"))
			{
				if ((substr($p_filename, 0, 2) == "./") && (substr($p_remove_dir, 0, 2) != "./"))
				{
					$p_remove_dir = "./" . $p_remove_dir;
				}

				if ((substr($p_filename, 0, 2) != "./") && (substr($p_remove_dir, 0, 2) == "./"))
				{
					$p_remove_dir = substr($p_remove_dir, 2);
				}
			}

			$v_compare = $this->_PathInclusion($p_remove_dir, $p_filename);

			if ($v_compare > 0)
			{
				if ($v_compare == 2)
				{
					$v_stored_filename = "";
				}
				else
				{
					$v_stored_filename =
						substr($p_filename, (function_exists('mb_strlen') ? mb_strlen($p_remove_dir, '8bit') :
							strlen($p_remove_dir)));
				}
			}
		}
		else
		{
			$v_stored_filename = $p_filename;
		}

		if (!($p_add_dir == ""))
		{
			if (substr($p_add_dir, -1) == "/")
			{
				$v_stored_filename = $p_add_dir . $v_stored_filename;
			}
			else
			{
				$v_stored_filename = $p_add_dir . "/" . $v_stored_filename;
			}
		}

		return $v_stored_filename;
	}

	/**
	 * This function indicates if the path $p_path is under the $p_dir tree. Or,
	 * said in an other way, if the file or sub-dir $p_path is inside the dir
	 * $p_dir.
	 * The function indicates also if the path is exactly the same as the dir.
	 * This function supports path with duplicated '/' like '//', but does not
	 * support '.' or '..' statements.
	 *
	 * Copied verbatim from pclZip library
	 *
	 * @codeCoverageIgnore
	 *
	 * @param   string  $p_dir   Source tree
	 * @param   string  $p_path  Check if this is part of $p_dir
	 *
	 * @return  integer   0 if $p_path is not inside directory $p_dir,
	 *                    1 if $p_path is inside directory $p_dir
	 *                    2 if $p_path is exactly the same as $p_dir
	 */
	private function _PathInclusion($p_dir, $p_path)
	{
		$v_result = 1;

		// ----- Explode dir and path by directory separator
		$v_list_dir       = explode("/", $p_dir);
		$v_list_dir_size  = count($v_list_dir);
		$v_list_path      = explode("/", $p_path);
		$v_list_path_size = count($v_list_path);

		// ----- Study directories paths
		$i = 0;
		$j = 0;

		while (($i < $v_list_dir_size) && ($j < $v_list_path_size) && ($v_result))
		{
			// ----- Look for empty dir (path reduction)
			if ($v_list_dir[$i] == '')
			{
				$i++;

				continue;
			}

			if ($v_list_path[$j] == '')
			{
				$j++;

				continue;
			}

			// ----- Compare the items
			if (($v_list_dir[$i] != $v_list_path[$j]) && ($v_list_dir[$i] != '') && ($v_list_path[$j] != ''))
			{
				$v_result = 0;
			}

			// ----- Next items
			$i++;
			$j++;
		}

		// ----- Look if everything seems to be the same
		if ($v_result)
		{
			// ----- Skip all the empty items
			while (($j < $v_list_path_size) && ($v_list_path[$j] == ''))
			{
				$j++;
			}

			while (($i < $v_list_dir_size) && ($v_list_dir[$i] == ''))
			{
				$i++;
			}

			if (($i >= $v_list_dir_size) && ($j >= $v_list_path_size))
			{
				// ----- There are exactly the same
				$v_result = 2;
			}
			else if ($i < $v_list_dir_size)
			{
				// ----- The path is shorter than the dir
				$v_result = 0;
			}
		}

		// ----- Return
		return $v_result;
	}

	/**
	 * Skips over the JPA header entry and returns the offset file data starts from
	 *
	 * @codeCoverageIgnore
	 *
	 * @return  boolean|integer  False on failure, offset otherwise
	 */
	private function _xformReadHeader()
	{
		// Fail for unreadable files
		if ($this->_xform_fp === false)
		{
			return false;
		}

		// Go to the beggining of the file
		rewind($this->_xform_fp);

		// Read the signature
		$sig = fread($this->_xform_fp, 3);

		// Not a JPA Archive?
		if ($sig != 'JPA')
		{
			return false;
		}

		// Read and parse header length
		$header_length_array = unpack('v', fread($this->_xform_fp, 2));
		$header_length       = $header_length_array[1];

		// Read and parse the known portion of header data (14 bytes)
		$bin_data    = fread($this->_xform_fp, 14);
		$header_data = unpack('Cmajor/Cminor/Vcount/Vuncsize/Vcsize', $bin_data);

		// Load any remaining header data (forward compatibility)
		$rest_length = $header_length - 19;

		if ($rest_length > 0)
		{
			$junk = fread($this->_xform_fp, $rest_length);
		}

		return ftell($this->_xform_fp);
	}

	/**
	 * Extracts a file from the JPA archive and returns an in-memory array containing it
	 * and its file data. The data returned is an array, consisting of the following keys:
	 * "filename" => relative file path stored in the archive
	 * "data"     => file data
	 * "offset"   => next offset to use
	 * "skip"     => if this is not a file, just skip it...
	 * "done"     => No more files left in archive
	 *
	 * @codeCoverageIgnore
	 *
	 * @param   integer  $offset  The absolute data offset from archive's header
	 *
	 * @return  array|bool  See description for more information
	 */
	private function &_xformExtract($offset)
	{
		$false = false; // Used to return false values in case an error occurs

		// Generate a return array
		$retArray = [
			"filename" => '', // File name extracted
			"data"     => '', // File data
			"offset"   => 0, // Offset in ZIP file
			"skip"     => false, // Skip this?
			"done"     => false // Are we done yet?
		];

		// If we can't open the file, return an error condition
		if ($this->_xform_fp === false)
		{
			return $false;
		}

		// Go to the offset specified
		if (!fseek($this->_xform_fp, $offset) == 0)
		{
			return $false;
		}

		// Get and decode Entity Description Block
		$signature = fread($this->_xform_fp, 3);

		// Check signature
		if ($signature == 'JPF')
		{
			// This a JPA Entity Block. Process the header.

			// Read length of EDB and of the Entity Path Data
			$length_array = unpack('vblocksize/vpathsize', fread($this->_xform_fp, 4));
			// Read the path data
			$file = fread($this->_xform_fp, $length_array['pathsize']);
			// Read and parse the known data portion
			$bin_data    = fread($this->_xform_fp, 14);
			$header_data = unpack('Ctype/Ccompression/Vcompsize/Vuncompsize/Vperms', $bin_data);
			// Read any unknwon data
			$restBytes = $length_array['blocksize'] - (21 + $length_array['pathsize']);

			if ($restBytes > 0)
			{
				$junk = fread($this->_xform_fp, $restBytes);
			}

			$compressionType = $header_data['compression'];

			// Populate the return array
			$retArray['filename'] = $file;
			$retArray['skip']     = ($header_data['compsize'] == 0); // Skip over directories

			switch ($header_data['type'])
			{
				case 0:
					// directory
					break;

				case 1:
					// file
					switch ($compressionType)
					{
						case 0: // No compression
							if ($header_data['compsize'] > 0) // 0 byte files do not have data to be read
							{
								$retArray['data'] = fread($this->_xform_fp, $header_data['compsize']);
							}
							break;

						case 1: // GZip compression
							$zipData          = fread($this->_xform_fp, $header_data['compsize']);
							$retArray['data'] = gzinflate($zipData);
							break;

						case 2: // BZip2 compression
							$zipData          = fread($this->_xform_fp, $header_data['compsize']);
							$retArray['data'] = bzdecompress($zipData);
							break;
					}
					break;
			}
		}
		else
		{
			// This is not a file header. This means we are done.
			$retArray['done'] = true;
		}

		$retArray['offset'] = ftell($this->_xform_fp);

		return $retArray;
	}
}
