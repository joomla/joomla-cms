<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Component\Router;

use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Categories\CategoryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait for default category behavior in a component router.
 * When configuring the router, hand in these methods as build-
 * and parse-callback for a category or categories view.
 *
 * @since  __DEPLOY_VERSION__
 */
trait CategoryCallbackTrait
{
    /**
     * The category factory
     *
     * @var CategoryFactoryInterface
     *
     * @since  __DEPLOY_VERSION__
     */
    private $categoryFactory;

    /**
     * The category cache
     *
     * @var  array
     *
     * @since  __DEPLOY_VERSION__
     */
    private $categoryCache = [];

    /**
     * Method to get the id for a category
     *
     * @param   string  $segment  Segment to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     *
     * @since  __DEPLOY_VERSION__
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
     * @param   string  $id     ID of the category to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array|string  The segments of this item
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getCategorySegment($id, $query)
    {
        $category = $this->getCategories(['access' => true])->get($id);

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
     * Method to get categories from cache
     *
     * @param   array  $options   The options for retrieving categories
     *
     * @return  CategoryInterface  The object containing categories
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getCategories(array $options = []): CategoryInterface
    {
        $key = serialize($options);

        if (!isset($this->categoryCache[$key])) {
            $this->categoryCache[$key] = $this->categoryFactory->createCategory($options);
        }

        return $this->categoryCache[$key];
    }

    /**
     * Set the category factory for the trait
     *
     * @param   CategoryFactoryInterface  $factory   The factory to get the category object from
     *
     * @return  void
     * @since   __DEPLOY_VERSION__
     */
    protected function setCategoryFactory(CategoryFactoryInterface $factory)
    {
        $this->categoryFactory = $factory;
    }
}
