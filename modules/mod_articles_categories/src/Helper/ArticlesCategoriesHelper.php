<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_categories
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\ArticlesCategories\Site\Helper;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Categories\CategoryInterface;
use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_articles_categories
 *
 * @since  1.5
 */
class ArticlesCategoriesHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Given a parent category, return a list of children categories
     *
     * @param   Registry         $moduleParams  The module parameters.
     * @param   SiteApplication  $app           The current application.
     *
     * @return  CategoryNode[]
     *
     * @since   4.4.0
     */
    public function getChildrenCategories(Registry $moduleParams, SiteApplication $app): array
    {
        // Joomla\CMS\Categories\Categories options to set
        $options = [];

        // Get the number of items in this category or descendants of this category at the expense of performance.
        $options['countItems'] = $moduleParams->get('numitems', 0);

        /** @var CategoryInterface $categoryFactory */
        $categoryFactory = $app->bootComponent('com_content')->getCategory($options);

        /** @var CategoryNode $parentCategory */
        $parentCategory = $categoryFactory->get($moduleParams->get('parent', 'root'));

        if ($parentCategory === null) {
            return [];
        }

        // Get all the children categories of this node
        $childrenCategories = $parentCategory->getChildren();

        $count = $moduleParams->get('count', 0);

        if ($count > 0 && \count($childrenCategories) > $count) {
            $childrenCategories = \array_slice($childrenCategories, 0, $count);
        }

        return $childrenCategories;
    }

    /**
     * Get list of categories
     *
     * @param   Registry  $params  module parameters
     *
     * @return  array
     *
     * @since   1.6
     *
     * @deprecated  4.4.0  will be removed in 6.0
     *              Use the non-static method getChildrenCategories
     *              Example: Factory::getApplication()->bootModule('mod_articles_categories', 'site')
     *                           ->getHelper('ArticlesCategoriesHelper')
     *                           ->getChildrenCategories($params, Factory::getApplication())
     */
    public static function getList($params)
    {
        /** @var SiteApplication $app */
        $app = Factory::getApplication();

        return (new self())->getChildrenCategories($params, $app);
    }
}
