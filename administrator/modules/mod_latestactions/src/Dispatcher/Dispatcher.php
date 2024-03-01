<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_latestactions
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\LatestActions\Administrator\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_latestactions
 *
 * @since  __DEPLOY_VERSION__
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Runs the dispatcher.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function dispatch()
    {
        if (!$this->getApplication()->getIdentity()->authorise('core.admin')) {
            return;
        }

        parent::dispatch();
    }

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $data['list'] = $this->getHelperFactory()->getHelper('LatestActionsHelper')->getActions($data['params']);

        if ($data['params']->get('automatic_title', 0)) {
            $this->module->title = $this->getHelperFactory()->getHelper('LatestActionsHelper')->getModuleTitle($data['params']);
        }

        return $data;
    }
}
