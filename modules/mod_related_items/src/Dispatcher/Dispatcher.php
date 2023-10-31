<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_related_items
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\RelatedItems\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Helper\ModuleHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_articles_popular
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

        $cacheParams               = new \stdClass();
        $cacheParams->cachemode    = 'safeuri';
        $cacheParams->class        = $this->getHelperFactory()->getHelper('RelatedItemsHelper');
        $cacheParams->method       = 'getRelatedArticles';
        $cacheParams->methodparams = [$params, $data['app']];
        $cacheParams->modeparams   = ['id' => 'int', 'Itemid' => 'int'];

        $data['list']     = ModuleHelper::moduleCache($this->module, $params, $cacheParams);
        $data['showDate'] = $params->get('showDate', 0);

        return $data;
    }
}
