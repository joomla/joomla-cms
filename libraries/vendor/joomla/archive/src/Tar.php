<?php
/**
 * Part of the Joomla Framework Archive Package
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Archive;

use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;

/**
 * Tar format adapter for the Archive package
 *
 * This class is inspired from and draws heavily in code and concept from the Compress package of
 * The Horde Project <http://www.horde.org>
 *
 * @contributor  Michael Slusarz <slusarz@horde.org>
 * @contributor  Michael Cochrane <mike@graftonhall.co.nz>
 *
 * @since  1.0
 */
class Tar implements ExtractableInterface
{
	/**
	 * Tar file types.
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $types = array(
		0x0  => 'Unix file',
		0x30 => 'File',
		0x31 => 'Link',
		0x32 => 'Symbolic link',
		0x33 => 'Character special file',
		0x34 => 'Block special file',
		0x35 => 'Directory',
		0x36 => 'FIFO special file',
		0x37 => 'Contiguous file',
	);

	/**
	 * Tar file data buffer
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $data;

	/**
	 * Tar file metadata array
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $metadata;

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
		if (!\is_array($options) && !($options instanceof \ArrayAccess))
		{
			throw new \InvalidArgumentException(
				'The options param must be an array or implement the ArrayAccess interface.'
			);
		}

		$this->options = $options;
	}

	/**
	 * Extract a ZIP compressed file to a given path
	 *
	 * @param   string  $archive      Path to ZIP archive to extract
	 * @param   string  $destination  Path to extract archive into
	 *
	 * @return  boolean True if successful
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function extract($archive, $destination)
	{
		$destination = Path::resolve($destination);

		$this->metadata = null;
		$this->data     = file_get_contents($archive);

		if (!$this->data)
		{
			throw new \RuntimeException('Unable to read archive');
		}

		$this->getTarInfo($this->data);

		for ($i = 0, $n = \count($this->metadata); $i < $n; $i++)
		{
			$type = strtolower($this->metadata[$i]['type']);

			if ($type === 'file' || $type === 'unix file')
			{
				$buffer = $this->metadata[$i]['data'];
				$path = Path::clean($destination . '/' . $this->metadata[$i]['name']);

				if (!$this->isBelow($destination, $path))
				{
					throw new \OutOfBoundsException('Unable to write outside of destination path', 100);
				}

				// Make sure the destination folder exists
				if (!Folder::create(\dirname($path)))
				{
					throw new \RuntimeException('Unable to create destination folder ' . \dirname($path));
				}

				if (!File::write($path, $buffer))
				{
					throw new \RuntimeException('Unable to write entry to file ' . $path);
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
	 * @since   1.0
	 */
	public static function isSupported()
	{
		return true;
	}

	/**
	 * Get the list of files/data from a Tar archive buffer.
	 *
	 * @param   string  $data  The Tar archive buffer.
	 *
	 * @return  array  Archive metadata array
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
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	protected function getTarInfo(&$data)
	{
		$position    = 0;
		$returnArray = array();

		while ($position < \strlen($data))
		{
			if (version_compare(\PHP_VERSION, '5.5', '>='))
			{
				$info = @unpack(
					'Z100filename/Z8mode/Z8uid/Z8gid/Z12size/Z12mtime/Z8checksum/Ctypeflag'
					. '/Z100link/Z6magic/Z2version/Z32uname/Z32gname/Z8devmajor/Z8devminor',
					substr($data, $position)
				);
			}
			else
			{
				$info = @unpack(
					'a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/Ctypeflag'
					. '/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor',
					substr($data, $position)
				);
			}

			/*
			 * This variable has been set in the previous loop, meaning that the filename was present in the previous block
			 * to allow more than 100 characters - see below
			 */
			if (isset($longlinkfilename))
			{
				$info['filename'] = $longlinkfilename;
				unset($longlinkfilename);
			}

			if (!$info)
			{
				throw new \RuntimeException('Unable to decompress data');
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
					'type' => isset($this->types[$info['typeflag']]) ? $this->types[$info['typeflag']] : null,
				);

				if (($info['typeflag'] == 0) || ($info['typeflag'] == 0x30) || ($info['typeflag'] == 0x35))
				{
					// File or folder.
					$file['data'] = $contents;

					$mode         = hexdec(substr($info['mode'], 4, 3));
					$file['attr'] = (($info['typeflag'] == 0x35) ? 'd' : '-')
						. (($mode & 0x400) ? 'r' : '-')
						. (($mode & 0x200) ? 'w' : '-')
						. (($mode & 0x100) ? 'x' : '-')
						. (($mode & 0x040) ? 'r' : '-')
						. (($mode & 0x020) ? 'w' : '-')
						. (($mode & 0x010) ? 'x' : '-')
						. (($mode & 0x004) ? 'r' : '-')
						. (($mode & 0x002) ? 'w' : '-')
						. (($mode & 0x001) ? 'x' : '-');
				}
				elseif (\chr($info['typeflag']) === 'L' && $info['filename'] === '././@LongLink')
				{
					// GNU tar ././@LongLink support - the filename is actually in the contents, set a variable here so we can test in the next loop
					$longlinkfilename = $contents;

					// And the file contents are in the next block so we'll need to skip this
					continue;
				}

				$returnArray[] = $file;
			}
		}

		$this->metadata = $returnArray;

		return true;
	}

	/**
	 * Check if a path is below a given destination path
	 *
	 * @param   string  $destination  Root path
	 * @param   string  $path         Path to check
	 *
	 * @return  boolean
	 *
	 * @since   1.1.12
	 */
	private function isBelow($destination, $path)
	{
		$absoluteRoot = Path::clean(Path::resolve($destination));
		$absolutePath = Path::clean(Path::resolve($path));

		return strpos($absolutePath, $absoluteRoot) === 0;
	}
}
