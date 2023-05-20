<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Tags\Site\Helper;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\RouteHelper as CMSRouteHelper;
use Joomla\CMS\Menu\AbstractMenu;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

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
     * @since  4.3.0
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
        $link           = '';
        $explodedAlias  = explode('.', $typeAlias);
        $explodedRouter = explode('::', $routerName);

        if (file_exists($routerFile = JPATH_BASE . '/components/' . $explodedAlias[0] . '/helpers/route.php')) {
            \JLoader::register($explodedRouter[0], $routerFile);
            $routerClass  = $explodedRouter[0];
            $routerMethod = $explodedRouter[1];

            if (class_exists($routerClass) && method_exists($routerClass, $routerMethod)) {
                if ($routerMethod === 'getCategoryRoute') {
                    $link = $routerClass::$routerMethod($contentItemId, $language);
                } else {
                    $link = $routerClass::$routerMethod($contentItemId . ':' . $contentItemAlias, $contentCatId, $language);
                }
            }
        }

        if ($link === '') {
            // Create a fallback link in case we can't find the component router
            $router = new CMSRouteHelper();
            $link   = $router->getRoute($contentItemId, $typeAlias, $link, $language, $contentCatId);
        }

        return $link;
    }

    /**
     * Tries to load the router for the component and calls it. Otherwise calls getRoute.
     *
     * @param   integer  $id        The ID of the tag
     *
     * @return  string  URL link to pass to the router
     *
     * @since      3.1
     * @throws     Exception
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use RouteHelper::getComponentTagRoute() instead
     */
    public static function getTagRoute($id)
    {
        @trigger_error('This function is replaced by the getComponentTagRoute()', E_USER_DEPRECATED);

        return self::getComponentTagRoute($id);
    }

    /**
     * Tries to load the router for the component and calls it. Otherwise calls getRoute.
     *
     * @param   string   $id        The ID of the tag in the format TAG_ID:TAG_ALIAS
     * @param   string   $language  The language of the tag
     *
     * @return  string  URL link to pass to the router
     *
     * @since   4.2.0
     * @throws  Exception
     */
    public static function getComponentTagRoute(string $id, string $language = '*'): string
    {
        // We actually would want to allow arrays of tags here, but can't due to B/C
        if (!is_array($id)) {
            if ($id < 1) {
                return '';
            }

            $id = [$id];
        }

        $id = array_values(array_filter($id));

        if (!count($id)) {
            return '';
        }

        $link = 'index.php?option=com_tags&view=tag';

        foreach ($id as $i => $value) {
            $link .= '&id[' . $i . ']=' . $value;
        }

        return $link;
    }

    /**
     * Tries to load the router for the tags view.
     *
     * @return  string  URL link to pass to the router
     *
     * @since      3.7
     * @throws     Exception
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use RouteHelper::getComponentTagsRoute() instead
     *
     */
    public static function getTagsRoute()
    {
        @trigger_error('This function is replaced by the getComponentTagsRoute()', E_USER_DEPRECATED);

        return self::getComponentTagsRoute();
    }

    /**
     * Tries to load the router for the tags view.
     *
     * @param   string  $language  The language of the tag
     *
     * @return  string  URL link to pass to the router
     *
     * @since   4.2.0
     * @throws  Exception
     */
    public static function getComponentTagsRoute(string $language = '*'): string
    {
        $link = 'index.php?option=com_tags&view=tags';

        return $link;
    }

    /**
     * Find Item static function
     *
     * @param   array  $needles  Array used to get the language value
     *
     * @return null
     *
     * @throws Exception
     */
    protected static function _findItem($needles = null)
    {
        $menus    = AbstractMenu::getInstance('site');
        $language = $needles['language'] ?? '*';

        // Prepare the reverse lookup array.
        if (self::$lookup === null) {
            self::$lookup = [];

            $component = ComponentHelper::getComponent('com_tags');
            $items     = $menus->getItems('component_id', $component->id);

            if ($items) {
                foreach ($items as $item) {
                    if (isset($item->query, $item->query['view'])) {
                        $lang = ($item->language != '' ? $item->language : '*');

                        if (!isset(self::$lookup[$lang])) {
                            self::$lookup[$lang] = [];
                        }

                        $view = $item->query['view'];

                        if (!isset(self::$lookup[$lang][$view])) {
                            self::$lookup[$lang][$view] = [];
                        }

                        // Only match menu items that list one tag
                        if (isset($item->query['id']) && is_array($item->query['id'])) {
                            foreach ($item->query['id'] as $position => $tagId) {
                                if (!isset(self::$lookup[$lang][$view][$item->query['id'][$position]]) || count($item->query['id']) == 1) {
                                    self::$lookup[$lang][$view][$item->query['id'][$position]] = $item->id;
                                }
                            }
                        } elseif ($view == 'tags') {
                            self::$lookup[$lang]['tags'][] = $item->id;
                        }
                    }
                }
            }
        }

        if ($needles) {
            foreach ($needles as $view => $ids) {
                if (isset(self::$lookup[$language][$view])) {
                    foreach ($ids as $id) {
                        if (isset(self::$lookup[$language][$view][(int) $id])) {
                            return self::$lookup[$language][$view][(int) $id];
                        }
                    }
                }
            }
        } else {
            $active = $menus->getActive();

            if ($active) {
                return $active->id;
            }
        }

        return null;
    }
}
