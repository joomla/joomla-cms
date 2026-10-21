<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_whosonline
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Whosonline\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Helper\ModuleHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_whosonline
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

            if ($app->get('session_metadata', true)) {
                require ModuleHelper::getLayoutPath('mod_whosonline', $params->get('layout', 'default'));
            } else {
                require ModuleHelper::getLayoutPath('mod_whosonline', 'disabled');
            }
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
    protected function getLayoutData(): array
    {
        $data   = parent::getLayoutData();
        $helper = $this->getHelperFactory()->getHelper('WhosonlineHelper');

        // Check if session metadata tracking is enabled
        if ($data['app']->get('session_metadata', true)) {
            $data['showmode'] = $data['params']->get('showmode', 0);

            if ($data['showmode'] == 0 || $data['showmode'] == 2) {
                $data['count'] = $helper->getOnlineUsersCount($data['app']);
            }

            if ($data['showmode'] > 0) {
                $data['names'] = $helper->fetchOnlineUserNames($data['app'], $data['params']);
            }
        }

        return $data;
    }
}
