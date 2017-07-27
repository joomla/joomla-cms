<?php
/**
 *  @package     FrameworkOnFramework
 *  @subpackage  config
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 *  @license     GNU General Public License version 2, or later
 */

defined('FOF_INCLUDED') or die();

/**
 * Configuration parser for the dispatcher-specific settings
 *
 * @package  FrameworkOnFramework
 * @since    2.1
 */
class FOFConfigDomainDispatcher implements FOFConfigDomainInterface
{
	/**
	 * Parse the XML data, adding them to the $ret array
	 *
	 * @param   SimpleXMLElement  $xml   The XML data of the component's configuration area
	 * @param   array             &$ret  The parsed data, in the form of a hash array
	 *
	 * @return  void
	 */
	public function parseDomain(SimpleXMLElement $xml, array &$ret)
	{
		// Initialise
		$ret['dispatcher'] = array();

		// Parse the dispatcher configuration
		$dispatcherData = $xml->dispatcher;

		// Sanity check

		if (empty($dispatcherData))
		{
			return;
		}

		$options = $xml->xpath('dispatcher/option');

		if (!empty($options))
		{
			foreach ($options as $option)
			{
				$key = (string) $option['name'];
				$ret['dispatcher'][$key] = (string) $option;
			}
		}
	}

	/**
	 * Return a configuration variable
	 *
	 * @param   string  &$configuration  Configuration variables (hashed array)
	 * @param   string  $var             The variable we want to fetch
	 * @param   mixed   $default         Default value
	 *
	 * @return  mixed  The variable's value
	 */
	public function get(&$configuration, $var, $default)
	{
		if (isset($configuration['dispatcher'][$var]))
		{
			return $configuration['dispatcher'][$var];
		}
		else
		{
			return $default;
		}
	}
}
