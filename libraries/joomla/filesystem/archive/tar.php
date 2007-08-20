<?php
/**
 * @version		$Id:tar.php 6961 2007-03-15 16:06:53Z tcp $
 * @package		Joomla.Framework
 * @subpackage	FileSystem
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Tar format adapter for the JArchive class
 *
 * This class is inspired from and draws heavily in code and concept from the Compress package of
 * The Horde Project <http://www.horde.org>
 *
 * @contributor  Michael Slusarz <slusarz@horde.org>
 * @contributor  Michael Cochrane <mike@graftonhall.co.nz>
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package 	Joomla.Framework
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
		// Initialize variables
		$this->_data = null;
		$this->_metadata = null;

		if (!$this->_data = JFile::read($archive))
		{
			$this->set('error.message', 'Unable to read archive');
			return JError::raiseWarning(100, $this->get('error.message'));
		}

		if (!$this->_getTarInfo($this->_data))
		{
			return JError::raiseWarning(100, $this->get('error.message'));
		}

		for ($i=0,$n=count($this->_metadata);$i<$n;$i++)
		{
			$type	= strtolower( $this->_metadata[$i]['type'] );
			if ($type == 'file' || $type == 'unix file')
			{
				$buffer = $this->_metadata[$i]['data'];
				$path = JPath::clean($destination.DS.$this->_metadata[$i]['name']);
				// Make sure the destination folder exists
				if (!JFolder::create(dirname($path)))
				{
					$this->set('error.message', 'Unable to create destination');
					return JError::raiseWarning(100, $this->get('error.message'));
				}
				if (JFile::write($path, $buffer) === false)
				{
					$this->set('error.message', 'Unable to write entry');
					return JError::raiseWarning(100, $this->get('error.message'));
				}
			}
		}
		return true;
	}

	/**
	 * Get the list of files/data from a Tar archive buffer.
	 *
	 * @access	private
	 * @param 	string	$data	The Tar archive buffer.
	 * @return	array	Archive metadata array
	 * <pre>
	 * KEY: Position in the array
	 * VALUES: 'attr'  --  File attributes
	 *         'data'  --  Raw file contents
	 *         'date'  --  File modification time
	 *         'name'  --  Filename
	 *         'size'  --  Original file size
	 *         'type'  --  File type
	 * </pre>
	 * @since	1.5
	 */
	function _getTarInfo(& $data)
	{
		$position = 0;
		$return_array = array ();

		while ($position < strlen($data))
		{
			$info = @ unpack("a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/Ctypeflag/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor", substr($data, $position));
			if (!$info) {
				$this->set('error.message', 'Unable to decompress data');
				return false;
			}

			$position += 512;
			$contents = substr($data, $position, octdec($info['size']));
			$position += ceil(octdec($info['size']) / 512) * 512;

			if ($info['filename']) {
				$file = array (
					'attr' => null,
					'data' => null,
					'date' => octdec($info['mtime']
				), 'name' => trim($info['filename']), 'size' => octdec($info['size']), 'type' => isset ($this->_types[$info['typeflag']]) ? $this->_types[$info['typeflag']] : null);

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
				$return_array[] = $file;
			}
		}
		$this->_metadata = $return_array;
		return true;
	}
}