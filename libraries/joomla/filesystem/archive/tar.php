<?php
/**
 * @version		$Id:tar.php 6961 2007-03-15 16:06:53Z tcp $
 * @package		Joomla.Framework
 * @subpackage	FileSystem
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Tar format adapter for the JArchive class
 *
 * This class is inspired from and draws heavily in code and concept from the Compress package of
 * The Horde Project <http://www.horde.org>
 *
 * @contributor  Michael Slusarz <slusarz@horde.org>
 * @contributor  Michael Cochrane <mike@graftonhall.co.nz>
 *
 * @package		Joomla.Framework
 * @subpackage	FileSystem
 * @since		1.5
 */
class JArchiveTar extends JObject
{
	/**
	 * Tar file types.
	 * @var array
	 */
	var $_types = array (
		0x0 => 'Unix file',
		0x30 => 'File',
		0x31 => 'Link',
		0x32 => 'Symbolic link',
		0x33 => 'Character special file',
		0x34 => 'Block special file',
		0x35 => 'Directory',
		0x36 => 'FIFO special file',
		0x37 => 'Contiguous file'
	);

	/**
	 * Tar file flags.
	 * @var array
	 */
	var $_flags = array (
		'FTEXT' => 0x01,
		'FHCRC' => 0x02,
		'FEXTRA' => 0x04,
		'FNAME' => 0x08,
		'FCOMMENT' => 0x10
	);

	/**
	 * Tar file data buffer
	 * @var string
	 */
	var $_data = null;

	/**
	 * Tar file metadata array
	 * @var array
	 */
	var $_metadata = null;

	/**
	* Extract a ZIP compressed file to a given path
	*
	* @access	public
	* @param	string	$archive		Path to ZIP archive to extract
	* @param	string	$destination	Path to extract archive into
	* @param	array	$options		Extraction options [unused]
	* @return	boolean	True if successful
	* @since	1.5
	*/
	function extract($archive, $destination, $options = array ())
	{
		// Initialise variables.
		$this->_data = null;
		$this->_metadata = null;

		$stream = JFactory::getStream();
		if(!$stream->open($archive, 'rb'))
		{
			$this->set('error.message', JText::_('JLIB_FILESYSTEM_TAR_UNABLE_TO_READ'));
			return JError::raiseWarning(100, $this->get('error.message'));
		}

		$position = 0;
		$return_array = array ();
		$i = 0;
		$chunksize = 512; // tar has items in 512 byte packets

		while($entry = $stream->read($chunksize)) {
			//$entry = &$this->_data[$i];
			$info = @ unpack("a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/Ctypeflag/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor", $entry);
			if (!$info) {
				$this->set('error.message', JText::_('JLIB_FILESYSTEM_TAR_UNABLE_TO_DECOMPRESS'));
				return JError::raiseWarning(100, $this->get('error.message'));
			}

			$size = octdec($info['size']);
			$bsize = ceil($size / $chunksize) * $chunksize;
			$contents = '';
			if($size) {
				//$contents = fread($this->_fh, $size);
				$contents = substr($stream->read($bsize),0, octdec($info['size']));
			}

			if ($info['filename']) {
				$file = array (
					'attr' => null,
					'data' => null,
					'date' => octdec($info['mtime']),
					'name' => trim($info['filename']),
					'size' => octdec($info['size']),
					'type' => isset ($this->_types[$info['typeflag']]) ? $this->_types[$info['typeflag']] : null);

				if (($info['typeflag'] == 0) || ($info['typeflag'] == 0x30) || ($info['typeflag'] == 0x35)) {
					/* File or folder. */
					$file['data'] = $contents;

					$mode = hexdec(substr($info['mode'], 4, 3));
					$file['attr'] = (($info['typeflag'] == 0x35) ? 'd' : '-') .
					(($mode & 0x400) ? 'r' : '-') .
					(($mode & 0x200) ? 'w' : '-') .
					(($mode & 0x100) ? 'x' : '-') .
					(($mode & 0x040) ? 'r' : '-') .
					(($mode & 0x020) ? 'w' : '-') .
					(($mode & 0x010) ? 'x' : '-') .
					(($mode & 0x004) ? 'r' : '-') .
					(($mode & 0x002) ? 'w' : '-') .
					(($mode & 0x001) ? 'x' : '-');
				} else {
					/* Some other type. */
				}

				$type = strtolower( $file['type'] );
				if ($type == 'file' || $type == 'unix file')
				{
					$path = JPath::clean($destination.DS.$file['name']);
					// Make sure the destination folder exists
					if (!JFolder::create(dirname($path)))
					{
						$this->set('error.message', JText::_('JLIB_FILESYSTEM_TAR_UNABLE_TO_CREATE_DESTINATION'));
						return JError::raiseWarning(100, $this->get('error.message'));
			}
					if (JFile::write($path, $contents, true) === false)
					{
						$this->set('error.message', JText::_('JLIB_FILESYSTEM_TAR_UNABLE_TO_WRITE_ENTRY'));
						return JError::raiseWarning(100, $this->get('error.message'));
		}
					$contents = ''; // reclaim some memory
				}
			}
		}
		$stream->close();
		return true;
	}
}
