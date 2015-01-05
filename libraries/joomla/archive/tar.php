<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Archive
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.path');

/**
 * Tar format adapter for the JArchive class
 *
 * This class is inspired from and draws heavily in code and concept from the Compress package of
 * The Horde Project <http://www.horde.org>
 *
 * @contributor  Michael Slusarz <slusarz@horde.org>
 * @contributor  Michael Cochrane <mike@graftonhall.co.nz>
 *
 * @since  11.1
 */
class JArchiveTar implements JArchiveExtractable
{
	/**
	 * Tar file types.
	 *
	 * @var    array
	 * @since  11.1
	 */
	private $_types = array(
		0x0 => 'Unix file',
		0x30 => 'File',
		0x31 => 'Link',
		0x32 => 'Symbolic link',
		0x33 => 'Character special file',
		0x34 => 'Block special file',
		0x35 => 'Directory',
		0x36 => 'FIFO special file',
		0x37 => 'Contiguous file');

	/**
	 * Tar file data buffer
	 *
	 * @var    string
	 * @since  11.1
	 */
	private $_data = null;

	/**
	 * Tar file metadata array
	 *
	 * @var    array
	 * @since  11.1
	 */
	private $_metadata = null;

	/**
	 * Extract a ZIP compressed file to a given path
	 *
	 * @param   string  $archive      Path to ZIP archive to extract
	 * @param   string  $destination  Path to extract archive into
	 * @param   array   $options      Extraction options [unused]
	 *
	 * @return  boolean True if successful
	 *
	 * @throws  RuntimeException
	 * @since   11.1
	 */
	public function extract($archive, $destination, array $options = array())
	{
		$this->_data = null;
		$this->_metadata = null;

		$this->_data = file_get_contents($archive);

		if (!$this->_data)
		{
			if (class_exists('JError'))
			{
				return JError::raiseWarning(100, 'Unable to read archive');
			}
			else
			{
				throw new RuntimeException('Unable to read archive');
			}
		}

		$this->_getTarInfo($this->_data);

		for ($i = 0, $n = count($this->_metadata); $i < $n; $i++)
		{
			$type = strtolower($this->_metadata[$i]['type']);

			if ($type == 'file' || $type == 'unix file')
			{
				$buffer = $this->_metadata[$i]['data'];
				$path = JPath::clean($destination . '/' . $this->_metadata[$i]['name']);

				// Make sure the destination folder exists
				if (!JFolder::create(dirname($path)))
				{
					if (class_exists('JError'))
					{
						return JError::raiseWarning(100, 'Unable to create destination');
					}
					else
					{
						throw new RuntimeException('Unable to create destination');
					}
				}

				if (JFile::write($path, $buffer) === false)
				{
					if (class_exists('JError'))
					{
						return JError::raiseWarning(100, 'Unable to write entry');
					}
					else
					{
						throw new RuntimeException('Unable to write entry');
					}
				}
			}
		}

		return true;
	}

	/**
	 * Tests whether this adapter can unpack files on this computer.
	 *
	 * @return  boolean  True if supported
	 *
	 * @since   11.3
	 */
	public static function isSupported()
	{
		return true;
	}

	/**
	 * Get the list of files/data from a Tar archive buffer.
	 *
	 * @param   string  &$data  The Tar archive buffer.
	 *
	 * @return   array  Archive metadata array
	 * <pre>
	 * KEY: Position in the array
	 * VALUES: 'attr'  --  File attributes
	 * 'data'  --  Raw file contents
	 * 'date'  --  File modification time
	 * 'name'  --  Filename
	 * 'size'  --  Original file size
	 * 'type'  --  File type
	 * </pre>
	 *
	 * @since    11.1
	 */
	protected function _getTarInfo(& $data)
	{
		$position = 0;
		$return_array = array();

		while ($position < strlen($data))
		{
			if (version_compare(PHP_VERSION, '5.5', '>='))
			{
				$info = @unpack(
					"Z100filename/Z8mode/Z8uid/Z8gid/Z12size/Z12mtime/Z8checksum/Ctypeflag/Z100link/Z6magic/Z2version/Z32uname/Z32gname/Z8devmajor/Z8devminor",
					substr($data, $position)
				);
			}
			else
			{
				$info = @unpack(
					"a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/Ctypeflag/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor",
					substr($data, $position)
				);
			}

			if (!$info)
			{
				if (class_exists('JError'))
				{
					return JError::raiseWarning(100, 'Unable to decompress data');
				}
				else
				{
					throw new RuntimeException('Unable to decompress data');
				}
			}

			$position += 512;
			$contents = substr($data, $position, octdec($info['size']));
			$position += ceil(octdec($info['size']) / 512) * 512;

			if ($info['filename'])
			{
				$file = array(
					'attr' => null,
					'data' => null,
					'date' => octdec($info['mtime']),
					'name' => trim($info['filename']),
					'size' => octdec($info['size']),
					'type' => isset($this->_types[$info['typeflag']]) ? $this->_types[$info['typeflag']] : null);

				if (($info['typeflag'] == 0) || ($info['typeflag'] == 0x30) || ($info['typeflag'] == 0x35))
				{
					/* File or folder. */
					$file['data'] = $contents;

					$mode = hexdec(substr($info['mode'], 4, 3));
					$file['attr'] = (($info['typeflag'] == 0x35) ? 'd' : '-') . (($mode & 0x400) ? 'r' : '-') . (($mode & 0x200) ? 'w' : '-') .
						(($mode & 0x100) ? 'x' : '-') . (($mode & 0x040) ? 'r' : '-') . (($mode & 0x020) ? 'w' : '-') . (($mode & 0x010) ? 'x' : '-') .
						(($mode & 0x004) ? 'r' : '-') . (($mode & 0x002) ? 'w' : '-') . (($mode & 0x001) ? 'x' : '-');
				}
				else
				{
					/* Some other type. */
				}

				$return_array[] = $file;
			}
		}

		$this->_metadata = $return_array;

		return true;
	}
}
