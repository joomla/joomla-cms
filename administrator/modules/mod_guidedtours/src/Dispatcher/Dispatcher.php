<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_guidedtours
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\GuidedTours\Administrator\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Plugin\PluginHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_guidedtours
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
        // The guided tour will not show if no user is logged in.
        $user = $this->getApplication()->getIdentity();
        if ($user === null || $user->id === 0) {
            return;
        }

        // The module can't show if the plugin is not enabled.
        if (!PluginHelper::isEnabled('system', 'tour')) {
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

        $data['tours'] = $this->getHelperFactory()->getHelper('GuidedToursHelper')->getTours($data['params'], $this->getApplication());

        return $data;
    }
}
