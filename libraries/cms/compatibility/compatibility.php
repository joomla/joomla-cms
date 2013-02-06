<?php
/**
 * @package     Joomla.Libraries
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Static class to handle loading of CMS libraries.
 *
 * @package  Joomla.Libraries
 * @link     https://github.com/joomla/joomla-cms/issues/598
 * @since    3.0.3
 */
class JCompatibility extends JLoader
{
	protected $compatibilities;

	/**
	 * Class constructor.
	 *
	 * @param   SimpleXMLElement  $compatibilities  The <compatibilities> node.
	 *
	 * @return  void
	 *
	 * @since   3.0.3
	 */
	public function __construct(SimpleXMLElement $compatibilities)
	{
		$this->compatibilities = $compatibilities;
	}

	/**
	 * Checks a version with a context.
	 *
	 * The way this method reads is:
	 *
	 * "Check the compatibilty of version, $version, with the context, $with". For example:
	 * "Check the compatibilty of version, '3.0.3' with 'joomla'."
	 *
	 * @param   string  $version  The version number to compare against the compatibility specification.
	 * @param   string  $with     The context of what we are testing compatibility with, for example "joomla".
	 *
	 * @return
	 *
	 * @since   3.0.3
	 */
	public function check($version, $with)
	{

	}

	protected function checkRules(SimpleXMLElement $rules, $version)
	{

	}

}
