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
use Joomla\CMS\Helper\ModuleHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_login
 *
 * @since  5.4.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Runs the dispatcher.
     *
     * @return  void
     *
     * @since   5.4.0
     */
    public function dispatch()
    {
        $this->loadLanguage();

        $displayData = $this->getLayoutData();

        // Stop when display data is false
        if ($displayData === false) {
            return;
        }

        // Execute the layout without the module context
        $loader = static function (array $displayData) {
            // If $displayData doesn't exist in extracted data, unset the variable.
            if (!\array_key_exists('displayData', $displayData)) {
                extract($displayData);
                unset($displayData);
            } else {
                extract($displayData);
            }

            /**
             * Extracted variables
             * -----------------
             * @var   \stdClass  $module
             * @var   Registry   $params
             */

            // Logged users must load the logout sublayout
            if (!$user->guest) {
                $layout .= '_logout';
            }

            require ModuleHelper::getLayoutPath('mod_login', $layout);
        };

        $loader($displayData);
    }

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   5.4.0
     */
    protected function getLayoutData()
    {
        $data   = parent::getLayoutData();
        $helper = $this->getHelperFactory()->getHelper('LoginHelper');

        $data['params']->def('greeting', 1);

        // HTML IDs
        $formId               = 'login-form-' . $data['module']->id;
        $data['user']         = $data['app']->getIdentity();
        $type                 = $helper->getUserType($data['user']);
        $data['return']       = $helper->getReturnUrlString($data['params'], $type, $data['app']);
        $data['registerLink'] = $helper->getRegistrationUrlString($data['params'], $data['app']);
        $data['extraButtons'] = AuthenticationHelper::getLoginButtons($formId);
        $data['layout']       = $data['params']->get('layout', 'default');

        return $data;
    }
}
