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
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Cache\Controller\OutputController;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Site\Model\CategoriesModel;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_articles_categories
 *
 * @since  __DEPLOY_VERSION__
 */
class ArticlesCategoriesHelper
{
    /**
     * The module instance
     *
     * @var    \stdClass
     *
     * @since  __DEPLOY_VERSION__
     */
    protected $module;

    /**
     * Constructor.
     *
     * @param  array  $config   An optional associative array of configuration settings.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __construct($config = [])
    {
        $this->module = $config['module'];
    }

    /**
     * Retrieve a list of months with archived articles
     *
     * @param   Registry         $params  The module parameters.
     * @param   SiteApplication  $app     The current application.
     *
     * @return  object[]
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getArticles(Registry $moduleParams, SiteApplication $app)
    {
        $cacheKey = md5(serialize(array ($moduleParams->toString(), $this->module->module, $this->module->id)));

        /** @var OutputController $cache */
        $cache = Factory::getContainer()->get(CacheControllerFactoryInterface::class)
            ->createCacheController('output', ['defaultgroup' => 'mod_articles_categories']);

        if (!$cache->contains($cacheKey)) {

            $mvcContentFactory = $app->bootComponent('com_content')->getMVCFactory();

            /** @var CategoriesModel $categoriesModel */
            $categoriesModel = $mvcContentFactory->createModel('Categories', 'Site', ['ignore_request' => true]);

            // Set application parameters in model
            $appParams = $app->getParams();
            $categoriesModel->setState('params', $appParams);

            $categoriesModel->setState('list.start', 0);

            $categoriesModel->setState('filter.published', ContentComponent::CONDITION_PUBLISHED);

            // Set the filters based on the module params
            $categoriesModel->setState('filter.parentId', $moduleParams->get('parent', 'root'));
            $categoriesModel->setState('list.limit', (int) $moduleParams->get('count', 0));

            // Access filter
            $access = !ComponentHelper::getParams('com_content')->get('show_noauth');
            $categoriesModel->setState('filter.access', $access);

            $items = $categoriesModel->getItems();

            // Cache the output and return
            $cache->store($items, $cacheKey);
        }

        // Return the cached output
        return $cache->get($cacheKey);
    }

    /**
     * Get list of articles
     *
     * @param   \Joomla\Registry\Registry  &$params  module parameters
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getList(&$params)
    {
        return (new self())->getArticles($params, Factory::getApplication());
    }
}
