<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Updater
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Update class.
 *
 * @package     Joomla.Platform
 * @subpackage  Updater
 * @since       11.1
 */
class JUpdate extends JObject
{
	/**
	 * @var    string
	 * @since  11.1
	 */
	protected $name;

	/**
	 * @var    string
	 * @since  11.1
	 */
	protected $description;

	/**
	 * @var    string
	 * @since  11.1
	 */
	protected $element;

	/**
	 * @var    string
	 * @since  11.1
	 */
	protected $type;

	/**
	 * @var    string
	 * @since  11.1
	 */
	protected $version;

	/**
	 * @var    string
	 * @since  11.1
	 */
	protected $infourl;

	/**
	 * @var    string
	 * @since  11.1
	 */
	protected $client;

	/**
	 * @var    string
	 * @since  11.1
	 */
	protected $group;

	/**
	 * @var    string
	 * @since  11.1
	 */
	protected $downloads;

	/**
	 * @var    string
	 * @since  11.1
	 */
	protected $tags;

	/**
	 * @var    string
	 * @since  11.1
	 */
	protected $maintainer;

	/**
	 * @var    string
	 * @since  11.1
	 */
	protected $maintainerurl;

	/**
	 * @var    string
	 * @since  11.1
	 */
	protected $category;

	/**
	 * @var    string
	 * @since  11.1
	 */
	protected $relationships;

	/**
	 * @var    string
	 * @since  11.1
	 */
	protected $targetplatform;

	/**
	 * @var    string
	 * @since  12.1
	 */
	protected $xmlParser;

	/**
	 * @var    array
	 * @since  12.1
	 */
	protected $stack = array('base');

	/**
	 * @var    array
	 * @since  12.1
	 */
	protected $stateStore = array();

	/**
	 * @var    stdClass
	 * @since  12.1
	 */
	protected $currentUpdate;

	/**
	 * @var    stdClass
	 * @since  12.1
	 */
	protected $latest;

	/**
	 * Gets the reference to the current direct parent
	 *
	 * @return  object
	 *
	 * @since   11.1
	 */
	protected function _getStackLocation()
	{
		return implode('->', $this->stack);
	}

	/**
	 * Get the last position in stack count
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	protected function _getLastTag()
	{
		return $this->stack[count($this->stack) - 1];
	}

	/**
	 * XML Start Element callback
	 *
	 * @param   object  $parser  Parser object
	 * @param   string  $name    Name of the tag found
	 * @param   array   $attrs   Attributes of the tag
	 *
	 * @return  void
	 *
	 * @note    This is public because it is called externally
	 * @since   11.1
	 */
	public function _startElement($parser, $name, $attrs = array())
	{
		array_push($this->stack, $name);
		$tag = $this->_getStackLocation();

		// Reset the data
		if (isset($this->$tag))
		{
			$this->$tag->_data = "";
		}

		switch ($name)
		{
			// This is a new update; create a current update
			case 'UPDATE':
				$this->currentUpdate = new stdClass;
				break;

			// Don't do anything
			case 'UPDATES':
				break;

			// For everything else there's...the default!
			default:
				$name = strtolower($name);

				if (!isset($this->currentUpdate->$name))
				{
					$this->currentUpdate->$name = new stdClass;
				}
				$this->currentUpdate->$name->_data = '';

				foreach ($attrs as $key => $data)
				{
					$key = strtolower($key);
					$this->currentUpdate->$name->$key = $data;
				}
				break;
		}
	}

	/**
	 * Callback for closing the element
	 *
	 * @param   object  $parser  Parser object
	 * @param   string  $name    Name of element that was closed
	 *
	 * @return  void
	 *
	 * @note This is public because it is called externally
	 * @since  11.1
	 */
	public function _endElement($parser, $name)
	{
		array_pop($this->stack);
		switch ($name)
		{
			// Closing update, find the latest version and check
			case 'UPDATE':
				$ver = new JVersion;
				$product = strtolower(JFilterInput::getInstance()->clean($ver->PRODUCT, 'cmd'));

				// Check for optional min_dev_level and max_dev_level attributes to further specify targetplatform (e.g., 3.0.1)
				if (isset($this->currentUpdate->targetplatform->name)
					&& $product == $this->currentUpdate->targetplatform->name
					&& preg_match('/' . $this->currentUpdate->targetplatform->version . '/', $ver->RELEASE)
					&& ((!isset($this->currentUpdate->targetplatform->min_dev_level)) || $ver->DEV_LEVEL >= $this->currentUpdate->targetplatform->min_dev_level)
					&& ((!isset($this->currentUpdate->targetplatform->max_dev_level)) || $ver->DEV_LEVEL <= $this->currentUpdate->targetplatform->max_dev_level))
				{
					if (isset($this->latest))
					{
						if (version_compare($this->currentUpdate->version->_data, $this->latest->version->_data, '>') == 1)
						{
							$this->latest = $this->currentUpdate;
						}
					}
					else
					{
						$this->latest = $this->currentUpdate;
					}
				}
				break;
			case 'UPDATES':
				// If the latest item is set then we transfer it to where we want to
				if (isset($this->latest))
				{
					foreach (get_object_vars($this->latest) as $key => $val)
					{
						$this->$key = $val;
					}
					unset($this->latest);
					unset($this->currentUpdate);
				}
				elseif (isset($this->currentUpdate))
				{
					// The update might be for an older version of j!
					unset($this->currentUpdate);
				}
				break;
		}
	}

	/**
	 * Character Parser Function
	 *
	 * @param   object  $parser  Parser object.
	 * @param   object  $data    The data.
	 *
	 * @return  void
	 *
	 * @note    This is public because its called externally.
	 * @since   11.1
	 */
	public function _characterData($parser, $data)
	{
		$tag = $this->_getLastTag();

		// @todo remove code: if(!isset($this->$tag->_data)) $this->$tag->_data = '';
		// @todo remove code: $this->$tag->_data .= $data;

		// Throw the data for this item together
		$tag = strtolower($tag);
		if (isset($this->currentUpdate->$tag))
		{
			$this->currentUpdate->$tag->_data .= $data;
		}
	}

	/**
	 * Loads an XML file from a URL.
	 *
	 * @param   string  $url  The URL.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public function loadFromXML($url)
	{
		$http = JHttpFactory::getHttp();
		$response = $http->get($url);
		if (200 != $response->code)
		{
			// TODO: Add a 'mark bad' setting here somehow
			JLog::add(JText::sprintf('JLIB_UPDATER_ERROR_EXTENSION_OPEN_URL', $url), JLog::WARNING, 'jerror');
			return false;
		}

		$this->xmlParser = xml_parser_create('');
		xml_set_object($this->xmlParser, $this);
		xml_set_element_handler($this->xmlParser, '_startElement', '_endElement');
		xml_set_character_data_handler($this->xmlParser, '_characterData');

		if (!xml_parse($this->xmlParser, $response->body))
		{
			die(
				sprintf(
					"XML error: %s at line %d", xml_error_string(xml_get_error_code($this->xmlParser)),
					xml_get_current_line_number($this->xmlParser)
				)
			);
		}
		xml_parser_free($this->xmlParser);
		return true;
	}
}
