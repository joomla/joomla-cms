<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Updater
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Update class.
 *
 * @package     Joomla.Platform
 * @subpackage  Update
 * @since       11.1
 */
class JUpdate extends JObject
{
	/**
	 *
	 * @var   string
	 * @since 11.1
	 */
	protected $name;

	/**
	 *
	 * @var   string
	 * @since 11.1
	 */
	protected $description;

	/**
	 *
	 * @var   string
	 * @since 11.1
	 */
	protected $element;

	/**
	 *
	 * @var   string
	 * @since 11.1
	 */
	protected $type;

	/**
	 *
	 * @var   string
	 * @since 11.1
	 */
	protected $version;

	/**
	 *
	 * @var   string
	 * @since 11.1
	 */
	protected $infourl;

	/**
	 *
	 * @var   string
	 * @since 11.1
	 */
	protected $client;

	/**
	 *
	 * @var   string
	 * @since 11.1
	 */
	protected $group;

	/**
	 *
	 * @var   string
	 * @since 11.1
	 */
	protected $downloads;

	/**
	 *
	 * @var   string
	 * @since 11.1
	 */
	protected $tags;

	/**
	 *
	 * @var   string
	 * @since 11.1
	 */
	protected $maintainer;

	/**
	 *
	 * @var   string
	 * @since 11.1
	 */
	protected $maintainerurl;

	/**
	 *
	 * @var   string
	 * @since 11.1
	 */
	protected $category;

	/**
	 *
	 * @var   string
	 * @since 11.1
	 */
	protected $relationships;

	/**
	 *
	 * @var   string
	 * @since 11.1
	 */
	protected $targetplatform;

	/**
	 *
	 * @var   string
	 * @since 11.1
	 */
	protected $_xml_parser;

	/**
	 *
	 * @var   array
	 * @since 11.1
	 */
	protected $_stack = Array('base');

	/**
	 *
	 * @var   array
	 * @since 11.1
	 */
	protected $_state_store = Array();

	/**
	 * Gets the reference to the current direct parent
	 *
	 * @return  object
	 * @since   11.1
	 */
	protected function _getStackLocation()
	{
			return implode('->', $this->_stack);
	}

	/**
	 * Get the last position in stack count
	 *
	 * @return  string
	 * @since   11.1
	 */
	protected function _getLastTag()
	{
		return $this->_stack[count($this->_stack) - 1];
	}

	/**
	 * XML Start Element callback
	 * @note  This is public because it is called externally
	 *
	 * @param   object  $parser  Parser object
	 * @param   string  $name    Name of the tag found
	 * @param   array   $attrs   Attributes of the tag
	 *
	 * @since   11.1
	 *
	 */
	public function _startElement($parser, $name, $attrs = Array())
	{
		array_push($this->_stack, $name);
		$tag = $this->_getStackLocation();
		// Reset the data
		eval('$this->'. $tag .'->_data = "";');

		switch($name) {
			// This is a new update; create a current update
			case 'UPDATE':
				$this->_current_update = new stdClass;
				break;
			// Don't do anything
			case 'UPDATES':
				break;
			// For everything else there's...the default!
			default:
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
	 * @note This is public because it is called externally
	 *
	 * @param  object  $parser  Parser object
	 * @param  string  $name    Name of element that was closed
	 *
	 * @since  11.1
	 */
	public function _endElement($parser, $name)
	{
		array_pop($this->_stack);
		switch($name)
		{
			// Closing update, find the latest version and check
			case 'UPDATE':
				$ver = new JVersion;
				$product = strtolower(JFilterInput::getInstance()->clean($ver->PRODUCT, 'cmd'));
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
					// The update might be for an older version of j!
					unset($this->_current_update);
				}
				break;
		}
	}

	/**
	 * Character Parser Function
	 * @note This is public because its called externally
	 *
	 * @param   object  $data
	 * @param   object  $parser  Parser object
	 *
	 * @since   11.1
	 *
	 */
	public function _characterData($parser, $data) {
		$tag = $this->_getLastTag();
		//if(!isset($this->$tag->_data)) $this->$tag->_data = '';
		//$this->$tag->_data .= $data;
		// Throw the data for this item together
		$tag = strtolower($tag);
		$this->_current_update->$tag->_data .= $data;
	}
	/**
	 *
	 *
	 * @param   string     $url
	 *
	 * @return  boolean  True on success
	 * @since   11.1
	 *
	 */
	public function loadFromXML($url)
	{
		if (!($fp = @fopen($url, "r")))
		{
			// TODO: Add a 'mark bad' setting here somehow
			JError::raiseWarning('101', JText::sprintf('JLIB_UPDATER_ERROR_EXTENSION_OPEN_URL', $url));
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