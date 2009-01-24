<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * @package		Joomla
 * @subpackage	Admin
 */
class AdminModelChangelog extends JModel
{
	/**
	 * Changelog data
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct(array('name' => 'changelog'));
	}

	/**
	 * Method to get a currency
	 */
	function &getChangelog()
	{
		// Load the changelog
		if (!$this->_loadData())
			$this->_initData();

		return $this->_data;
	}

	/**
	 * Method to load currency data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 */
	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (!$this->_data)
		{
			ob_start();
			readfile(JPATH_SITE.DS.'CHANGELOG.php');
			$this->_data = ob_get_contents();
			ob_clean();

			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to initialise the data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 */
	function _initData()
	{
		if (empty($this->_data))
		{
			$this->_data = null;
			return (boolean) $this->_data;
		}
		return true;
	}
}