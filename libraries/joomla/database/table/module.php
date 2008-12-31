<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Table
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// No direct access
defined('JPATH_BASE') or die();

require_once dirname(__FILE__).DS.'asset.php';

/**
 * Module table
 *
 * @package 	Joomla.Framework
 * @subpackage		Table
 * @since	1.0
 */
class JTableModule extends JTableAsset
{
	/** @var int Primary key */
	protected $id				= null;
	/** @var string */
	protected $title			= null;
	/** @var string */
	protected $showtitle		= null;
	/** @var int */
	protected $content			= null;
	/** @var int */
	protected $ordering			= null;
	/** @var string */
	protected $position			= null;
	/** @var boolean */
	protected $checked_out		= 0;
	/** @var time */
	protected $checked_out_time	= 0;
	/** @var boolean */
	protected $published		= null;
	/** @var string */
	protected $module			= null;
	/** @var int */
	protected $numnews			= null;
	/** @var int */
	protected $access			= null;
	/** @var string */
	protected $params			= null;
	/** @var string */
	protected $iscore			= null;
	/** @var string */
	protected $client_id		= null;
	/** @var string */
	protected $control			= null;

	/**
	 * Contructore
	 *
	 * @access protected
	 * @param database A database connector object
	 */
	protected function __construct(&$db)
	{
		parent::__construct('#__modules', 'id', $db);
	}

	/**
	 * Method to return the title of the object to insert into the AXO table
	 *
	 * @return	string
	 */
	protected function getAssetSection()
	{
		return 'com_modules';
	}

	/**
	 * Method to return the section of the object to insert into the AXO table
	 *
	 * @return	string
	 */
	protected function getAssetTitle()
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
	public function check()
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
	public function bind($array, $ignore = '')
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
