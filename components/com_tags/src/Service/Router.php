<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Tags\Site\Service;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Component\Router\RouterBase;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\Database\DatabaseInterface;
use Joomla\Utilities\ArrayHelper;

/**
 * Routing class from com_tags
 *
 * @since  3.3
 */
class Router extends RouterBase
{
    /**
     * The db
     *
     * @var DatabaseInterface
     *
     * @since  4.0.0
     */
    private $db;

    /**
     * Tags Component router constructor
     *
     * @param   SiteApplication           $app              The application object
     * @param   AbstractMenu              $menu             The menu object to work with
     * @param   CategoryFactoryInterface  $categoryFactory  The category object
     * @param   DatabaseInterface         $db               The database object
     *
     * @since  4.0.0
     */
    public function __construct(SiteApplication $app, AbstractMenu $menu, ?CategoryFactoryInterface $categoryFactory, DatabaseInterface $db)
    {
        $this->db = $db;

        parent::__construct($app, $menu);
    }

    /**
     * Build the route for the com_tags component
     *
     * @param   array  &$query  An array of URL arguments
     *
     * @return  array  The URL arguments to use to assemble the subsequent URL.
     *
     * @since   3.3
     */
    public function build(&$query)
    {
        $segments = array();

        // Get a menu item based on Itemid or currently active

        // We need a menu item.  Either the one specified in the query, or the current active one if none specified
        if (empty($query['Itemid'])) {
            $menuItem = $this->menu->getActive();
        } else {
            $menuItem = $this->menu->getItem($query['Itemid']);
        }

        $mView = empty($menuItem->query['view']) ? null : $menuItem->query['view'];
        $mId   = empty($menuItem->query['id']) ? null : $menuItem->query['id'];

        if (is_array($mId)) {
            $mId = ArrayHelper::toInteger($mId);
        }

        $view = '';

        if (isset($query['view'])) {
            $view = $query['view'];

            if (empty($query['Itemid'])) {
                $segments[] = $view;
            }

            unset($query['view']);
        }

        // Are we dealing with a tag that is attached to a menu item?
        if ($mView == $view && isset($query['id']) && $mId == $query['id']) {
            unset($query['id']);

            return $segments;
        }

        if ($view === 'tag') {
            $notActiveTag = is_array($mId) ? (count($mId) > 1 || $mId[0] != (int) $query['id']) : ($mId != (int) $query['id']);

            if ($notActiveTag || $mView != $view) {
                // ID in com_tags can be either an integer, a string or an array of IDs
                $id = is_array($query['id']) ? implode(',', $query['id']) : $query['id'];
                $segments[] = $id;
            }

            unset($query['id']);
        }

        if (isset($query['layout'])) {
            if (
                (!empty($query['Itemid']) && isset($menuItem->query['layout'])
                && $query['layout'] == $menuItem->query['layout'])
                || $query['layout'] === 'default'
            ) {
                unset($query['layout']);
            }
        }

        $total = count($segments);

        for ($i = 0; $i < $total; $i++) {
            $segments[$i] = str_replace(':', '-', $segments[$i]);
            $position     = strpos($segments[$i], '-');

            if ($position) {
                // Remove id from segment
                $segments[$i] = substr($segments[$i], $position + 1);
            }
        }

        return $segments;
    }

    /**
     * Parse the segments of a URL.
     *
     * @param   array  &$segments  The segments of the URL to parse.
     *
     * @return  array  The URL attributes to be used by the application.
     *
     * @since   3.3
     */
    public function parse(&$segments)
    {
        $total = count($segments);
        $vars = array();

        for ($i = 0; $i < $total; $i++) {
            $segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
        }

        // Get the active menu item.
        $item = $this->menu->getActive();

        // Count route segments
        $count = count($segments);

        // Standard routing for tags.
        if (!isset($item)) {
            $vars['view'] = $segments[0];
            $vars['id']   = $this->fixSegment($segments[$count - 1]);
            unset($segments[0]);
            unset($segments[$count - 1]);

            return $vars;
        }

        $vars['id'] = $this->fixSegment($segments[0]);
        $vars['view'] = 'tag';
        unset($segments[0]);

        return $vars;
    }

    /**
     * Try to add missing id to segment
     *
     * @param   string  $segment  One piece of segment of the URL to parse
     *
     * @return  string  The segment with founded id
     *
     * @since   3.7
     */
    protected function fixSegment($segment)
    {
        // Try to find tag id
        $alias = str_replace(':', '-', $segment);

        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__tags'))
            ->where($this->db->quoteName('alias') . ' = :alias')
            ->bind(':alias', $alias);

        $id = $this->db->setQuery($query)->loadResult();

        if ($id) {
            $segment = "$id:$alias";
        }

        return $segment;
    }
}
