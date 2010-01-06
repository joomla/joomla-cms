<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Update
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// No direct access
defined('JPATH_BASE') or die();

/**
 * Update class.
 *
 * @package 	Joomla.Framework
 * @subpackage	Update
 * @since		1.6
 */
class JUpdate extends JObject
{
	protected $name;
	protected $description;
	protected $element;
	protected $type;
	protected $version;
	protected $infourl;
	protected $client;
	protected $group;
	protected $downloads;
	protected $tags;
	protected $maintainer;
	protected $maintainerurl;
	protected $category;
	protected $relationships;
	protected $targetplatform;

	private $_xml_parser;
	private $_stack = Array('base');
	private $_state_store = Array();

	/**
     * Gets the reference to the current direct parent
     *
     * @return object
     */
	protected function _getStackLocation()
    {
            return implode('->', $this->_stack);
    }

    /**
     * Get the last position in stack count
     *
     * @return string
     */
    protected function _getLastTag()
    {
    	return $this->_stack[count($this->_stack) - 1];
    }


    /**
     * XML Start Element callback
     * Note: This is public because it is called externally
     * @param object parser object
     * @param string name of the tag found
     * @param array attributes of the tag
     */
	public function _startElement($parser, $name, $attrs = Array())
	{
		array_push($this->_stack, $name);
		$tag = $this->_getStackLocation();
		// reset the data
		eval('$this->'. $tag .'->_data = "";');
		//echo 'Opened: '; print_r($this->_stack); echo '<br />';
		//print_r($attrs); echo '<br />';
		switch($name) {
			case 'UPDATE': // This is a new update; create a current update
				$this->_current_update = new stdClass();
				break;
			case 'UPDATES': // don't do anything
				break;
			default: // for everything else there's...the default!
				$name = strtolower($name);
				$this->_current_update->$name->_data = '';
				foreach($attrs as $key=>$data) {
					$key = strtolower($key);
					$this->_current_update->$name->$key = $data;
				}
				break;
		}
	}

	/**
	 * Callback for closing the element
	 * Note: This is public because it is called externally
	 * @param object parser object
	 * @param string name of element that was closed
	 */
	public function _endElement($parser, $name)
	{
		array_pop($this->_stack);
		switch($name)
		{
			case 'UPDATE': // closing update, find the latest version and check
				$ver = new JVersion();
				$product = strtolower(JFilterInput::clean($ver->PRODUCT, 'cmd'));
				if($product == $this->_current_update->targetplatform->name && $ver->RELEASE == $this->_current_update->targetplatform->version)
				{
					if(isset($this->_latest))
					{
						if(version_compare($this->_current_update->version->_data, $this->_latest->version->_data, '>') == 1) {
							$this->_latest = $this->_current_update;
						}
					}
					else {
						$this->_latest = $this->_current_update;
					}
				}
				break;
			case 'UPDATES':
				// If the latest item is set then we transfer it to where we want to
				if(isset($this->_latest))
				{
					foreach(get_object_vars($this->_latest) as $key=>$val) {
						$this->$key = $val;
					}
					unset($this->_latest);
					unset($this->_current_update);
				}
				else if(isset($this->_current_update))
				{
					// the update might be for an older version of j!
					unset($this->_current_update);
				}
				break;
		}
	}

	/**
	 * Character Parser Function
	 * Note: This is public because its called externally
	 */
	public function _characterData($parser, $data) {
		$tag = $this->_getLastTag();
		//if(!isset($this->$tag->_data)) $this->$tag->_data = '';
		//$this->$tag->_data .= $data;
		// Throw the data for this item together
		$tag = strtolower($tag);
		$this->_current_update->$tag->_data .= $data;
	}

	public function loadFromXML($url)
	{
		if (!($fp = @fopen($url, "r")))
		{
			// TODO: Add a 'mark bad' setting here somehow
		    JError::raiseWarning('101', JText::_('Update') .'::'. JText::_('Extension') .': '. JText::_('Could not open').' '. $url);
		    return false;
		}

		$this->xml_parser = xml_parser_create('');
		xml_set_object($this->xml_parser, $this);
		xml_set_element_handler($this->xml_parser, '_startElement', '_endElement');
		xml_set_character_data_handler($this->xml_parser, '_characterData');

		while ($data = fread($fp, 8192))
		{
		    if (!xml_parse($this->xml_parser, $data, feof($fp)))
		    {
		        die(sprintf("XML error: %s at line %d",
		                    xml_error_string(xml_get_error_code($this->xml_parser)),
		                    xml_get_current_line_number($this->xml_parser)));
		    }
		}
		xml_parser_free($this->xml_parser);
		return true;
	}
}