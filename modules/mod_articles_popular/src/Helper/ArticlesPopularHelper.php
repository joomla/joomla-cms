<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_popular
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\ArticlesPopular\Site\Helper;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Cache\Controller\OutputController;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Component\Content\Site\Model\ArticlesModel;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_articles_popular
 *
 * @since  4.3.0
 */
class ArticlesPopularHelper
{
    /**
     * The module instance
     *
     * @var    \stdClass
     *
     * @since  4.3.0
     */
    protected $module;

    /**
     * Constructor.
     *
     * @param  array  $config   An optional associative array of configuration settings.
     *
     * @since  4.3.0
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
     * @since   4.3.0
     */
    public function getArticles(Registry $moduleParams, SiteApplication $app)
    {
        $cacheKey = md5(serialize([$moduleParams->toString(), $this->module->module, $this->module->id]));

        /** @var OutputController $cache */
        $cache = Factory::getContainer()->get(CacheControllerFactoryInterface::class)
            ->createCacheController('output', ['defaultgroup' => 'mod_articles_popular']);

        if (!$cache->contains($cacheKey)) {
            $mvcContentFactory = $app->bootComponent('com_content')->getMVCFactory();

            /** @var ArticlesModel $articlesModel */
            $articlesModel = $mvcContentFactory->createModel('Articles', 'Site', ['ignore_request' => true]);

            // Set application parameters in model
            $appParams = $app->getParams();
            $articlesModel->setState('params', $appParams);

            $articlesModel->setState('list.start', 0);
            $articlesModel->setState('filter.published', ContentComponent::CONDITION_PUBLISHED);

            // Set the filters based on the module params
            $articlesModel->setState('list.limit', (int) $moduleParams->get('count', 5));
            $articlesModel->setState('filter.featured', $moduleParams->get('show_front', 1) == 1 ? 'show' : 'hide');

            // This module does not use tags data
            $articlesModel->setState('load_tags', false);

            // Access filter
            $access = !ComponentHelper::getParams('com_content')->get('show_noauth');
            $articlesModel->setState('filter.access', $access);

            // Category filter
            $articlesModel->setState('filter.category_id', $moduleParams->get('catid', []));

            // Date filter
            $date_filtering = $moduleParams->get('date_filtering', 'off');

            if ($date_filtering !== 'off') {
                $articlesModel->setState('filter.date_filtering', $date_filtering);
                $articlesModel->setState('filter.date_field', $moduleParams->get('date_field', 'a.created'));
                $articlesModel->setState('filter.start_date_range', $moduleParams->get('start_date_range', '1000-01-01 00:00:00'));
                $articlesModel->setState('filter.end_date_range', $moduleParams->get('end_date_range', '9999-12-31 23:59:59'));
                $articlesModel->setState('filter.relative_date', $moduleParams->get('relative_date', 30));
            }

            // Filter by language
            $articlesModel->setState('filter.language', $app->getLanguageFilter());

            // Ordering
            $articlesModel->setState('list.ordering', 'a.hits');
            $articlesModel->setState('list.direction', 'DESC');

            // Prepare the module output
            $items      = [];
            $itemParams = new \stdClass();

            $itemParams->authorised = Access::getAuthorisedViewLevels($app->getIdentity()->id);
            $itemParams->access     = $access;

            foreach ($articlesModel->getItems() as $item) {
                $items[] = $this->prepareItem($item, $itemParams);
            }

            // Cache the output and return
            $cache->store($items, $cacheKey);

            return $items;
        }

        // Return the cached output
        return $cache->get($cacheKey);
    }

    /**
     * Prepare the article before render.
     *
     * @param   object     $item   The article to prepare
     * @param   \stdClass  $params  The model item
     *
     * @return  object
     *
     * @since   4.3.0
     */
    private function prepareItem($item, $params): object
    {
        $item->slug = $item->id . ':' . $item->alias;

        if ($params->access || \in_array($item->access, $params->authorised)) {
            // We know that user has the privilege to view the article
            $item->link = Route::_(RouteHelper::getArticleRoute($item->slug, $item->catid, $item->language));
        } else {
            $item->link = Route::_('index.php?option=com_users&view=login');
        }

        return $item;
    }

    /**
     * Get a list of popular articles from the articles model
     *
     * @param   \Joomla\Registry\Registry  &$params  object holding the models parameters
     *
     * @return  mixed
     *
     * @since  4.3.0
     *
     * @deprecated 4.3 will be removed in 6.0
     *             Use the non-static method getArticles
     *             Example: Factory::getApplication()->bootModule('mod_articles_popular', 'site')
     *                          ->getHelper('ArticlesPopularHelper')
     *                          ->getArticles($params, Factory::getApplication())
     */
    public static function getList(&$params)
    {
        return (new self())->getArticles($params, Factory::getApplication());
    }
}
