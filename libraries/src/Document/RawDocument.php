<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document;

defined('JPATH_PLATFORM') or die;

/**
 * RawDocument class, provides an easy interface to parse and display raw output
 *
 * @since  11.1
 */
class RawDocument extends Document
{
	/**
	 * Class constructor
	 *
	 * @param   array  $options  Associative array of options
	 *
	 * @since   11.1
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		// Set mime type
		$this->_mime = 'text/html';

		// Set document type
		$this->_type = 'raw';
	}

	/**
	 * Render the document.
	 *
	 * @param   boolean  $cache   If true, cache the output
	 * @param   array    $params  Associative array of attributes
	 *
	 * @return  string  The rendered data
	 *
	 * @since   11.1
	 */
	public function render($cache = false, $params = array())
	{
		parent::render();

		return $this->getBuffer();
	}
}
