<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Login\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Uri\Uri;

/**
 * Helper for mod_login
 *
 * @since  1.5
 */
class LoginHelper
{
	/**
	 * Retrieve the URL where the user should be returned after logging in
	 *
	 * @param   \Joomla\Registry\Registry  $params  module parameters
	 * @param   string                     $type    return type
	 *
	 * @return  string
	 */
	public static function getReturnUrl($params, $type)
	{
		$app  = Factory::getApplication();
		$item = $app->getMenu()->getItem($params->get($type));

		if ($item)
		{
			$lang = '';

			if ($item->language !== '*' && Multilanguage::isEnabled())
			{
				$lang = '&lang=' . $item->language;
			}

			return base64_encode('index.php?Itemid=' . $item->id . $lang);
		}

		// Stay on the same page
		$uri = new Uri('index.php');
		$uri->setQuery($app->getRouter()->getVars());

		return base64_encode($uri->toString());
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
