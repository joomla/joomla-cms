<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document;

defined('JPATH_PLATFORM') or die;

/**
 * ImageDocument class, provides an easy interface to output image data
 *
 * @since  12.1
 */
class ImageDocument extends Document
{
	/**
	 * Class constructor
	 *
	 * @param   array  $options  Associative array of options
	 *
	 * @since   12.1
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		// Set mime type
		$this->_mime = 'image/png';

		// Set document type
		$this->_type = 'image';
	}

	/**
	 * Render the document.
	 *
	 * @param   boolean  $cache   If true, cache the output
	 * @param   array    $params  Associative array of attributes
	 *
	 * @return  string  The rendered data
	 *
	 * @since   12.1
	 */
	public function render($cache = false, $params = array())
	{
		// Get the image type
		$type = \JFactory::getApplication()->input->get('type', 'png');

		switch ($type)
		{
			case 'jpg':
			case 'jpeg':
				$this->_mime = 'image/jpeg';
				break;
			case 'gif':
				$this->_mime = 'image/gif';
				break;
			case 'png':
			default:
				$this->_mime = 'image/png';
				break;
		}

		$this->_charset = null;

		parent::render();

		return $this->getBuffer();
	}
}
