<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_syndicate
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Helper for mod_syndicate
 *
 * @since  1.5
 */
class ModSyndicateHelper
{
	/**
	 * Gets the link
	 *
	 * @param   \Joomla\Registry\Registry  &$params  module parameters
	 *
	 * @return  array  The link as a string
	 *
	 * @since   1.5
	 */
	public static function getLink(&$params)
	{
		$document = JFactory::getDocument();

		foreach ($document->_links as $link => $value)
		{
			$value = ArrayHelper::toString($value);

			if (strpos($value, 'application/' . $params->get('format') . '+xml'))
			{
				return $link;
			}
		}
	}
}
