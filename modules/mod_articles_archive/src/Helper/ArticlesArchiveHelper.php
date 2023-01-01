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
use Joomla\CMS\Cache\Controller\OutputController;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Site\Model\ArchiveModel;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
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
     * The input instance
     *
     * @var    Input
     * @since  __DEPLOY_VERSION__
     */
    protected $input;

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
        $this->input  = $config['input'];
    }

    /**
     * Retrieve a list of archive article
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
            ->createCacheController('output', ['defaultgroup' => 'mod_articles_archive']);

        if (!$cache->contains($cacheKey)) {
            $mvcContentFactory = $app->bootComponent('com_content')->getMVCFactory();

            /** @var ArchiveModel $archiveModel */
            $archiveModel = $mvcContentFactory->createModel('Archive', 'Site', ['ignore_request' => true]);

            // Set application parameters in model
            $appParams = $app->getParams();
            $archiveModel->setState('params', $appParams);

            $archiveModel->setState('list.start', 0);

            // Set the filters based on the module params
            $archiveModel->setState('list.limit', (int) $moduleParams->get('count', 1));

            // Filter by language
            $archiveModel->setState('filter.language', $app->getLanguageFilter());

            // Prepare the module output
            $items  = [];
            $menu   = $app->getMenu();

            $menuItem       = $menu->getItems('link', 'index.php?option=com_content&view=archive', true);
            $urlParamItemid = (isset($menuItem) && !empty($menuItem->id)) ? '&Itemid=' . $menuItem->id : '';

            foreach ($archiveModel->getData() as $item) {
                $items[] = static::prepareItem($item, $urlParamItemid);
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
     * @param   object     $item            The article to prepare
     * @param   \stdClass  $urlParamItemid  The Itemid param of the URL
     *
     * @return  \stdClass
     *
     * @since   __DEPLOY_VERSION__
     */
    protected static function prepareItem($item, $urlParamItemid): \stdClass
    {
        $date = Factory::getDate($item->created);

        $createdMonth = $date->format('n');
        $createdYear  = $date->format('Y');

        $createdYearCal = HTMLHelper::_('date', $item->created, 'Y');
        $monthNameCal   = HTMLHelper::_('date', $item->created, 'F');

        $archivedArticle = new \stdClass();

        $archivedArticle->link = Route::_('index.php?option=com_content&view=archive&year=' . $createdYear . '&month=' . $createdMonth . $urlParamItemid);
        $archivedArticle->text = Text::sprintf('MOD_ARTICLES_ARCHIVE_DATE', $monthNameCal, $createdYearCal);

        return $archivedArticle;
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
        return (new self())->getArticles($params, Factory::getApplication());
    }
}
