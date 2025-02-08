<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Login\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\AuthenticationHelper;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_login
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
        $data   = parent::getLayoutData();
        $helper = $this->getHelperFactory()->getHelper('LoginHelper');

        $data['params']->def('greeting', 1);

        // HTML IDs
        $formId               = 'login-form-' . $data['module']->id;
        $type                 = $helper->getModuleType($data['app']);
        $data['return']       = $helper->getReturnUrlString($data['params'], $type, $data['app']);
        $data['registerLink'] = $helper->getRegistrationUrlString($data['params'], $data['app']);
        $data['extraButtons'] = AuthenticationHelper::getLoginButtons($formId);
        $data['user']         = $data['app']->getIdentity();
        $layout               = $data['params']->get('layout', 'default');

        // Logged users must load the logout sublayout
        if (!$data['user']->guest) {
            $data['params']->set('layout', $layout . '_logout');
        }

        return $data;
    }
}
