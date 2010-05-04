<?php

// No direct access
defined('JPATH_BASE') or die();

jimport('joomla.base.adapterinstance');

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
