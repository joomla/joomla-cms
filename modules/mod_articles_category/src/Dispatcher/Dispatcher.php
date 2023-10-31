<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_category
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\ArticlesCategory\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Helper\ModuleHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_articles_category
 *
 * @since  4.4.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   4.4.0
     */
    protected function getLayoutData(): array
    {
        $data   = parent::getLayoutData();
        $params = $data['params'];

        // Prep for Normal or Dynamic Modes
        $mode   = $params->get('mode', 'normal');
        $idBase = null;

        switch ($mode) {
            case 'dynamic':
                $option = $data['input']->get('option');
                $view   = $data['input']->get('view');

                if ($option === 'com_content') {
                    switch ($view) {
                        case 'category':
                        case 'categories':
                            $idBase = $data['input']->getInt('id');
                            break;
                        case 'article':
                            if ($params->get('show_on_article_page', 1)) {
                                $idBase = $data['input']->getInt('catid');
                            }
                            break;
                    }
                }
                break;
            default:
                $idBase = $params->get('catid');
                break;
        }

        $cacheParams               = new \stdClass();
        $cacheParams->cachemode    = 'id';
        $cacheParams->class        = $this->getHelperFactory()->getHelper('ArticlesCategoryHelper');
        $cacheParams->method       = 'getArticles';
        $cacheParams->methodparams = [$params, $data['app']];
        $cacheParams->modeparams   = md5(serialize([$idBase, $this->module->module, $this->module->id]));

        $data['list'] = ModuleHelper::moduleCache($this->module, $params, $cacheParams);

        $data['grouped'] = $params->get('article_grouping', 'none') !== 'none';

        return $data;
    }
}
