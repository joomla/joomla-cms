<?php
jimport('joomla.base.adapterinstance');

class JUpdateAdapter extends JAdapterInstance {
	var $xml_parser;
	var $_stack = Array('base');
	var $_update_site_id = 0;
	var $_updatecols = Array('NAME', 'ELEMENT', 'TYPE', 'FOLDER', 'CLIENT', 'VERSION', 'DESCRIPTION');

	/**
     * Gets the reference to the current direct parent
     *
     * @return object
     */
	function _getStackLocation()
    {
            return implode('->', $this->_stack);
    }

    function _getLastTag() {
    	return $this->_stack[count($this->_stack) - 1];
    }

}