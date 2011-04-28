<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Updater
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.base.adapterinstance');

/**
 * UpdateAdapter class.
 *
 * @package		Joomla.Platform
 * @subpackage	Update
 * @since		11.1
 */

class JUpdateAdapter extends JAdapterInstance {
	protected $xml_parser;
	protected $_stack = Array('base');
	protected $_update_site_id = 0;
	protected $_updatecols = Array('NAME', 'ELEMENT', 'TYPE', 'FOLDER', 'CLIENT', 'VERSION', 'DESCRIPTION');

	/**
	 * Gets the reference to the current direct parent
	 *
	 * @return object
	 */
	protected function _getStackLocation()
	{
			return implode('->', $this->_stack);
	}

	protected function _getLastTag() {
		return $this->_stack[count($this->_stack) - 1];
	}
}
