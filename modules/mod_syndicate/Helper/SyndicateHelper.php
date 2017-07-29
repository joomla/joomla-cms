<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_syndicate
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Syndicate\Site\Helper;

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;

/**
 * Helper for mod_syndicate
 *
 * @package     Joomla.Site
 * @subpackage  mod_syndicate
 * @since       1.5
 */
class SyndicateHelper
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
		$document = Factory::getDocument();

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
