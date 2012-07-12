<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Archive
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
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
 * @package     Joomla.Platform
 * @subpackage  Archive
 * @since       11.1
 */
class JArchiveGzip implements JArchiveExtractable
{
	/**
	 * Gzip file flags.
	 *
	 * @var    array
	 * @since  11.1
	 */
	private $_flags = array('FTEXT' => 0x01, 'FHCRC' => 0x02, 'FEXTRA' => 0x04, 'FNAME' => 0x08, 'FCOMMENT' => 0x10);

	/**
	 * Gzip file data buffer
	 *
	 * @var    string
	 * @since  11.1
	 */
	private $_data = null;

	/**
	 * Extract a Gzip compressed file to a given path
	 *
	 * @param   string  $archive      Path to ZIP archive to extract
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
		// Initialise variables.
		$this->_data = null;

		if (!extension_loaded('zlib'))
		{
			if (class_exists('JError'))
			{
				return JError::raiseWarning(100, 'The zlib extension is not available.');
			}
			else
			{
				throw new RuntimeException('The zlib extension is not available.');
			}
		}

		if (!isset($options['use_streams']) || $options['use_streams'] == false)
		{
			if (!$this->_data = JFile::read($archive))
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

			$position = $this->_getFilePosition();
			$buffer = gzinflate(substr($this->_data, $position, strlen($this->_data) - $position));
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

			// Use gz
			$input->set('processingmethod', 'gz');

			if (!$input->open($archive))
			{
				if (class_exists('JError'))
				{
					return JError::raiseWarning(100, 'Unable to read archive (gz)');
				}
				else
				{
					throw new RuntimeException('Unable to read archive (gz)');
				}
			}

			$output = JFactory::getStream();

			if (!$output->open($destination, 'w'))
			{
				$input->close();
				if (class_exists('JError'))
				{
					return JError::raiseWarning(100, 'Unable to write archive (gz)');
				}
				else
				{
					throw new RuntimeException('Unable to write archive (gz)');
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
							return JError::raiseWarning(100, 'Unable to write file (gz)');
						}
						else
						{
							throw new RuntimeException('Unable to write file (gz)');
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
		return extension_loaded('zlib');
	}

	/**
	 * Get file data offset for archive
	 *
	 * @return  integer  Data position marker for archive
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public function _getFilePosition()
	{
		// Gzipped file... unpack it first
		$position = 0;
		$info = @ unpack('CCM/CFLG/VTime/CXFL/COS', substr($this->_data, $position + 2));

		if (!$info)
		{
			if (class_exists('JError'))
			{
				return JError::raiseWarning(100, 'Unable to decompress data.');
			}
			else
			{
				throw new RuntimeException('Unable to decompress data.');
			}
		}

		$position += 10;

		if ($info['FLG'] & $this->_flags['FEXTRA'])
		{
			$XLEN = unpack('vLength', substr($this->_data, $position + 0, 2));
			$XLEN = $XLEN['Length'];
			$position += $XLEN + 2;
		}

		if ($info['FLG'] & $this->_flags['FNAME'])
		{
			$filenamePos = strpos($this->_data, "\x0", $position);
			$position = $filenamePos + 1;
		}

		if ($info['FLG'] & $this->_flags['FCOMMENT'])
		{
			$commentPos = strpos($this->_data, "\x0", $position);
			$position = $commentPos + 1;
		}

		if ($info['FLG'] & $this->_flags['FHCRC'])
		{
			$hcrc = unpack('vCRC', substr($this->_data, $position + 0, 2));
			$hcrc = $hcrc['CRC'];
			$position += 2;
		}

		return $position;
	}
}
