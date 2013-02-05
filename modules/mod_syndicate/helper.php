<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_syndicate
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class modSyndicateHelper
{
	static function getLink(&$params)
	{
		$document = JFactory::getDocument();

		foreach($document->_links as $link => $value)
		{
			$value = JArrayHelper::toString($value);
			if (strpos($value, 'application/'.$params->get('format').'+xml')) {
				return $link;
			}
		}

	}
}
