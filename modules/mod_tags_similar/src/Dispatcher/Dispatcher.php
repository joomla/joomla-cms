<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_similar
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\TagsSimilar\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Helper\ModuleHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_tags_similar
 *
 * @since  5.1.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   5.1.0
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $cacheparams               = new \stdClass();
        $cacheparams->cachemode    = 'safeuri';
        $cacheparams->class        = $this->getHelperFactory()->getHelper('TagsSimilarHelper');
        $cacheparams->method       = 'getItems';
        $cacheparams->methodparams = $data['params'];
        $cacheparams->modeparams   = ['id' => 'array', 'Itemid' => 'int'];

        $data['list'] = ModuleHelper::moduleCache($this->module, $data['params'], $cacheparams);

        return $data;
    }
}
