<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.dontTranslate
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Plugin to enable adding the translate HTML5 attribute
 * This uses the {dontTranslate text} syntax
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemDontTranslate extends CMSPlugin
{
	/**
	 * Plugin that adds the translate="no" attribute
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onAfterRender() : void
	{
		if (!Factory::getApplication()->isClient('site'))
		{
			return;
		}

		$body = Factory::getApplication()->getBody();

		// Expression to search for {dontTranslate}
		$regex = '/{dontTranslate\s(.*?)}/i';

		// Find all instances of plugin and put in $matches for dontTranslate
		// $matches[0] is full pattern match, $matches[1] is the position
		preg_match_all($regex, $body, $matches, PREG_SET_ORDER);

		// No matches, skip this
		if ($matches)
		{
			foreach ($matches as $match)
			{
				$matcheslist = explode(',', $match[1]);

				$text = $matcheslist[0];

				$output = '<span translate="no">' . $text . '</span>';

				$body = preg_replace("|$match[0]|", addcslashes($output, '\\$'), $body, 1);
				Factory::getApplication()->setBody($body);
			}
		}
	}
}
