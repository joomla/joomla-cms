<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Newsfeeds\Site\Service;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Categories\CategoryInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\PreprocessRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Routing class from com_newsfeeds
 *
 * @since  3.3
 */
class Router extends RouterView
{
    /**
     * Flag to remove IDs
     *
     * @var    boolean
     */
    protected $noIDs = false;

    /**
     * The category factory
     *
     * @var CategoryFactoryInterface
     *
     * @since  4.0.0
     */
    private $categoryFactory;

    /**
     * The category cache
     *
     * @var  array
     *
     * @since  4.0.0
     */
    private $categoryCache = [];

    /**
     * The db
     *
     * @var DatabaseInterface
     *
     * @since  4.0.0
     */
    private $db;

    /**
     * Newsfeeds Component router constructor
     *
     * @param   SiteApplication           $app              The application object
     * @param   AbstractMenu              $menu             The menu object to work with
     * @param   CategoryFactoryInterface  $categoryFactory  The category object
     * @param   DatabaseInterface         $db               The database object
     */
    public function __construct(SiteApplication $app, AbstractMenu $menu, CategoryFactoryInterface $categoryFactory, DatabaseInterface $db)
    {
        $this->categoryFactory = $categoryFactory;
        $this->db              = $db;

        $params      = ComponentHelper::getParams('com_newsfeeds');
        $this->noIDs = (bool) $params->get('sef_ids');
        $categories  = new RouterViewConfiguration('categories');
        $categories->setKey('id');
        $this->registerView($categories);
        $category = new RouterViewConfiguration('category');
        $category->setKey('id')->setParent($categories, 'catid')->setNestable();
        $this->registerView($category);
        $newsfeed = new RouterViewConfiguration('newsfeed');
        $newsfeed->setKey('id')->setParent($category, 'catid');
        $this->registerView($newsfeed);

        parent::__construct($app, $menu);

        $preprocess = new PreprocessRules($newsfeed, '#__newsfeeds', 'id', 'catid');
        $preprocess->setDatabase($this->db);
        $this->attachRule($preprocess);
        $this->attachRule(new MenuRules($this));
        $this->attachRule(new StandardRules($this));
        $this->attachRule(new NomenuRules($this));
    }

    /**
     * Method to get the segment(s) for a category
     *
     * @param   string  $id     ID of the category to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array|string  The segments of this item
     */
    public function getCategorySegment($id, $query)
    {
        $category = $this->getCategories()->get($id);

        if ($category) {
            $path    = array_reverse($category->getPath(), true);
            $path[0] = '1:root';

            if ($this->noIDs) {
                foreach ($path as &$segment) {
                    [, $segment] = explode(':', $segment, 2);
                }
            }

            return $path;
        }

        return [];
    }

    /**
     * Method to get the segment(s) for a category
     *
     * @param   string  $id     ID of the category to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array|string  The segments of this item
     */
    public function getCategoriesSegment($id, $query)
    {
        return $this->getCategorySegment($id, $query);
    }

    /**
     * Method to get the segment(s) for a newsfeed
     *
     * @param   string  $id     ID of the newsfeed to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array|string  The segments of this item
     */
    public function getNewsfeedSegment($id, $query)
    {
        if ($this->noIDs && strpos($id, ':')) {
            [$void, $segment] = explode(':', $id, 2);

            return [$void => $segment];
        }

        return [(int) $id => $id];
    }

    /**
     * Method to get the id for a category
     *
     * @param   string  $segment  Segment to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     */
    public function getCategoryId($segment, $query)
    {
        if (isset($query['id'])) {
            $category = $this->getCategories(['access' => false])->get($query['id']);

            if ($category) {
                if ($this->noIDs) {
                    foreach ($category->getChildren() as $child) {
                        if ($child->alias == $segment) {
                            return $child->id;
                        }
                    }

                    // We haven't found a matching category, but maybe we turned off IDs?
                    foreach ($category->getChildren() as $child) {
                        if ($child->id == (int) $segment) {
                            $this->app->getRouter()->setTainted();

                            return $child->id;
                        }
                    }
                } else {
                    foreach ($category->getChildren() as $child) {
                        if ($child->id == (int) $segment) {
                            if ($child->id . '-' . $child->alias != $segment) {
                                $this->app->getRouter()->setTainted();
                            }

                            return $child->id;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Method to get the segment(s) for a category
     *
     * @param   string  $segment  Segment to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     */
    public function getCategoriesId($segment, $query)
    {
        return $this->getCategoryId($segment, $query);
    }

    /**
     * Method to get the segment(s) for a newsfeed
     *
     * @param   string  $segment  Segment of the newsfeed to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     */
    public function getNewsfeedId($segment, $query)
    {
        if ($this->noIDs) {
            $dbquery = $this->db->getQuery(true);
            $dbquery->select($this->db->quoteName('id'))
                ->from($this->db->quoteName('#__newsfeeds'))
                ->where($this->db->quoteName('alias') . ' = :segment')
                ->bind(':segment', $segment);

            if (isset($query['id']) && $query['id']) {
                $dbquery->where($this->db->quoteName('catid') . ' = :id')
                    ->bind(':id', $query['id'], ParameterType::INTEGER);
            }

            $this->db->setQuery($dbquery);

            $id = (int) $this->db->loadResult();

            // Do we have a URL with ID?
            if ($id) {
                return $id;
            }

            $this->app->getRouter()->setTainted();
        }

        $id = (int) $segment;

        if ($id) {
            $dbquery = $this->db->getQuery(true);
            $dbquery->select($this->db->quoteName('alias'))
                ->from($this->db->quoteName('#__newsfeeds'))
                ->where($this->db->quoteName('id') . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER);
            $this->db->setQuery($dbquery);
            $alias = $this->db->loadResult();

            if ($alias && $id . '-' . $alias != $segment) {
                $this->app->getRouter()->setTainted();
            }
        }

        return $id;
    }

    /**
     * Method to get categories from cache
     *
     * @param   array  $options   The options for retrieving categories
     *
     * @return  CategoryInterface  The object containing categories
     *
     * @since   4.0.0
     */
    private function getCategories(array $options = []): CategoryInterface
    {
        $key = serialize($options);

        if (!isset($this->categoryCache[$key])) {
            $this->categoryCache[$key] = $this->categoryFactory->createCategory($options);
        }

        return $this->categoryCache[$key];
    }
}
