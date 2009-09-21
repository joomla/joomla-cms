<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.database.tableasset');

/**
 * Module table
 *
 * @package 	Joomla.Framework
 * @subpackage	Table
 * @since		1.0
 */
class JTableModule extends JTable
{
	/** @var int Primary key */
	var $id					= null;
	/** @var string */
	var $title				= null;
	/** @var string */
	var $showtitle			= null;
	/** @var int */
	var $content			= null;
	/** @var int */
	var $ordering			= null;
	/** @var string */
	var $position			= null;
	/** @var boolean */
	var $checked_out		= 0;
	/** @var time */
	var $checked_out_time	= 0;
	/** @var boolean */
	var $published			= null;
	/** @var string */
	var $module				= null;
	/** @var int */
	var $numnews			= null;
	/** @var int */
	var $access				= null;
	/** @var string */
	var $params				= null;
	/** @var string */
	var $iscore				= null;
	/** @var string */
	var $client_id			= null;
	/** @var string */
	var $control				= null;

	/**
	 * Contructore
	 *
	 * @access protected
	 * @param database A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__modules', 'id', $db);

		$this->access	= (int)JFactory::getConfig()->getValue('access');
	}

	/**
	 * Method to return the access section name for the asset table.
	 *
	 * @access	public
	 * @return	string
	 * @since	1.6
	 */
	function getAssetSection()
	{
		return 'core';
	}

	/**
	 * Method to return the name prefix to use for the asset table.
	 *
	 * @access	public
	 * @return	string
	 * @since	1.6
	 */
	function getAssetNamePrefix()
	{
		return 'module';
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @access	public
	 * @return	string
	 * @since	1.0
	 */
	function getAssetTitle()
	{
		return $this->title;
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
		if (trim($this->title) == '') {
			$this->setError(JText::sprintf('must contain a title', JText::_('Module')));
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
		if (is_array($array['params']))
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
}
