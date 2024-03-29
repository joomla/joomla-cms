<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\TagsPopular\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Helper\ModuleHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_tags_popular
 *
 * @since  5.1.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Runs the dispatcher.
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function dispatch()
    {
        $displayData = $this->getLayoutData();

        if (!\count($displayData['list']) && !$displayData['params']->get('no_results_text')) {
            return;
        }

        parent::dispatch();
    }

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
        $cacheparams->class        = $this->getHelperFactory()->getHelper('TagsPopularHelper');
        $cacheparams->method       = 'getTags';
        $cacheparams->methodparams = $data['params'];
        $cacheparams->modeparams   = ['id' => 'array', 'Itemid' => 'int'];

        $data['list']          = ModuleHelper::moduleCache($this->module, $data['params'], $cacheparams);
        $data['display_count'] = $data['params']->get('display_count', 0);

        return $data;
    }
}
