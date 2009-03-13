<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Table
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Extension table
 * Replaces plugins table
 *
 * @package 	Joomla.Framework
 * @subpackage		Table
 * @since	1.6
 */
class JTableExtension extends JTable
{
	/** @var int Primary key */
	protected $extension_id					= null;
	/** @var string Friendly name of the extension */
	protected $name				= null;
	/** @var string Type of Extension */
	protected $type			= null;
	/** @var string Unique name of extension */
	protected $element			= null;
	/** @var string Folder/container/group of extension */
	protected $folder			= null;
	/** @var int Client Unique Identifier (e.g. administrator=1, site=0) */
	protected $client_id			= null;
	/** @var boolean Published/Enabled state of the extension */
	protected $enabled			= 1;
	/** @var int Primitive Access Control */
	protected $access				= 0;
	/** @var int If the extension is included in the Core (2) or otherwise protected (1); default not protected (0)*/
	protected $protected			= 0;
	/** @var string Manifest Cache; cache of the manifest data */
	protected $manifest_cache		= null;
	/** @var string Extension parameters */
	protected $params				= null;
	/** @var string Generic extension data field; for extensions private use */
	protected $custom_data				= null;
	/** @var string Generic extension data field; for Joomla! use */
	protected $system_data				= null;
	/** @var int Checked Out */
	protected $checked_out = 0;
	/** @var datetime Checked Out Time */
	protected $checked_out_time = null;
	/** @var int ordering */
	protected $ordering = 0;
	/** @var int state State of the extension, either default (0), discovered (-1) */
	protected $state = 0;


	/**
	 * Contructor
	 *
	 * @access protected
	 * @param database A database connector object
	 */
	function __construct(&$db) {
		parent::__construct('#__extensions', 'extension_id', $db);
	}

	/**
	* Overloaded check function
	*
	* @access public
	* @return boolean True if the object is ok
	* @see JTable:bind
	*/
	function check()
	{
		// check for valid name
		if (trim($this->name) == '' || trim($this->element) == '') {
			$this->setError(JText::sprintf('must contain a title', JText::_('Extension')));
			return false;
		}
		return true;
	}

	/**
	* Overloaded bind function
	*
	* @access public
	* @param array $hash named array
	* @return null|string	null is operation was satisfactory, otherwise returns an error
	* @see JTable:bind
	* @since 1.5
	*/
	function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}

		if (isset($array['control']) && is_array($array['control']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['control']);
			$array['control'] = $registry->toString();
		}

		return parent::bind($array, $ignore);
	}

	function find($options=Array()) {
		$dbo =& JFactory::getDBO();
		$where = Array();
		foreach($options as $col=>$val) {
			$where[] = $col .' = '. $dbo->Quote($val);
		}
		$query = 'SELECT extension_id FROM #__extensions WHERE '. implode(' AND ', $where);
		$dbo->setQuery($query);
		try {
			return $dbo->loadResult();
		} catch(JException $e) {
			$this->setError($e, true);
			return false;
		}
	}
}
