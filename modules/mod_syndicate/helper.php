<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_syndicate
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_syndicate
 *
 * @package     Joomla.Site
 * @subpackage  mod_syndicate
 * @since       1.5
 */
class ModSyndicateHelper
{
	public static function getLink(&$params)
	{
		$document = JFactory::getDocument();

		foreach ($document->_links as $link => $value)
		{
			$value = JArrayHelper::toString($value);

			if (strpos($value, 'application/' . $params->get('format') . '+xml'))
			{
				return $link;
			}
		}

	}
}
