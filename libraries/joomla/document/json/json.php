<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Document
* @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * JDocumentJSON class, provides an easy interface to parse and display JSON output
 *
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.6
 */

jimport('joomla.document.document');

class JDocumentJSON extends JDocument
{
	/**
	 * Document name
	 *
	 * @var		string
	 * @access  protected
	 */
	protected $_name = 'joomla';

	/**
	 * Class constructor
	 *
	 * @access public
	 * @param	array	$options Associative array of options
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		//set mime type
		$this->_mime = 'application/json';

		//set document type
		$this->_type = 'json';
	}

	/**
	 * Render the document.
	 *
	 * @access public
	 * @param boolean	$cache		If true, cache the output
	 * @param array	$params		Associative array of attributes
	 * @return	The rendered data
	 */
	public function render($cache = false, $params = array())
	{
		JResponse::allowCache(false);
		JResponse::setHeader('Content-disposition', 'attachment; filename="'.$this->getName().'.json"', true);

		parent::render();

		return $this->getBuffer();
	}

	/**
	 * Get the document head data
	 *
	 * @access	public
	 * @return	array	The document head data in array form
	 */
	public function getHeadData()
	{
	}

	/**
	 * Set the document head data
	 *
	 * @access	public
	 * @param	array	$data	The document head data in array form
	 */
	public function setHeadData($data)
	{
	}

	/**
	 * Returns the document name
	 *
	 * @access public
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 * Sets the document name
	 *
	 * @param	string	$name	Document name
	 * @access  public
	 * @return  void
	 */
	public function setName($name = 'joomla') {
		$this->_name = $name;
	}
}
