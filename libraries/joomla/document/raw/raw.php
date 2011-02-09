<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Document
 */

defined('JPATH_PLATFORM') or die;

/**
 * DocumentRAW class, provides an easy interface to parse and display raw output
 *
 * @package		Joomla.Platform
 * @subpackage	Document
 * @since		1.5
 */

jimport('joomla.document.document');

class JDocumentRAW extends JDocument
{

	/**
	 * Class constructore
	 *
	 * @access protected
	 * @param	array	$options Associative array of options
	 */
	function __construct($options = array())
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
	 * @access public
	 * @param boolean	$cache		If true, cache the output
	 * @param array		$params		Associative array of attributes
	 * @return	The rendered data
	 */
	function render($cache = false, $params = array())
	{
		parent::render();
		return $this->getBuffer();
	}
}
