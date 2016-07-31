<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Archive
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.stream');

/**
 * Bzip2 format adapter for the JArchive class
 *
 * @since  11.1
 */
class JArchiveBzip2 implements JArchiveExtractable
{
	/**
	 * Bzip2 file data buffer
	 *
	 * @var    string
	 * @since  11.1
	 */
	private $_data = null;

	/**
	 * Extract a Bzip2 compressed file to a given path
	 *
	 * @param   string  $archive      Path to Bzip2 archive to extract
	 * @param   string  $destination  Path to extract archive to
	 * @param   array   $options      Extraction options [unused]
	 *
	 * @return  boolean  True if successful
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public function extract($archive, $destination, array $options = array ())
	{
		$this->_data = null;

		if (!extension_loaded('bz2'))
		{
			$this->raiseWarning(100, 'The bz2 extension is not available.');
		}

		if (isset($options['use_streams']) && $options['use_streams'] != false)
		{
			return $this->extractStream($archive, $destination, $options);
		}

		// Old style: read the whole file and then parse it
		$this->_data = file_get_contents($archive);

		if (!$this->_data)
		{
			return $this->raiseWarning(100, 'Unable to read archive');
		}

		$buffer = bzdecompress($this->_data);
		unset($this->_data);

		if (empty($buffer))
		{
			return $this->raiseWarning(100, 'Unable to decompress data');
		}

		if (JFile::write($destination, $buffer) === false)
		{
			return $this->raiseWarning(100, 'Unable to write archive');
		}

		return true;
	}

	/**
	 * Method to extract archive using stream objects
	 *
	 * @param   string  $archive      Path to Bzip2 archive to extract
	 * @param   string  $destination  Path to extract archive to
	 * @param   array   $options      Extraction options [unused]
	 *
	 * @return  boolean  True if successful
	 */
	protected function extractStream($archive, $destination, $options = array ())
	{
		// New style! streams!
		$input = JFactory::getStream();

		// Use bzip
		$input->set('processingmethod', 'bz');

		if (!$input->open($archive))
		{
			return $this->raiseWarning(100, 'Unable to read archive (bz2)');

		}

		$output = JFactory::getStream();

		if (!$output->open($destination, 'w'))
		{
			$input->close();

			return $this->raiseWarning(100, 'Unable to write archive (bz2)');

		}

		do
		{
			$this->_data = $input->read($input->get('chunksize', 8196));

			if ($this->_data && !$output->write($this->_data))
			{
				$input->close();

				return $this->raiseWarning(100, 'Unable to write archive (bz2)');
			}
		}

		while ($this->_data);

		$output->close();
		$input->close();

		return true;
	}

	/**
	 * Temporary private method to isolate JError from the extract method
	 * This code should be removed when JError is removed.
	 *
	 * @param   int     $code  The application-internal error code for this error
	 * @param   string  $msg   The error message, which may also be shown the user if need be.
	 *
	 * @return  JException  JException instance if JError class exists
	 *
	 * @throws  RuntimeException if JError class does not exist
	 */
	private function raiseWarning($code, $msg)
	{
		if (class_exists('JError'))
		{
			return JError::raiseWarning($code, $msg);
		}

		throw new RuntimeException($msg);
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
		return extension_loaded('bz2');
	}
}
