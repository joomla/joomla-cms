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
	 * @param   string  $checkVersion  The version number to compare against the compatibility specification.
	 * @param   string  $with          The context of what we are testing compatibility with, for example "joomla".
	 *
	 * @return
	 *
	 * @since   3.0.3
	 * @throws  InvalidArgumentException if compatibility nodes are not found, or the 'with' argument does not match.
	 */
	public function check($checkVersion, $with = 'joomla')
	{
		$with = strtolower($with);
		$node = $this->compatibilities->xpath("compatibility[@with='$with']");

		if (empty($node))
		{
			throw new InvalidArgumentException(sprintf('Compatibility with "%s" not found.', $with));
		}

		$include = $this->checkRules($node[0]->include, $checkVersion);
		$exclude = $this->checkRules($node[0]->exclude, $checkVersion);

		return (boolean) ($include ^ $exclude);
	}

	/**
	 * Checks a node with a collection <versions> tags against the check-version.
	 *
	 * If any matches are found, it will return true.
	 *
	 * @param   SimpleXMLElement  $rules
	 * @param   string            $checkVersion
	 *
	 * @return  boolean
	 *
	 * @since   3.0.3
	 */
	protected function checkRules(SimpleXMLElement $rules, $checkVersion)
	{
		$results = array();
		$versions = $rules->xpath('versions');

		foreach ($versions as $version)
		{
			$from = (string) $version['from'];
			$to = (string) $version['to'];

			if ($from && $to)
			{
				$results[] = version_compare($checkVersion, $from, 'ge') & version_compare($checkVersion, $to, 'le');
			}
			else
			{
				if ($from)
				{
					$results[] = version_compare($checkVersion, $from, 'ge');
				}
				else
				{
					$results[] = version_compare($checkVersion, $to, 'le');
				}
			}
		}

		return in_array(true, $results);
	}

}
