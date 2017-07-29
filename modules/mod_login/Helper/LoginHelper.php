<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Login\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Helper\AuthenticationHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

/**
 * Helper for mod_login
 *
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @since       1.5
 */
class LoginHelper
{
	/**
	 * Retrieve the URL where the user should be returned after logging in
	 *
	 * @param   \Joomla\Registry\Registry  $params  module parameters
	 * @param   string                     $type    return type
	 *
	 * @return string
	 */
	public static function getReturnUrl($params, $type)
	{
		$item = Factory::getApplication()->getMenu()->getItem($params->get($type));

		// Stay on the same page
		$url = Uri::getInstance()->toString();

		if ($item)
		{
			$lang = '';

			if ($item->language !== '*' && Multilanguage::isEnabled())
			{
				$lang = '&lang=' . $item->language;
			}

			$url = 'index.php?Itemid=' . $item->id . $lang;
		}

		return base64_encode($url);
	}

	/**
	 * Returns the current users type
	 *
	 * @return string
	 */
	public static function getType()
	{
		$user = Factory::getUser();

		return (!$user->get('guest')) ? 'logout' : 'login';
	}
}
