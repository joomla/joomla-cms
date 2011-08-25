<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Updater
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.updater.updateadapter');
/**
 * Extension class for updater
 *
 * @package     Joomla.Platform
 * @subpackage  Updater
 * @since       11.1
 * */
class JUpdaterExtension extends JUpdateAdapter
{
	/**
	 * Start element parser callback.
	 *
	 * @param   object  $parser  The parser object.
	 * @param   string  $name    The name of the element.
	 * @param   array   $attrs   The attributes of the element.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function _startElement($parser, $name, $attrs = Array())
	{
		array_push($this->_stack, $name);
		$tag = $this->_getStackLocation();
		// reset the data
		eval('$this->' . $tag . '->_data = "";');

		switch ($name)
		{
			case 'UPDATE':
				$this->current_update = JTable::getInstance('update');
				$this->current_update->update_site_id = $this->_update_site_id;
				$this->current_update->detailsurl = $this->_url;
				break;
			// Don't do anything
			case 'UPDATES':
				break;
			default:
				if (in_array($name, $this->_updatecols))
				{
					$name = strtolower($name);
					$this->current_update->$name = '';
				}
				if ($name == 'TARGETPLATFORM')
				{
					$this->current_update->targetplatform = $attrs;
				}
				break;
		}
	}

	/**
	 * Character Parser Function
	 *
	 * @param   object  $parser  Parser object.
	 * @param   object  $name    The name of the element.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function _endElement($parser, $name)
	{
		array_pop($this->_stack);
		//echo 'Closing: '. $name .'<br />';
		switch ($name)
		{
			case 'UPDATE':
				$ver = new JVersion;
				$product = strtolower(JFilterInput::getInstance()->clean($ver->PRODUCT, 'cmd')); // lower case and remove the exclamation mark
				// Check that the product matches and that the version matches (optionally a regexp)
				if ($product == $this->current_update->targetplatform['NAME']
					&& preg_match('/' . $this->current_update->targetplatform['VERSION'] . '/', $ver->RELEASE))
				{
					// Target platform isn't a valid field in the update table so unset it to prevent J! from trying to store it
					unset($this->current_update->targetplatform);
					if (isset($this->latest))
					{
						if (version_compare($this->current_update->version, $this->latest->version, '>') == 1)
						{
							$this->latest = $this->current_update;
						}
					}
					else
					{
						$this->latest = $this->current_update;
					}
				}
				break;
			case 'UPDATES':
			// :D
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
	protected function _characterData($parser, $data)
	{
		$tag = $this->_getLastTag();
		//if(!isset($this->$tag->_data)) $this->$tag->_data = '';
		//$this->$tag->_data .= $data;
		if (in_array($tag, $this->_updatecols) || $tag == 'INFOURL')
		{
			$tag = strtolower($tag);
			$this->current_update->$tag .= $data;
		}
	}

	/**
	 * Finds an update.
	 *
	 * @param   array  $options
	 *
	 * @return  array  Array containing the array of update sites and array of updates
	 *
	 * @since   11.1
	 */
	public function findUpdate($options)
	{
		$url = $options['location'];
		$this->_url = &$url;
		$this->_update_site_id = $options['update_site_id'];
		//echo '<p>Find update for extension run on <a href="'. $url .'">'. $url .'</a></p>';
		if (substr($url, -4) != '.xml')
		{
			if (substr($url, -1) != '/')
			{
				$url .= '/';
			}
			$url .= 'extension.xml';
		}

		$dbo = $this->parent->getDBO();

		if (!($fp = @fopen($url, "r")))
		{
			$query = $dbo->getQuery(true);
			$query->update('#__update_sites');
			$query->set('enabled = 0');
			$query->where('update_site_id = ' . $this->_update_site_id);
			$dbo->setQuery($query);
			$dbo->Query();

			JLog::add("Error opening url: ".$url, JLog::WARNING, 'updater');
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('JLIB_UPDATER_ERROR_EXTENSION_OPEN_URL', $url), 'warning');
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
				JLog::add("Error parsing url: ".$url, JLog::WARNING, 'updater');
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::sprintf('JLIB_UPDATER_ERROR_EXTENSION_PARSE_URL', $url), 'warning');
				return false;
			}
		}
		xml_parser_free($this->xml_parser);
		if (isset($this->latest))
		{
			$updates = Array($this->latest);
		}
		else
		{
			$updates = Array();
		}
		return Array('update_sites' => Array(), 'updates' => $updates);
	}
}
