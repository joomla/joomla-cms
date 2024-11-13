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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterBase;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

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
     * Lookup array of the menu items
     *
     * @var   array
     * @since 4.3.0
     */
    protected $lookup = [];

    /**
     * System - SEF Plugin parameters
     *
     * @var   Registry
     * @since 5.2.0
     * @deprecated  5.2.0 will be removed in 6.0
     *              without replacement
     */
    private $sefparams;

    /**
     * Tags Component router constructor
     *
     * @param   SiteApplication            $app              The application object
     * @param   AbstractMenu               $menu             The menu object to work with
     * @param   ?CategoryFactoryInterface  $categoryFactory  The category object
     * @param   DatabaseInterface          $db               The database object
     *
     * @since  4.0.0
     */
    public function __construct(SiteApplication $app, AbstractMenu $menu, ?CategoryFactoryInterface $categoryFactory, DatabaseInterface $db)
    {
        $this->db = $db;

        parent::__construct($app, $menu);

        $sefPlugin       = PluginHelper::getPlugin('system', 'sef');

        if ($sefPlugin) {
            $this->sefparams = new Registry($sefPlugin->params);
        } else {
            $this->sefparams = new Registry();
        }

        $this->buildLookup();
    }


    /**
     * Preprocess com_tags URLs
     *
     * @param   array  $query  An associative array of URL arguments
     *
     * @return  array  The URL arguments to use to assemble the subsequent URL.
     *
     * @since   4.3.0
     */
    public function preprocess($query)
    {
        $active = $this->menu->getActive();

        /**
         * If the active item id is not the same as the supplied item id or we have a supplied item id and no active
         * menu item then we just use the supplied menu item and continue
         */
        if (isset($query['Itemid']) && ($active === null || $query['Itemid'] != $active->id)) {
            return $query;
        }

        // Get query language
        $lang = $query['lang'] ?? '*';

        // Set the language to the current one when multilang is enabled and item is tagged to ALL
        if (Multilanguage::isEnabled() && $lang === '*') {
            $lang = $this->app->get('language');
        }

        foreach (array_unique([$lang, '*']) as $language) {
            if (isset($query['view']) && $query['view'] === 'tags') {
                if (isset($query['parent_id']) && isset($this->lookup[$language]['tags'][$query['parent_id']])) {
                    $query['Itemid'] = $this->lookup[$language]['tags'][$query['parent_id']];
                    break;
                }

                if (isset($this->lookup[$language]['tags'][0])) {
                    $query['Itemid'] = $this->lookup[$language]['tags'][0];
                    break;
                }
            } elseif (isset($query['view']) && $query['view'] === 'tag') {
                if (isset($query['id'])) {
                    if (!\is_array($query['id'])) {
                        $query['id'] = [$query['id']];
                    }

                    $id = ArrayHelper::toInteger($query['id']);
                    sort($id);

                    if (isset($this->lookup[$language]['tag'][implode(',', $id)])) {
                        $query['Itemid'] = $this->lookup[$language]['tag'][implode(',', $id)];
                        break;
                    }

                    foreach ($id as $i) {
                        if (isset($this->lookup[$language]['tag'][$i])) {
                            $query['Itemid'] = $this->lookup[$language]['tag'][$i];
                            break 2;
                        }
                    }

                    if (isset($this->lookup[$language]['tags'][implode(',', $id)])) {
                        $query['Itemid'] = $this->lookup[$language]['tags'][implode(',', $id)];
                        break;
                    }

                    if (isset($this->lookup[$language]['tags'][0])) {
                        $query['Itemid'] = $this->lookup[$language]['tags'][0];
                        break;
                    }
                }
            }
        }

        // TODO: Remove this whole block in 6.0 as it is a bug
        if (!$this->sefparams->get('strictrouting', 0)) {
            // If not found, return language specific home link
            if (!isset($query['Itemid'])) {
                $default = $this->menu->getDefault($lang);

                if (!empty($default->id)) {
                    $query['Itemid'] = $default->id;
                }
            }
        }

        return $query;
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
        $segments = [];

        $menuItem = !empty($query['Itemid']) ? $this->menu->getItem($query['Itemid']) : false;

        if ($menuItem && $menuItem->query['option'] == 'com_tags') {
            if ($menuItem->query['view'] == 'tags') {
                if (isset($query['id'])) {
                    $ids = $query['id'];

                    if (!\is_array($ids)) {
                        $ids = [$ids];
                    }

                    foreach ($ids as $id) {
                        $segments[] = $id;
                    }

                    unset($query['id']);
                } elseif (isset($query['parent_id'], $menuItem->query['parent_id'])) {
                    if ($query['parent_id'] == $menuItem->query['parent_id']) {
                        unset($query['parent_id']);
                    }
                }
            } elseif ($menuItem->query['view'] == 'tag') {
                $ids     = $query['id'];
                $int_ids = ArrayHelper::toInteger($ids);
                $mIds    = (array) $menuItem->query['id'];

                /**
                 * We check if there is a difference between the tags of the menu item and the query.
                 * If they are identical, we exactly match the menu item. Otherwise we append all tags to the URL
                 */
                if (\count(array_diff($int_ids, $mIds)) > 0 || \count(array_diff($mIds, $int_ids)) > 0) {
                    foreach ($ids as $id) {
                        $segments[] = $id;
                    }
                }

                unset($query['id']);
            }

            unset($query['view']);
        } else {
            $segments[] = $query['view'];
            unset($query['view'], $query['Itemid']);

            if (isset($query['id']) && \is_array($query['id'])) {
                foreach ($query['id'] as $id) {
                    $segments[] = $id;
                }

                unset($query['id']);
            }
        }

        unset($query['layout']);

        foreach ($segments as &$segment) {
            if (strpos($segment, ':')) {
                [$void, $segment] = explode(':', $segment, 2);
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
        $vars = [];

        // Get the active menu item.
        $item = $this->menu->getActive();

        // We don't have a menu item
        if (!$item || $item->query['option'] != 'com_tags') {
            if (!isset($segments[0])) {
                return $vars;
            }

            $vars['view'] = array_shift($segments);
        }

        $ids = [];

        if ($item && $item->query['view'] == 'tag') {
            $ids = $item->query['id'];
        }

        while (\count($segments)) {
            $id    = array_shift($segments);
            $ids[] = $this->fixSegment($id);
        }

        if (\count($ids)) {
            $vars['id']   = $ids;
            $vars['view'] = 'tag';
        }

        return $vars;
    }

    /**
     * Method to build the lookup array
     *
     * @param   string  $language  The language that the lookup should be built up for
     *
     * @return  void
     *
     * @since   4.3.0
     */
    protected function buildLookup()
    {
        $component = ComponentHelper::getComponent('com_tags');
        $items     = $this->app->getMenu()->getItems(['component_id'], [$component->id]);

        foreach ($items as $item) {
            if (!isset($this->lookup[$item->language])) {
                $this->lookup[$item->language] = ['tags' => [], 'tag' => []];
            }

            if ($item->query['view'] == 'tag') {
                $id = $item->query['id'];
                sort($id);
                $this->lookup[$item->language]['tag'][implode(',', $id)] = $item->id;

                foreach ($id as $i) {
                    $this->lookup[$item->language]['tag'][$i] = $item->id;
                }
            }

            if ($item->query['view'] == 'tags') {
                $id                                         = (int) ($item->query['parent_id'] ?? 0);
                $this->lookup[$item->language]['tags'][$id] = $item->id;
            }
        }

        foreach ($this->lookup as $language => $items) {
            // We have tags views with parent_id set and need to load child tags to be assigned to this menu item
            if (
                \count($this->lookup[$language]['tags']) > 1
                || (\count($this->lookup[$language]['tags']) == 1 && !isset($this->lookup[$language]['tags'][0]))
            ) {
                foreach ($this->lookup[$language]['tags'] as $id => $menu) {
                    if ($id === 0) {
                        continue;
                    }

                    $query = $this->db->getQuery(true);
                    $query->select($this->db->quoteName('a.id'))
                        ->from($this->db->quoteName('#__tags', 'a'))
                        ->leftJoin(
                            $this->db->quoteName('#__tags', 'b')
                            . ' ON ' . $this->db->quoteName('b.lft') . ' < ' . $this->db->quoteName('a.lft')
                            . ' AND ' . $this->db->quoteName('a.rgt') . ' < ' . $this->db->quoteName('b.rgt')
                        )
                        ->where($this->db->quoteName('b.id') . ' = :id')
                        ->bind(':id', $id);
                    $this->db->setQuery($query);
                    $ids = (array) $this->db->loadColumn();

                    foreach ($ids as $i) {
                        $this->lookup[$language]['tags'][$i] = $menu;
                    }
                }
            }
        }
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
            $segment = $id . ':' . $alias;
        }

        return $segment;
    }
}
