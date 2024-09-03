<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_articles
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Articles\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Helper\ModuleHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_articles
 *
 * @since  5.2.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   5.2.0
     */
    protected function getLayoutData(): array
    {
        $data   = parent::getLayoutData();
        $params = $data['params'];

        $cacheParams               = new \stdClass();
        $cacheParams->cachemode    = 'id';
        $cacheParams->class        = $this->getHelperFactory()->getHelper('ArticlesHelper');
        $cacheParams->method       = 'getArticles';
        $cacheParams->methodparams = [$params, $data['app']];
        $cacheParams->modeparams   = md5(serialize([$params->get('catid'), $this->module->module, $this->module->id]));

        $data['list'] = ModuleHelper::moduleCache($this->module, $params, $cacheParams);

        $data['grouped'] = $params->get('article_grouping', 'none') !== 'none';

        return $data;
    }
}
