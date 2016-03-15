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
			if (class_exists('JError'))
			{
				return JError::raiseWarning(100, 'The bz2 extension is not available.');
			}
			else
			{
				throw new RuntimeException('The bz2 extension is not available.');
			}
		}

		if (!isset($options['use_streams']) || $options['use_streams'] == false)
		{
			// Old style: read the whole file and then parse it
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

			$buffer = bzdecompress($this->_data);
			unset($this->_data);

			if (empty($buffer))
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

			if (JFile::write($destination, $buffer) === false)
			{
				if (class_exists('JError'))
				{
					return JError::raiseWarning(100, 'Unable to write archive');
				}
				else
				{
					throw new RuntimeException('Unable to write archive');
				}
			}
		}
		else
		{
			// New style! streams!
			$input = JFactory::getStream();

			// Use bzip
			$input->set('processingmethod', 'bz');

			if (!$input->open($archive))
			{
				if (class_exists('JError'))
				{
					return JError::raiseWarning(100, 'Unable to read archive (bz2)');
				}
				else
				{
					throw new RuntimeException('Unable to read archive (bz2)');
				}
			}

			$output = JFactory::getStream();

			if (!$output->open($destination, 'w'))
			{
				$input->close();

				if (class_exists('JError'))
				{
					return JError::raiseWarning(100, 'Unable to write archive (bz2)');
				}
				else
				{
					throw new RuntimeException('Unable to write archive (bz2)');
				}
			}

			do
			{
				$this->_data = $input->read($input->get('chunksize', 8196));

				if ($this->_data)
				{
					if (!$output->write($this->_data))
					{
						$input->close();

						if (class_exists('JError'))
						{
							return JError::raiseWarning(100, 'Unable to write archive (bz2)');
						}
						else
						{
							throw new RuntimeException('Unable to write archive (bz2)');
						}
					}
				}
			}

			while ($this->_data);

			$output->close();
			$input->close();
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
		return extension_loaded('bz2');
	}
}
