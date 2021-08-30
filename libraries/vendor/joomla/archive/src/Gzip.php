<?php
/**
 * Part of the Joomla Framework Archive Package
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Archive;

use Joomla\Filesystem\File;
use Joomla\Filesystem\Stream;

/**
 * Gzip format adapter for the Archive package
 *
 * This class is inspired from and draws heavily in code and concept from the Compress package of
 * The Horde Project <http://www.horde.org>
 *
 * @contributor  Michael Slusarz <slusarz@horde.org>
 * @contributor  Michael Cochrane <mike@graftonhall.co.nz>
 *
 * @since  1.0
 */
class Gzip implements ExtractableInterface
{
	/**
	 * Gzip file flags.
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $flags = array('FTEXT' => 0x01, 'FHCRC' => 0x02, 'FEXTRA' => 0x04, 'FNAME' => 0x08, 'FCOMMENT' => 0x10);

	/**
	 * Gzip file data buffer
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $data;

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
	 * @param   array|\ArrayAccess  $options  An array of options
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
	 * Extract a Gzip compressed file to a given path
	 *
	 * @param   string  $archive      Path to ZIP archive to extract
	 * @param   string  $destination  Path to extract archive to
	 *
	 * @return  boolean  True if successful
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function extract($archive, $destination)
	{
		$this->data = null;

		if (!isset($this->options['use_streams']) || $this->options['use_streams'] == false)
		{
			$this->data = file_get_contents($archive);

			if (!$this->data)
			{
				throw new \RuntimeException('Unable to read archive');
			}

			$position = $this->getFilePosition();
			$buffer   = gzinflate(substr($this->data, $position, \strlen($this->data) - $position));

			if (empty($buffer))
			{
				throw new \RuntimeException('Unable to decompress data');
			}

			if (!File::write($destination, $buffer))
			{
				throw new \RuntimeException('Unable to write archive to file ' . $destination);
			}
		}
		else
		{
			// New style! streams!
			$input = Stream::getStream();

			// Use gz
			$input->set('processingmethod', 'gz');

			if (!$input->open($archive))
			{
				throw new \RuntimeException('Unable to read archive');
			}

			$output = Stream::getStream();

			if (!$output->open($destination, 'w'))
			{
				$input->close();

				throw new \RuntimeException('Unable to open file "' . $destination . '" for writing');
			}

			do
			{
				$this->data = $input->read($input->get('chunksize', 8196));

				if ($this->data)
				{
					if (!$output->write($this->data))
					{
						$input->close();

						throw new \RuntimeException('Unable to write archive to file ' . $destination);
					}
				}
			}
			while ($this->data);

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
	 * @since   1.0
	 */
	public static function isSupported()
	{
		return \extension_loaded('zlib');
	}

	/**
	 * Get file data offset for archive
	 *
	 * @return  integer  Data position marker for archive
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function getFilePosition()
	{
		// Gzipped file... unpack it first
		$position = 0;
		$info     = @ unpack('CCM/CFLG/VTime/CXFL/COS', substr($this->data, $position + 2));

		if (!$info)
		{
			throw new \RuntimeException('Unable to decompress data.');
		}

		$position += 10;

		if ($info['FLG'] & $this->flags['FEXTRA'])
		{
			$XLEN = unpack('vLength', substr($this->data, $position + 0, 2));
			$XLEN = $XLEN['Length'];
			$position += $XLEN + 2;
		}

		if ($info['FLG'] & $this->flags['FNAME'])
		{
			$filenamePos = strpos($this->data, "\x0", $position);
			$position    = $filenamePos + 1;
		}

		if ($info['FLG'] & $this->flags['FCOMMENT'])
		{
			$commentPos = strpos($this->data, "\x0", $position);
			$position   = $commentPos + 1;
		}

		if ($info['FLG'] & $this->flags['FHCRC'])
		{
			$hcrc = unpack('vCRC', substr($this->data, $position + 0, 2));
			$hcrc = $hcrc['CRC'];
			$position += 2;
		}

		return $position;
	}
}
