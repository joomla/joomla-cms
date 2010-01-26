<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Newsfeeds
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	Newsfeeds
 */
class NewsfeedsTableNewsfeed extends JTable
{
	/**
	 * @var int Primary key
	 */
	public $id = null;

	/**
	 * @var int
	 */
	public $catid = null;

	/**
	 * @var string
	 */
	public $name = null;

	/**
	 * @var string
	 */
	public $alias = null;

	/**
	 * @var string
	 */
	public $link = null;

	/**
	 * @var string
	 */
	public $filename = null;

	/**
	 * @var int
	 */
	public $published = null;

	/**
	 * @var int
	 */
	public $numarticles = null;

	/**
	 * @var int
	 */
	public $cache_time = null;

	/**
	 * @var int
	 */
	public $checked_out = 0;

	/**
	 * @var time
	 */
	public $checked_out_time = 0;

	/**
	 * @var int
	 */
	public $ordering = null;

	/**
	 * @var int
	 */
	public $rtl = 0;

	/**
	 * @var int
	 */
	public $access = 0;

	/**
	 * @var string
	 */
	public $language = 'en-GB';

	/**
	 * Constructor
	 *
	 * @param JDatabase A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__newsfeeds', 'id', $db);
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param	array		Named array
	 * @return	null|string	null is operation was satisfactory, otherwise returns an error
	 * @see JTable:bind
	 * @since 1.5
	 */
	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = (string)$registry;
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check method to ensure data integrity.
	 *
	 * @return	boolean	True on success.
	 */
	function check()
	{
		if (empty($this->alias)) {
			$this->alias = $this->name;
		}
		$this->alias = JApplication::stringURLSafe($this->alias);
		if (trim(str_replace('-','',$this->alias)) == '')
		{
			$this->alias = JFactory::getDate()->toFormat("%Y-%m-%d-%H-%M-%S");
		}

		return true;
	}
}
