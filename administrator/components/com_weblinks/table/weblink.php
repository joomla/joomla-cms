<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Weblink Table class
 *
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 * @since       1.5
 */
class WeblinksTableWeblink extends JTableCms
{
	/**
	 * Constructor
	 */
	public function __construct($config = array())
	{
		$config['table']['name'] = '#__weblinks';
		$config['table']['key'] = 'id';
		parent::__construct($config);
	}
	
	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param   mixed  $array   An associative array or object to bind to the JTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JTable:bind
	 * @since   1.5
	 */
	public function bind($src, $ignore = '')
	{
		if (isset($src['images']) && is_array($src['images']))
		{
			$registry = new JRegistry;
			$registry->loadArray($src['images']);
			$src['images'] = (string) $registry;
		}
	
		return parent::bind($src, $ignore);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see JTableCms::getReorderConditions()
	 */
	protected function getReorderConditions($table)
	{
		$condition = array();
		$condition[] = 'catid = ' . (int) $table->catid;
	
		return $condition;
	}
	
	
}