<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Configuration\Domain;

defined('_JEXEC') || die;

use SimpleXMLElement;

/**
 * Configuration parser for the authentication-specific settings
 *
 * @since    2.1
 */
class Authentication implements DomainInterface
{
	/**
	 * Parse the XML data, adding them to the $ret array
	 *
	 * @param   SimpleXMLElement   $xml  The XML data of the component's configuration area
	 * @param   array             &$ret  The parsed data, in the form of a hash array
	 *
	 * @return  void
	 */
	public function parseDomain(SimpleXMLElement $xml, array &$ret)
	{
		// Initialise
		$ret['authentication'] = [];

		// Parse the dispatcher configuration
		$authenticationData = $xml->authentication;

		// Sanity check

		if (empty($authenticationData))
		{
			return;
		}

		$options = $xml->xpath('authentication/option');

		if (!empty($options))
		{
			foreach ($options as $option)
			{
				$key                         = (string) $option['name'];
				$ret['authentication'][$key] = (string) $option;
			}
		}
	}

	/**
	 * Return a configuration variable
	 *
	 * @param   string  &$configuration  Configuration variables (hashed array)
	 * @param   string   $var            The variable we want to fetch
	 * @param   mixed    $default        Default value
	 *
	 * @return  mixed  The variable's value
	 */
	public function get(&$configuration, $var, $default)
	{
		if ($var == '*')
		{
			return $configuration['authentication'];
		}

		if (isset($configuration['authentication'][$var]))
		{
			return $configuration['authentication'][$var];
		}
		else
		{
			return $default;
		}
	}
}
