<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 * @since       1.5
 */
class modLoginHelper
{
	static function getReturnURL($params, $type)
	{
		$app	= JFactory::getApplication();
		$router = $app->getRouter();
		$url = null;
		if ($itemid = $params->get($type))
		{
			$db		= JFactory::getDbo();
			$query	= $db->getQuery(true);

			$query->select($db->quoteName('link'));
			$query->from($db->quoteName('#__menu'));
			$query->where($db->quoteName('published') . '=1');
			$query->where($db->quoteName('id') . '=' . $db->quote($itemid));

			$db->setQuery($query);
			if ($link = $db->loadResult()) {
				if ($router->getMode() == JROUTER_MODE_SEF) {
					$url = 'index.php?Itemid='.$itemid;
				}
				else {
					$url = $link.'&Itemid='.$itemid;
				}
			}
		}
		if (!$url)
		{
			// Stay on the same page
			$uri = clone JURI::getInstance();
			$vars = $router->parse($uri);
			unset($vars['lang']);
			if ($router->getMode() == JROUTER_MODE_SEF)
			{
				if (isset($vars['Itemid']))
				{
					$itemid = $vars['Itemid'];
					$menu = $app->getMenu();
					$item = $menu->getItem($itemid);
					unset($vars['Itemid']);
					if (isset($item) && $vars == $item->query) {
						$url = 'index.php?Itemid='.$itemid;
					}
					else {
						$url = 'index.php?'.JURI::buildQuery($vars).'&Itemid='.$itemid;
					}
				}
				else
				{
					$url = 'index.php?'.JURI::buildQuery($vars);
				}
			}
			else
			{
				$url = 'index.php?'.JURI::buildQuery($vars);
			}
		}

		return base64_encode($url);
	}

	static function getType()
	{
		$user = JFactory::getUser();
		return (!$user->get('guest')) ? 'logout' : 'login';
	}
}
