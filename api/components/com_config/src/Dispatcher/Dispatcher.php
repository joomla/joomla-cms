<?php

/**
 * @package     Joomla.Api
 * @subpackage  com_config
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Api\Dispatcher;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Dispatcher\ApiDispatcher;
use Joomla\Component\Config\Administrator\Helper\ConfigHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * ComponentDispatcher class for com_config
 *
 * @since  6.2.1
 */
class Dispatcher extends ApiDispatcher
{
    /**
     * Check if the user have the right access to the component config
     *
     * @return  void
     *
     * @since  6.2.1
     *
     * @throws  \Exception
     */
    protected function checkAccess(): void
    {
        $component  = $this->input->getCmd('component_name');
        $controller = $this->input->getCmd('controller');

        // Check the user has permission to access this component
        if ($controller === 'component' && ConfigHelper::canChangeComponentConfig($component)) {
            return;
        }

        if ($this->app->getIdentity()->authorise('core.admin')) {
            return;
        }

        throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
    }
}
