<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_logged
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Logged\Administrator\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Database\DatabaseInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_logged
 *
 * @since   __DEPLOY_VERSION_
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Runs the dispatcher.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION_
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

            // Check if session metadata tracking is enabled
            if ($app->get('session_metadata', true)) {
                require ModuleHelper::getLayoutPath('mod_logged', $params->get('layout', 'default'));
            } else {
                require ModuleHelper::getLayoutPath('mod_logged', 'disabled');
            }
        };

        $loader($displayData);
    }

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION_
     */
    protected function getLayoutData()
    {
        $data   = parent::getLayoutData();
        $helper = $this->getHelperFactory()->getHelper('LoggedHelper');

        if ($data['params']->get('automatic_title', 0)) {
            $data['module']->title = $helper->getModuleTitle($data['params']);
        }

        // Check if session metadata tracking is enabled
        if ($data['app']->get('session_metadata', true)) {
            $data['users'] = $helper->getUsers($data['params'], $data['app'], Factory::getContainer()->get(DatabaseInterface::class));
        }

        return $data;
    }
}
