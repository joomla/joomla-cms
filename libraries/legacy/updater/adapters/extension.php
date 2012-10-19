<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Updater
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
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
	protected function _startElement($parser, $name, $attrs = array())
	{
		array_push($this->stack, $name);
		$tag = $this->_getStackLocation();

		// Reset the data
		eval('$this->' . $tag . '->_data = "";');

		switch ($name)
		{
			case 'UPDATE':
				$this->current_update = JTable::getInstance('update');
				$this->current_update->update_site_id = $this->updateSiteId;
				$this->current_update->detailsurl = $this->_url;
				$this->current_update->folder = "";
				$this->current_update->client_id = 1;
				break;

			// Don't do anything
			case 'UPDATES':
				break;
			default:
				if (in_array($name, $this->updatecols))
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
		array_pop($this->stack);

		// @todo remove code: echo 'Closing: '. $name .'<br />';
		switch ($name)
		{
			case 'UPDATE':
				$ver = new JVersion;

				// Lower case and remove the exclamation mark
				$product = strtolower(JFilterInput::getInstance()->clean($ver->PRODUCT, 'cmd'));

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
		/**
		 * @todo remove code
		 * if(!isset($this->$tag->_data)) $this->$tag->_data = '';
		 * $this->$tag->_data .= $data;
		 */
		if (in_array($tag, $this->updatecols))
		{
			$tag = strtolower($tag);
			$this->current_update->$tag .= $data;
		}
	}

	/**
	 * Finds an update.
	 *
	 * @param   array  $options  Update options.
	 *
	 * @return  array  Array containing the array of update sites and array of updates
	 *
	 * @since   11.1
	 */
	public function findUpdate($options)
	{
		$url = $options['location'];
		$this->_url = &$url;
		$this->updateSiteId = $options['update_site_id'];
		if (substr($url, -4) != '.xml')
		{
			if (substr($url, -1) != '/')
			{
				$url .= '/';
			}
			$url .= 'extension.xml';
		}

		$dbo = $this->parent->getDBO();

		$http = JHttpFactory::getHttp();
		$response = $http->get($url);
		if (!empty($response->code) && 200 != $response->code)
		{
			$query = $dbo->getQuery(true);
			$query->update('#__update_sites');
			$query->set('enabled = 0');
			$query->where('update_site_id = ' . $this->updateSiteId);
			$dbo->setQuery($query);
			$dbo->execute();

			JLog::add("Error opening url: " . $url, JLog::WARNING, 'updater');
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('JLIB_UPDATER_ERROR_EXTENSION_OPEN_URL', $url), 'warning');
			return false;
		}

		$this->xmlParser = xml_parser_create('');
		xml_set_object($this->xmlParser, $this);
		xml_set_element_handler($this->xmlParser, '_startElement', '_endElement');
		xml_set_character_data_handler($this->xmlParser, '_characterData');

		if (!xml_parse($this->xmlParser, $response->body))
		{
			JLog::add("Error parsing url: " . $url, JLog::WARNING, 'updater');
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('JLIB_UPDATER_ERROR_EXTENSION_PARSE_URL', $url), 'warning');
			return false;
		}
		xml_parser_free($this->xmlParser);
		if (isset($this->latest))
		{
			if (isset($this->latest->client) && strlen($this->latest->client))
			{
				$this->latest->client_id = JApplicationHelper::getClientInfo($this->latest->client)->id;
				unset($this->latest->client);
			}
			$updates = array($this->latest);
		}
		else
		{
			$updates = array();
		}
		return array('update_sites' => array(), 'updates' => $updates);
	}
}
