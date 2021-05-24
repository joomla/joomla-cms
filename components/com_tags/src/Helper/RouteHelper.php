<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Tags\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Helper\RouteHelper as CMSRouteHelper;

/**
 * Tags Component Route Helper.
 *
 * @since  3.1
 */
class RouteHelper extends CMSRouteHelper
{
	/**
	 * Lookup-table for menu items
	 *
	 * @var    array
	 */
	protected static $lookup;

	/**
	 * Tries to load the router for the component and calls it. Otherwise uses getTagRoute.
	 *
	 * @param   integer  $contentItemId     Component item id
	 * @param   string   $contentItemAlias  Component item alias
	 * @param   integer  $contentCatId      Component item category id
	 * @param   string   $language          Component item language
	 * @param   string   $typeAlias         Component type alias
	 * @param   string   $routerName        Component router
	 *
	 * @return  string  URL link to pass to the router
	 *
	 * @since   3.1
	 */
	public static function getItemRoute($contentItemId, $contentItemAlias, $contentCatId, $language, $typeAlias, $routerName)
	{
		$link = '';
		$explodedAlias = explode('.', $typeAlias);
		$explodedRouter = explode('::', $routerName);

		if (file_exists($routerFile = JPATH_BASE . '/components/' . $explodedAlias[0] . '/helpers/route.php'))
		{
			\JLoader::register($explodedRouter[0], $routerFile);
			$routerClass = $explodedRouter[0];
			$routerMethod = $explodedRouter[1];

			if (class_exists($routerClass) && method_exists($routerClass, $routerMethod))
			{
				if ($routerMethod === 'getCategoryRoute')
				{
					$link = $routerClass::$routerMethod($contentItemId, $language);
				}
				else
				{
					$link = $routerClass::$routerMethod($contentItemId . ':' . $contentItemAlias, $contentCatId, $language);
				}
			}
		}

		if ($link === '')
		{
			// Create a fallback link in case we can't find the component router
			$router = new CMSRouteHelper;
			$link = $router->getRoute($contentItemId, $typeAlias, $link, $language, $contentCatId);
		}

		return $link;
	}

	/**
	 * Tries to load the router for the component and calls it. Otherwise calls getRoute.
	 *
	 * @param   integer  $id  The ID of the tag
	 *
	 * @return  string  URL link to pass to the router
	 *
	 * @since   3.1
	 */
	public static function getTagRoute($id)
	{
		if ($id < 1)
		{
			return '';
		}

		return 'index.php?option=com_tags&view=tag&id=' . $id;
	}

	/**
	 * Tries to load the router for the tags view.
	 *
	 * @return  string  URL link to pass to the router
	 *
	 * @since   3.7
	 */
	public static function getTagsRoute()
	{
		return 'index.php?option=com_tags&view=tags';
	}
}
