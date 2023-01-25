<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Administrator\Dispatcher;

use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * ComponentDispatcher class for com_config
 *
 * @since  __DEPLOY_VERSION__
 */
class Dispatcher extends ComponentDispatcher
{
    /**
     * Check if the user have the right access to the component config
     *
     * @return  void
     */
    protected function checkAccess()
    {
        $component = $this->input->get('component');

        // If not a SuperUser, check the permissions to the component config
        if (
            !$this->app->getIdentity()->authorise('core.admin', $component)
            && !$this->app->getIdentity()->authorise('core.options', $component)
        ) {
            $controller = $this->getController('display', ucfirst($this->app->getName()));
            $controller->setRedirect(Route::_('index.php?option=' . $component, false), Text::_('JERROR_ALERTNOAUTHOR'), 'error')->redirect();
        }
    }
}
