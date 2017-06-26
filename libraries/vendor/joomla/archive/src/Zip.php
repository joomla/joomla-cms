<?php
/**
 * Part of the Joomla Framework Archive Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Archive;

use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;
use Joomla\Filesystem\Folder;

/**
 * ZIP format adapter for the Archive package
 *
 * The ZIP compression code is partially based on code from:
 * Eric Mueller <eric@themepark.com>
 * http://www.zend.com/codex.php?id=535&single=1
 *
 * Deins125 <webmaster@atlant.ru>
 * http://www.zend.com/codex.php?id=470&single=1
 *
 * The ZIP compression date code is partially based on code from
 * Peter Listiak <mlady@users.sourceforge.net>
 *
 * This class is inspired from and draws heavily in code and concept from the Compress package of
 * The Horde Project <http://www.horde.org>
 *
 * @contributor  Chuck Hagenbuch <chuck@horde.org>
 * @contributor  Michael Slusarz <slusarz@horde.org>
 * @contributor  Michael Cochrane <mike@graftonhall.co.nz>
 *
 * @since  1.0
 */
class Zip implements ExtractableInterface
{
	/**
	 * ZIP compression methods.
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $methods = array(
		0x0 => 'None',
		0x1 => 'Shrunk',
		0x2 => 'Super Fast',
		0x3 => 'Fast',
		0x4 => 'Normal',
		0x5 => 'Maximum',
		0x6 => 'Imploded',
		0x8 => 'Deflated'
	);

	/**
	 * Beginning of central directory record.
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $ctrlDirHeader = "\x50\x4b\x01\x02";

	/**
	 * End of central directory record.
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $ctrlDirEnd = "\x50\x4b\x05\x06\x00\x00\x00\x00";

	/**
	 * Beginning of file contents.
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $fileHeader = "\x50\x4b\x03\x04";

	/**
	 * ZIP file data buffer
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $data = null;

	/**
	 * ZIP file metadata array
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $metadata = null;

	/**
	 * Holds the options array.
	 *
	 * @var    array|\ArrayAccess
	 * @since  1.0
	 */
	protected $options = array();

	/**
	 * Create a new Archive object.
	 *
	 * @param   array|\ArrayAccess  $options  An array of options or an object that implements \ArrayAccess
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	public function __construct($options = array())
	{
		if (!is_array($options) && !($options instanceof \ArrayAccess))
		{
			throw new \InvalidArgumentException(
				'The options param must be an array or implement the ArrayAccess interface.'
			);
		}

		$this->options = $options;
	}

	/**
	 * Create a ZIP compressed file from an array of file data.
	 *
	 * @param   string  $archive  Path to save archive.
	 * @param   array   $files    Array of files to add to archive.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @since   1.0
	 * @todo    Finish Implementation
	 */
	public function create($archive, $files)
	{
		$contents = array();
		$ctrldir = array();

		foreach ($files as $file)
		{
			$this->addToZipFile($file, $contents, $ctrldir);
		}

		return $this->createZipFile($contents, $ctrldir, $archive);
	}

	/**
	 * Extract a ZIP compressed file to a given path
	 *
	 * @param   string  $archive      Path to ZIP archive to extract
	 * @param   string  $destination  Path to extract archive into
	 *
	 * @return  boolean  True if successful
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function extract($archive, $destination)
	{
		if (!is_file($archive))
		{
			throw new \RuntimeException('Archive does not exist');
		}

		if ($this->hasNativeSupport())
		{
			return $this->extractNative($archive, $destination);
		}

		return $this->extractCustom($archive, $destination);
	}

	/**
	 * Tests whether this adapter can unpack files on this computer.
	 *
	 * @return  boolean  True if supported
	 *
	 * @since   1.0
	 */
	public static function isSupported()
	{
		return self::hasNativeSupport() || extension_loaded('zlib');
	}

	/**
	 * Method to determine if the server has native zip support for faster handling
	 *
	 * @return  boolean  True if php has native ZIP support
	 *
	 * @since   1.0
	 */
	public static function hasNativeSupport()
	{
		return extension_loaded('zip');
	}

	/**
	 * Checks to see if the data is a valid ZIP file.
	 *
	 * @param   string  &$data  ZIP archive data buffer.
	 *
	 * @return  boolean  True if valid, false if invalid.
	 *
	 * @since   1.0
	 */
	public function checkZipData(&$data)
	{
		return strpos($data, $this->fileHeader) !== false;
	}

	/**
	 * Extract a ZIP compressed file to a given path using a php based algorithm that only requires zlib support
	 *
	 * @param   string  $archive      Path to ZIP archive to extract.
	 * @param   string  $destination  Path to extract archive into.
	 *
	 * @return  boolean  True if successful
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	protected function extractCustom($archive, $destination)
	{
		$this->data = null;
		$this->metadata = null;

		$this->data = file_get_contents($archive);

		if (!$this->data)
		{
			throw new \RuntimeException('Unable to read archive (zip)');
		}

		if (!$this->readZipInfo($this->data))
		{
			throw new \RuntimeException('Get ZIP Information failed');
		}

		for ($i = 0, $n = count($this->metadata); $i < $n; $i++)
		{
			$lastPathCharacter = substr($this->metadata[$i]['name'], -1, 1);

			if ($lastPathCharacter !== '/' && $lastPathCharacter !== '\\')
			{
				$buffer = $this->getFileData($i);
				$path = Path::clean($destination . '/' . $this->metadata[$i]['name']);

				// Make sure the destination folder exists
				if (!Folder::create(dirname($path)))
				{
					throw new \RuntimeException('Unable to create destination');
				}

				if (!File::write($path, $buffer))
				{
					throw new \RuntimeException('Unable to write entry');
				}
			}
		}

		return true;
	}

	/**
	 * Extract a ZIP compressed file to a given path using native php api calls for speed
	 *
	 * @param   string  $archive      Path to ZIP archive to extract
	 * @param   string  $destination  Path to extract archive into
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	protected function extractNative($archive, $destination)
	{
		$zip = zip_open($archive);

		if (!is_resource($zip))
		{
			throw new \RuntimeException('Unable to open archive');
		}

		// Make sure the destination folder exists
		if (!Folder::create($destination))
		{
			throw new \RuntimeException('Unable to create destination');
		}

		// Read files in the archive
		while ($file = @zip_read($zip))
		{
			if (!zip_entry_open($zip, $file, "r"))
			{
				throw new \RuntimeException('Unable to read entry');
			}

			if (substr(zip_entry_name($file), strlen(zip_entry_name($file)) - 1) != "/")
			{
				$buffer = zip_entry_read($file, zip_entry_filesize($file));

				if (File::write($destination . '/' . zip_entry_name($file), $buffer) === false)
				{
					throw new \RuntimeException('Unable to write entry');
				}

				zip_entry_close($file);
			}
		}

		@zip_close($zip);

		return true;
	}

	/**
	 * Get the list of files/data from a ZIP archive buffer.
	 *
	 * <pre>
	 * KEY: Position in zipfile
	 * VALUES: 'attr'  --  File attributes
	 * 'crc'   --  CRC checksum
	 * 'csize' --  Compressed file size
	 * 'date'  --  File modification time
	 * 'name'  --  Filename
	 * 'method'--  Compression method
	 * 'size'  --  Original file size
	 * 'type'  --  File type
	 * </pre>
	 *
	 * @param   string  &$data  The ZIP archive buffer.
	 *
	 * @return  boolean True on success
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	private function readZipInfo(&$data)
	{
		$entries = array();

		// Find the last central directory header entry
		$fhLast = strpos($data, $this->ctrlDirEnd);

		do
		{
			$last = $fhLast;
		}

		while (($fhLast = strpos($data, $this->ctrlDirEnd, $fhLast + 1)) !== false);

		// Find the central directory offset
		$offset = 0;

		if ($last)
		{
			$endOfCentralDirectory = unpack(
				'vNumberOfDisk/vNoOfDiskWithStartOfCentralDirectory/vNoOfCentralDirectoryEntriesOnDisk/' .
				'vTotalCentralDirectoryEntries/VSizeOfCentralDirectory/VCentralDirectoryOffset/vCommentLength',
				substr($data, $last + 4)
			);
			$offset = $endOfCentralDirectory['CentralDirectoryOffset'];
		}

		// Get details from central directory structure.
		$fhStart = strpos($data, $this->ctrlDirHeader, $offset);
		$dataLength = strlen($data);

		do
		{
			if ($dataLength < $fhStart + 31)
			{
				throw new \RuntimeException('Invalid Zip Data');
			}

			$info = unpack('vMethod/VTime/VCRC32/VCompressed/VUncompressed/vLength', substr($data, $fhStart + 10, 20));
			$name = substr($data, $fhStart + 46, $info['Length']);

			$entries[$name] = array(
				'attr' => null,
				'crc' => sprintf("%08s", dechex($info['CRC32'])),
				'csize' => $info['Compressed'],
				'date' => null,
				'_dataStart' => null,
				'name' => $name,
				'method' => $this->methods[$info['Method']],
				'_method' => $info['Method'],
				'size' => $info['Uncompressed'],
				'type' => null
			);

			$entries[$name]['date'] = mktime(
				(($info['Time'] >> 11) & 0x1f),
				(($info['Time'] >> 5) & 0x3f),
				(($info['Time'] << 1) & 0x3e),
				(($info['Time'] >> 21) & 0x07),
				(($info['Time'] >> 16) & 0x1f),
				((($info['Time'] >> 25) & 0x7f) + 1980)
			);

			if ($dataLength < $fhStart + 43)
			{
				throw new \RuntimeException('Invalid ZIP data');
			}

			$info = unpack('vInternal/VExternal/VOffset', substr($data, $fhStart + 36, 10));

			$entries[$name]['type'] = ($info['Internal'] & 0x01) ? 'text' : 'binary';
			$entries[$name]['attr'] = (($info['External'] & 0x10) ? 'D' : '-') . (($info['External'] & 0x20) ? 'A' : '-')
				. (($info['External'] & 0x03) ? 'S' : '-') . (($info['External'] & 0x02) ? 'H' : '-') . (($info['External'] & 0x01) ? 'R' : '-');
			$entries[$name]['offset'] = $info['Offset'];

			// Get details from local file header since we have the offset
			$lfhStart = strpos($data, $this->fileHeader, $entries[$name]['offset']);

			if ($dataLength < $lfhStart + 34)
			{
				throw new \RuntimeException('Invalid Zip Data');
			}

			$info = unpack('vMethod/VTime/VCRC32/VCompressed/VUncompressed/vLength/vExtraLength', substr($data, $lfhStart + 8, 25));
			$name = substr($data, $lfhStart + 30, $info['Length']);
			$entries[$name]['_dataStart'] = $lfhStart + 30 + $info['Length'] + $info['ExtraLength'];

			// Bump the max execution time because not using the built in php zip libs makes this process slow.
			@set_time_limit(ini_get('max_execution_time'));
		}

		while ((($fhStart = strpos($data, $this->ctrlDirHeader, $fhStart + 46)) !== false));

		$this->metadata = array_values($entries);

		return true;
	}

	/**
	 * Returns the file data for a file by offsest in the ZIP archive
	 *
	 * @param   integer  $key  The position of the file in the archive.
	 *
	 * @return  string  Uncompressed file data buffer.
	 *
	 * @since   1.0
	 */
	private function getFileData($key)
	{
		if ($this->metadata[$key]['_method'] == 0x8)
		{
			return gzinflate(substr($this->data, $this->metadata[$key]['_dataStart'], $this->metadata[$key]['csize']));
		}
		elseif ($this->metadata[$key]['_method'] == 0x0)
		{
			/* Files that aren't compressed. */
			return substr($this->data, $this->metadata[$key]['_dataStart'], $this->metadata[$key]['csize']);
		}
		elseif ($this->metadata[$key]['_method'] == 0x12)
		{
			// If bz2 extension is loaded use it
			if (extension_loaded('bz2'))
			{
				return bzdecompress(substr($this->data, $this->metadata[$key]['_dataStart'], $this->metadata[$key]['csize']));
			}
		}

		return '';
	}

	/**
	 * Converts a UNIX timestamp to a 4-byte DOS date and time format
	 * (date in high 2-bytes, time in low 2-bytes allowing magnitude
	 * comparison).
	 *
	 * @param   integer  $unixtime  The current UNIX timestamp.
	 *
	 * @return  integer  The current date in a 4-byte DOS format.
	 *
	 * @since   1.0
	 */
	protected function unix2DosTime($unixtime = null)
	{
		$timearray = (is_null($unixtime)) ? getdate() : getdate($unixtime);

		if ($timearray['year'] < 1980)
		{
			$timearray['year'] = 1980;
			$timearray['mon'] = 1;
			$timearray['mday'] = 1;
			$timearray['hours'] = 0;
			$timearray['minutes'] = 0;
			$timearray['seconds'] = 0;
		}

		return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) | ($timearray['hours'] << 11) |
			($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
	}

	/**
	 * Adds a "file" to the ZIP archive.
	 *
	 * @param   array  &$file      File data array to add
	 * @param   array  &$contents  An array of existing zipped files.
	 * @param   array  &$ctrldir   An array of central directory information.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @todo    Review and finish implementation
	 */
	private function addToZipFile(array &$file, array &$contents, array &$ctrldir)
	{
		$data = &$file['data'];
		$name = str_replace('\\', '/', $file['name']);

		/* See if time/date information has been provided. */
		$ftime = null;

		if (isset($file['time']))
		{
			$ftime = $file['time'];
		}

		// Get the hex time.
		$dtime = dechex($this->unix2DosTime($ftime));
		$hexdtime = chr(hexdec($dtime[6] . $dtime[7])) . chr(hexdec($dtime[4] . $dtime[5])) . chr(hexdec($dtime[2] . $dtime[3]))
			. chr(hexdec($dtime[0] . $dtime[1]));

		/* Begin creating the ZIP data. */
		$fr = $this->fileHeader;
		/* Version needed to extract. */
		$fr .= "\x14\x00";
		/* General purpose bit flag. */
		$fr .= "\x00\x00";
		/* Compression method. */
		$fr .= "\x08\x00";
		/* Last modification time/date. */
		$fr .= $hexdtime;

		/* "Local file header" segment. */
		$unc_len = strlen($data);
		$crc = crc32($data);
		$zdata = gzcompress($data);
		$zdata = substr(substr($zdata, 0, strlen($zdata) - 4), 2);
		$c_len = strlen($zdata);

		/* CRC 32 information. */
		$fr .= pack('V', $crc);
		/* Compressed filesize. */
		$fr .= pack('V', $c_len);
		/* Uncompressed filesize. */
		$fr .= pack('V', $unc_len);
		/* Length of filename. */
		$fr .= pack('v', strlen($name));
		/* Extra field length. */
		$fr .= pack('v', 0);
		/* File name. */
		$fr .= $name;

		/* "File data" segment. */
		$fr .= $zdata;

		/* Add this entry to array. */
		$old_offset = strlen(implode('', $contents));
		$contents[] = &$fr;

		/* Add to central directory record. */
		$cdrec = $this->ctrlDirHeader;
		/* Version made by. */
		$cdrec .= "\x00\x00";
		/* Version needed to extract */
		$cdrec .= "\x14\x00";
		/* General purpose bit flag */
		$cdrec .= "\x00\x00";
		/* Compression method */
		$cdrec .= "\x08\x00";
		/* Last mod time/date. */
		$cdrec .= $hexdtime;
		/* CRC 32 information. */
		$cdrec .= pack('V', $crc);
		/* Compressed filesize. */
		$cdrec .= pack('V', $c_len);
		/* Uncompressed filesize. */
		$cdrec .= pack('V', $unc_len);
		/* Length of filename. */
		$cdrec .= pack('v', strlen($name));
		/* Extra field length. */
		$cdrec .= pack('v', 0);
		/* File comment length. */
		$cdrec .= pack('v', 0);
		/* Disk number start. */
		$cdrec .= pack('v', 0);
		/* Internal file attributes. */
		$cdrec .= pack('v', 0);
		/* External file attributes -'archive' bit set. */
		$cdrec .= pack('V', 32);
		/* Relative offset of local header. */
		$cdrec .= pack('V', $old_offset);
		/* File name. */
		$cdrec .= $name;
		/* Optional extra field, file comment goes here. */

		/* Save to central directory array. */
		$ctrldir[] = &$cdrec;
	}

	/**
	 * Creates the ZIP file.
	 *
	 * Official ZIP file format: http://www.pkware.com/appnote.txt
	 *
	 * @param   array   &$contents  An array of existing zipped files.
	 * @param   array   &$ctrlDir   An array of central directory information.
	 * @param   string  $path       The path to store the archive.
	 *
	 * @return  boolean  True if successful
	 *
	 * @since   1.0
	 * @todo	Review and finish implementation
	 */
	private function createZipFile(array &$contents, array &$ctrlDir, $path)
	{
		$data = implode('', $contents);
		$dir = implode('', $ctrlDir);

		$buffer = $data . $dir . $this->ctrlDirEnd . /* Total # of entries "on this disk". */
		pack('v', count($ctrlDir)) . /* Total # of entries overall. */
		pack('v', count($ctrlDir)) . /* Size of central directory. */
		pack('V', strlen($dir)) . /* Offset to start of central dir. */
		pack('V', strlen($data)) . /* ZIP file comment length. */
		"\x00\x00";

		return File::write($path, $buffer);
	}
}
