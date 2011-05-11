<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * DocumentRAW class, provides an easy interface to parse and display raw output
 *
 * @package     Joomla.Platform
 * @subpackage  Document
 * @since       11.1
 */

jimport('joomla.document.document');

class JDocumentRAW extends JDocument
{

	/**
	 * Class constructor
	 *
	 * @param   array  $options  Associative array of options
	 */
	protected function __construct($options = array())
	{
		parent::__construct($options);

		//set mime type
		$this->_mime = 'text/html';

		//set document type
		$this->_type = 'raw';
	}

	/**
	 * Render the document.
	 *
	 * @param   bool   $cache   If true, cache the output
	 * @param   array  $params  Associative array of attributes
	 *
	 * @return	The rendered data
	 */
	public function render($cache = false, $params = array())
	{
		parent::render();
		return $this->getBuffer();
	}
}
