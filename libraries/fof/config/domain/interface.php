<?php
/**
 *  @package     FrameworkOnFramework
 *  @subpackage  config
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 *  @license     GNU General Public License version 2, or later
 */

defined('FOF_INCLUDED') or die();

/**
 * The Interface of an FOFConfigDomain class. The methods are used to parse and
 * privision sensible information to consumers. FOFConfigProvider acts as an
 * adapter to the FOFConfigDomain classes.
 *
 * @package  FrameworkOnFramework
 * @since    2.1
 */
interface FOFConfigDomainInterface
{
	/**
	 * Parse the XML data, adding them to the $ret array
	 *
	 * @param   SimpleXMLElement  $xml   The XML data of the component's configuration area
	 * @param   array             &$ret  The parsed data, in the form of a hash array
	 *
	 * @return  void
	 */
	public function parseDomain(SimpleXMLElement $xml, array &$ret);

	/**
	 * Return a configuration variable
	 *
	 * @param   string  &$configuration  Configuration variables (hashed array)
	 * @param   string  $var             The variable we want to fetch
	 * @param   mixed   $default         Default value
	 *
	 * @return  mixed  The variable's value
	 */
	public function get(&$configuration, $var, $default);
}
