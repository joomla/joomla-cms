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
	/**
	 * @var int Primary key
	 */
	public $id = null;
	/**
	 * @var string
	 */
	public $title = null;
	/**
	 * @var string
	 */
	public $showtitle = null;
	/**
	 * @var int
	 */
	public $content = null;
	/**
	 * @var int
	 */
	public $ordering = null;
	/**
	 * @var string
	 */
	public $position = null;
	/**
	 * @var boolean
	 */
	public $checked_out = 0;
	/**
	 * @var time
	 */
	public $checked_out_time = 0;
	/**
	 * @var boolean
	 */
	public $published = null;
	/**
	 * @var string
	 */
	public $module = null;
	/**
	 * @var int
	 */
	public $access = null;
	/**
	 * @var string
	 */
	public $params = null;
	/**
	 * @var string
	 */
	public $iscore = null;
	/**
	 * @var string
	 */
	public $client_id = null;

	/**
	 * Contructor.
	 *
	 * @param database A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__modules', 'id', $db);

		$this->access = (int) JFactory::getConfig()->getValue('access');
	}

	/**
	 * Overloaded check function.
	 *
	 * @return	boolean	True if the object is ok
	 */
	public function check()
	{
		// check for valid name
		if (trim($this->title) == '')
		{
			$this->setError(JText::sprintf('must contain a title', JText::_('Module')));
			return false;
		}

		return true;
	}

	/**
	 * Overloaded bind function.
	 *
	 * @param	array		named array
	 * @return	null|string	null is operation was satisfactory, otherwise returns an error
	 * @see		JTable:bind
	 * @since	1.5
	 */
	public function bind($array, $ignore = '')
	{
		if (is_array($array['params']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}

		return parent::bind($array, $ignore);
	}
}