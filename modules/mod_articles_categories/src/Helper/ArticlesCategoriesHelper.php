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
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_articles_categories
 *
 * @since  __DEPLOY_VERSION__
 */
class ArticlesCategoriesHelper implements \Joomla\Database\DatabaseAwareInterface
{
    use \Joomla\Database\DatabaseAwareTrait;

    /**
     * Given a parent category, return a list of children cateories
     *
     * @param   Registry         $params  The module parameters.
     * @param   SiteApplication  $app     The current application.
     *
     * @return  \Joomla\CMS\Categories\CategoryNode[]
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getChildrenCategories(Registry $moduleParams, SiteApplication $app): array
    {
        // Joomla\CMS\Categories\Categories options to set
        $options = [];

        // Get the number of items in this category or
        // descendants of this category at the expense of performance.
        $options['countItems'] = $moduleParams->get('numitems', 0);

        /** @var \Joomla\CMS\Categories\CategoryInterface $contentCategoryService */
        $contentCategoryService = new \Joomla\Component\Content\Site\Service\Category($options);

        /** @var \Joomla\CMS\Categories\CategoryNode $parentCategory */
        $parentCategory = $contentCategoryService->get($moduleParams->get('parent', 'root'));

        $childrenCategories = [];

        if ($parentCategory !== null) {
            // Get all the childrens categories of this node
            $childrenCategories = $parentCategory->getChildren(true);

            $count = $moduleParams->get('count', 0);

            if ($count > 0 && \count($childrenCategories) > $count) {
                $childrenCategories = \array_slice($childrenCategories, 0, $count);
            }
        }

        return $childrenCategories;
    }

    /**
     * Get list of articles
     *
     * @param   \Joomla\Registry\Registry  &$params  module parameters
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     *
     * @deprecated 5.0 Use the none static function getChildrenCategories
     */
    public static function getList(&$params)
    {
        return (new self())->getChildrenCategories($params, Factory::getApplication());
    }
}
