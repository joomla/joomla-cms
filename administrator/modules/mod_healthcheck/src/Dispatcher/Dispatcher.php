<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_healthcheck
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Healthcheck\Administrator\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_healthcheck
 *
 * @since  __DEPLOY_VERSION__
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

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

        $helper = $this->getHelperFactory()->getHelper('HealthCheckHelper');

        $data['gauges']  = $helper->getGauges($data['params'], $this->getApplication());
        $data['buttons'] = $helper->getButtons($data['params'], $this->getApplication());
        $data['lists']   = $helper->getLists($data['params'], $this->getApplication());
        $data['tables']  = $helper->getTables($data['params'], $this->getApplication());
        $data['reports'] = $helper->getReports($data['params'], $this->getApplication());

        $data['leading'] = $helper->getLeading($data['params'], $this->getApplication());
        $data['footer']  = $helper->getFooter($data['params'], $this->getApplication());

        return $data;
    }
}
