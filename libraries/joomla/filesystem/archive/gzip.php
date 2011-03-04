<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  FileSystem
 */

defined('JPATH_PLATFORM') or die;

/**
 * Gzip format adapter for the JArchive class
 *
 * This class is inspired from and draws heavily in code and concept from the Compress package of
 * The Horde Project <http://www.horde.org>
 *
 * @contributor  Michael Slusarz <slusarz@horde.org>
 * @contributor  Michael Cochrane <mike@graftonhall.co.nz>
 *
 * @package		Joomla.Platform
 * @subpackage	FileSystem
 * @since		11.1
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
	* @param	string	$archive		Path to ZIP archive to extract
	* @param	string	$destination	Path to extract archive to
	* @param	array	$options		Extraction options [unused]
	*
	* @return	boolean	True if successful
	* @since	11.1
	*/
	public function extract($archive, $destination, $options = array ())
	{
		// Initialise variables.
		$this->_data = null;

		if (!extension_loaded('zlib')) {
			$this->set('error.message', JText::_('JLIB_FILESYSTEM_GZIP_NOT_SUPPORTED'));

			return JError::raiseWarning(100, $this->get('error.message'));
		}

		/*
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
		*/

		// New style! streams!
		$input = JFactory::getStream();
		$input->set('processingmethod', 'gz'); // use gz

		if (!$input->open($archive)) {
			$this->set('error.message', JText::_('JLIB_FILESYSTEM_GZIP_UNABLE_TO_READ'));

			return JError::raiseWarning(100, $this->get('error.message'));
		}

		$output = JFactory::getStream();

		if (!$output->open($destination, 'w')) {
			$this->set('error.message', JText::_('JLIB_FILESYSTEM_GZIP_UNABLE_TO_WRITE'));
			$input->close(); // close the previous file

			return JError::raiseWarning(100, $this->get('error.message'));
		}

		$written = 0;
		do
		{
			$this->_data = $input->read($input->get('chunksize', 8196));
			if ($this->_data) {
				if (!$output->write($this->_data)) {
					$this->set('error.message', JText::_('JLIB_FILESYSTEM_GZIP_UNABLE_TO_WRITE_FILE'));

					return JError::raiseWarning(100, $this->get('error.message'));
				}
			}
		}
		while ($this->_data);

		$output->close();
		$input->close();

		return true;
	}

	/**
	 * Get file data offset for archive
	 *
	 * @return	int		Data position marker for archive
	 * @since	11.1
	 */
	function _getFilePosition()
	{
		// gzipped file... unpack it first
		$position = 0;
		$info = @ unpack('CCM/CFLG/VTime/CXFL/COS', substr($this->_data, $position +2));

		if (!$info) {
			$this->set('error.message', JText::_('JLIB_FILESYSTEM_GZIP_UNABLE_TO_DECOMPRESS'));
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