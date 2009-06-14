<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.database.tablenested');

/**
 * Category table
 *
 * @package 	Joomla.Framework
 * @subpackage		Table
 * @since	1.0
 */
class JTableCategory extends JTableNested
{
	/** @var int Primary key */
	public $id					= null;
	/** @var int */
	public $lft					= null;
	/** @var int */
	public $rgt					= null;
	/** @var int */
	public $ref_id				= null;
	/** @var int */
	public $parent				= null;
	/** @var string */
	public $extension			= null;
	/** @var string */
	public $lang				= null;
	/** @var string The menu title for the category (a short name)*/
	public $title				= null;
	/** @var string The the alias for the category*/
	public $alias				= null;
	/** @var string */
	public $description			= null;
	/** @var boolean */
	public $published			= null;
	/** @var boolean */
	public $checked_out			= 0;
	/** @var time */
	public $checked_out_time	= null;
	/** @var int */
	public $access				= null;
	/** @var string */
	public $params				= '';
	/** @var boolean */
	protected $_trackAssets 	= true;

	/**
	* @param database A database connector object
	*/
	function __construct(&$db)
	{
		parent::__construct('#__categories', 'id', $db);

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
		return $this->extension;
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
		return 'category';
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
	 * @return boolean
	 * @see JTable::check
	 * @since 1.5
	 */
	function check()
	{
		// check for valid name
		if (trim($this->title) == '') {
			$this->setError(JText::sprintf('must contain a title', JText::_('Category')));
			return false;
		}

		// check for existing name
		if (empty($this->alias)) {
			$this->alias = $this->title;
		}
		$this->alias = JFilterOutput::stringURLSafe($this->alias);
		if (trim(str_replace('-','',$this->alias)) == '') {
			$datenow = &JFactory::getDate();
			$this->alias = $datenow->toFormat("%Y-%m-%d-%H-%M-%S");
		}

		return true;
	}
}
