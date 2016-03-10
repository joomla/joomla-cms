<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Updater
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.updater.updateadapter');

/**
 * Extension class for updater
 *
 * @since  11.1
 */
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
		if (isset($this->$tag))
		{
			$this->$tag->_data = "";
		}

		switch ($name)
		{
			case 'UPDATE':
				$this->currentUpdate = JTable::getInstance('update');
				$this->currentUpdate->update_site_id = $this->updateSiteId;
				$this->currentUpdate->detailsurl = $this->_url;
				$this->currentUpdate->folder = "";
				$this->currentUpdate->client_id = 1;
				break;

			// Don't do anything
			case 'UPDATES':
				break;

			default:
				if (in_array($name, $this->updatecols))
				{
					$name = strtolower($name);
					$this->currentUpdate->$name = '';
				}

				if ($name == 'TARGETPLATFORM')
				{
					$this->currentUpdate->targetplatform = $attrs;
				}

				if ($name == 'PHP_MINIMUM')
				{
					$this->currentUpdate->php_minimum = '';
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
				$product = strtolower(JFilterInput::getInstance()->clean($ver::PRODUCT, 'cmd'));

				/*
				 * Check that the product matches and that the version matches (optionally a regexp)
				 *
				 * NOTE: Support for the min_dev_level and max_dev_level attributes is deprecated, a regexp should be
				 * used instead
				 *
				 * Check for optional min_dev_level and max_dev_level attributes to further specify targetplatform (e.g., 3.0.1)
				 */
				if ($product == $this->currentUpdate->targetplatform['NAME']
					&& preg_match('/^' . $this->currentUpdate->targetplatform['VERSION'] . '/', JVERSION)
					&& ((!isset($this->currentUpdate->targetplatform->min_dev_level)) || $ver::DEV_LEVEL >= $this->currentUpdate->targetplatform->min_dev_level)
					&& ((!isset($this->currentUpdate->targetplatform->max_dev_level)) || $ver::DEV_LEVEL <= $this->currentUpdate->targetplatform->max_dev_level))
				{
					// Check if PHP version supported via <php_minimum> tag, assume true if tag isn't present
					if (!isset($this->currentUpdate->php_minimum) || version_compare(PHP_VERSION, $this->currentUpdate->php_minimum, '>='))
					{
						$phpMatch = true;
					}
					else
					{
						// Notify the user of the potential update
						$msg = JText::sprintf(
							'JLIB_INSTALLER_AVAILABLE_UPDATE_PHP_VERSION',
							$this->currentUpdate->name,
							$this->currentUpdate->version,
							$this->currentUpdate->php_minimum,
							PHP_VERSION
						);

						JFactory::getApplication()->enqueueMessage($msg, 'warning');

						$phpMatch = false;
					}

					// Check minimum stability
					$stabilityMatch = true;

					if (isset($this->currentUpdate->stability) && ($this->currentUpdate->stability < $this->minimum_stability))
					{
						$stabilityMatch = false;
					}

					// Some properties aren't valid fields in the update table so unset them to prevent J! from trying to store them
					unset($this->currentUpdate->targetplatform);

					if (isset($this->currentUpdate->php_minimum))
					{
						unset($this->currentUpdate->php_minimum);
					}

					if (isset($this->currentUpdate->stability))
					{
						unset($this->currentUpdate->stability);
					}

					// If the PHP version and minimum stability checks pass, consider this version as a possible update
					if ($phpMatch && $stabilityMatch)
					{
						if (isset($this->latest))
						{
							// We already have a possible update. Check the version.
							if (version_compare($this->currentUpdate->version, $this->latest->version, '>') == 1)
							{
								$this->latest = $this->currentUpdate;
							}
						}
						else
						{
							// We don't have any possible updates yet, assume this is an available update.
							$this->latest = $this->currentUpdate;
						}
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

		if (in_array($tag, $this->updatecols))
		{
			$tag = strtolower($tag);
			$this->currentUpdate->$tag .= $data;
		}

		if ($tag == 'PHP_MINIMUM')
		{
			$this->currentUpdate->php_minimum = $data;
		}

		if ($tag == 'TAG')
		{
			$this->currentUpdate->stability = $this->stabilityTagToInteger((string) $data);
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
		$response = $this->getUpdateSiteResponse($options);

		if ($response === false)
		{
			return false;
		}

		if (array_key_exists('minimum_stability', $options))
		{
			$this->minimum_stability = $options['minimum_stability'];
		}

		$this->xmlParser = xml_parser_create('');
		xml_set_object($this->xmlParser, $this);
		xml_set_element_handler($this->xmlParser, '_startElement', '_endElement');
		xml_set_character_data_handler($this->xmlParser, '_characterData');

		if (!xml_parse($this->xmlParser, $response->body))
		{
			// If the URL is missing the .xml extension, try appending it and retry loading the update
			if (!$this->appendExtension && (substr($this->_url, -4) != '.xml'))
			{
				$options['append_extension'] = true;

				return $this->findUpdate($options);
			}

			JLog::add("Error parsing url: " . $this->_url, JLog::WARNING, 'updater');

			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('JLIB_UPDATER_ERROR_EXTENSION_PARSE_URL', $this->_url), 'warning');

			return false;
		}

		xml_parser_free($this->xmlParser);

		if (isset($this->latest))
		{
			if (isset($this->latest->client) && strlen($this->latest->client))
			{
				if (is_numeric($this->latest->client))
				{
					$byName = false;

					// <client> has to be 'administrator' or 'site', numeric values are deprecated. See https://docs.joomla.org/Design_of_JUpdate
					JLog::add(
						'Using numeric values for <client> in the updater xml is deprecated. Use \'administrator\' or \'site\' instead.',
						JLog::WARNING, 'deprecated'
					);
				}
				else
				{
					$byName = true;
				}

				$this->latest->client_id = JApplicationHelper::getClientInfo($this->latest->client, $byName)->id;
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

	/**
	 * Converts a tag to numeric stability representation. If the tag doesn't represent a known stability level (one of
	 * dev, alpha, beta, rc, stable) it is ignored.
	 *
	 * @param   string  $tag  The tag string, e.g. dev, alpha, beta, rc, stable
	 *
	 * @return  integer
	 *
	 * @since   3.4
	 */
	protected function stabilityTagToInteger($tag)
	{
		$constant = 'JUpdater::STABILITY_' . strtoupper($tag);

		if (defined($constant))
		{
			return constant($constant);
		}

		return JUpdater::STABILITY_STABLE;
	}
}
