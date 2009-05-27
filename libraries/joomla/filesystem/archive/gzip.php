<?php
/**
 * @version		$Id:gzip.php 6961 2007-03-15 16:06:53Z tcp $
 * @package		Joomla.Framework
 * @subpackage	FileSystem
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Gzip format adapter for the JArchive class
 *
 * This class is inspired from and draws heavily in code and concept from the Compress package of
 * The Horde Project <http://www.horde.org>
 *
 * @contributor  Michael Slusarz <slusarz@horde.org>
 * @contributor  Michael Cochrane <mike@graftonhall.co.nz>
 *
 * @package 	Joomla.Framework
 * @subpackage	FileSystem
 * @since		1.5
 */
class JArchiveGzip extends JObject
{
	/**
	 * Gzip file flags.
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
	 * Gzip file data buffer
	 * @var string
	 */
	var $_data = null;

	/**
	* Extract a Gzip compressed file to a given path
	*
	* @access	public
	* @param	string	$archive		Path to ZIP archive to extract
	* @param	string	$destination	Path to extract archive to
	* @param	array	$options		Extraction options [unused]
	* @return	boolean	True if successful
	* @since	1.5
	*/
	function extract($archive, $destination, $options = array ())
	{
		// Initialize variables
		$this->_data = null;

		if (!extension_loaded('zlib')) {
			$this->set('error.message', 'Zlib Not Supported');
			return JError::raiseWarning(100, $this->get('error.message'));
		}

		if (!$this->_data = JFile::read($archive)) {
			$this->set('error.message', 'Unable to read archive');
			return JError::raiseWarning(100, $this->get('error.message'));
		}

		$position = $this->_getFilePosition();
		$buffer = gzinflate(substr($this->_data, $position, strlen($this->_data) - $position));
		if (empty ($buffer)) {
			$this->set('error.message', 'Unable to decompress data');
			return JError::raiseWarning(100, $this->get('error.message'));
		}

		if (JFile::write($destination, $buffer) === false) {
			$this->set('error.message', 'Unable to write archive');
			return JError::raiseWarning(100, $this->get('error.message'));
		}
		return true;
	}

	/**
	* Get file data offset for archive
	*
	* @access	public
	* @return	int	Data position marker for archive
	* @since	1.5
	*/
	function _getFilePosition()
	{
		// gzipped file... unpack it first
		$position = 0;
		$info = @ unpack('CCM/CFLG/VTime/CXFL/COS', substr($this->_data, $position +2));
		if (!$info) {
			$this->set('error.message', 'Unable to decompress data');
			return false;
		}
		$position += 10;

		if ($info['FLG'] & $this->_flags['FEXTRA']) {
			$XLEN = unpack('vLength', substr($this->_data, $position +0, 2));
			$XLEN = $XLEN['Length'];
			$position += $XLEN +2;
		}

		if ($info['FLG'] & $this->_flags['FNAME']) {
			$filenamePos = strpos($this->_data, "\x0", $position);
			$filename = substr($this->_data, $position, $filenamePos - $position);
			$position = $filenamePos +1;
		}

		if ($info['FLG'] & $this->_flags['FCOMMENT']) {
			$commentPos = strpos($this->_data, "\x0", $position);
			$comment = substr($this->_data, $position, $commentPos - $position);
			$position = $commentPos +1;
		}

		if ($info['FLG'] & $this->_flags['FHCRC']) {
			$hcrc = unpack('vCRC', substr($this->_data, $position +0, 2));
			$hcrc = $hcrc['CRC'];
			$position += 2;
		}

		return $position;
	}
}
