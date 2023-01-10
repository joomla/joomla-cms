<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_archive
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\ArticlesArchive\Site\Helper;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_articles_archive
 *
 * @since  __DEPLOY_VERSION__
 */
class ArticlesArchiveHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * The module instance
     *
     * @var    \stdClass
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
    public function getArticlesByMonths(Registry $moduleParams, SiteApplication $app)
    {
        $cacheKey = md5(serialize(array ($moduleParams->toString(), $this->module->module, $this->module->id)));

        /** @var \Joomla\CMS\Cache\Controller\OutputController $cache */
        $cache = Factory::getContainer()->get(CacheControllerFactoryInterface::class)
            ->createCacheController('output', ['defaultgroup' => 'mod_articles_archive']);

        if (!$cache->contains($cacheKey)) {
            $mvcContentFactory = $app->bootComponent('com_content')->getMVCFactory();

            /** @var \Joomla\Component\Content\Site\Model\ArticlesModel $articlesModel */
            $articlesModel = $mvcContentFactory->createModel('Articles', 'Site', ['ignore_request' => true]);

            // Set application parameters in model
            $appParams = $app->getParams();
            $articlesModel->setState('params', $appParams);

            // Filter on archived articles
            $articlesModel->setState('filter.published', ContentComponent::CONDITION_ARCHIVED);

            $articlesModel->setState('list.start', 0);

            // Set the filters based on the module params
            $articlesModel->setState('list.limit', (int) $moduleParams->get('count', 1));

            // This module does not use tags data
            $articlesModel->setState('load_tags', false);

            // Filter by language
            $articlesModel->setState('filter.language', $app->getLanguageFilter());

            // Prepare the module output
            $items  = [];
            $menu   = $app->getMenu();

            $menuItem       = $menu->getItems('link', 'index.php?option=com_content&view=archive', true);
            $urlParamItemid = (isset($menuItem) && !empty($menuItem->id)) ? '&Itemid=' . $menuItem->id : '';

            foreach ($articlesModel->countItemsByMonth() as $month) {
                $items[] = static::prepareItem($month, $urlParamItemid);
            }

            // Cache the output and return
            $cache->store($items, $cacheKey);

            return $items;
        }

        // Return the cached output
        return $cache->get($cacheKey);
    }

    /**
     * Prepare the month before render.
     *
     * @param   object     $month           The month to prepare
     * @param   string  $urlParamItemid  The Itemid param of the URL
     *
     * @return  \stdClass
     *
     * @since   __DEPLOY_VERSION__
     */
    protected static function prepareItem($month, $urlParamItemid): \stdClass
    {
        $date = Factory::getDate($month->d);

        $createdMonth = $date->format('n');
        $createdYear  = $date->format('Y');

        $createdYearCal = HTMLHelper::_('date', $month->d, 'Y');
        $monthNameCal   = HTMLHelper::_('date', $month->d, 'F');

        $archivedArticlesMonth = new \stdClass();

        $archivedArticlesMonth->link        = Route::_('index.php?option=com_content&view=archive&year=' . $createdYear . '&month=' . $createdMonth . $urlParamItemid);
        $archivedArticlesMonth->name        = Text::sprintf('MOD_ARTICLES_ARCHIVE_DATE', $monthNameCal, $createdYearCal);
        $archivedArticlesMonth->numarticles = $month->c;

        return $archivedArticlesMonth;
    }

    /**
     * Retrieve list of archived articles
     *
     * @param   \Joomla\Registry\Registry  &$params  module parameters
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     *
     * @deprecated 5.0 Use the none static function getArticles
     */
    public static function getList(&$params)
    {
        /** @var \Joomla\CMS\Application\SiteApplication $app */
        $app = Factory::getApplication();

        return (new self())->getArticlesByMonths($params, $app);
    }
}
