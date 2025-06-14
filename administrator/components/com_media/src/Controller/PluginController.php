<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Controller;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Media\Administrator\Event\OAuthCallbackEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Plugin Controller for OAuth2.0 callbacks
 *
 * This controller handles OAuth2 Callbacks
 *
 * @since  4.0.0
 */
class PluginController extends BaseController
{
    /**
     * Handles an OAuth Callback request for a specified plugin.
     *
     * URLs containing [sitename]/administrator/index.php?option=com_media&task=plugin.oauthcallback
     *  &plugin=[plugin_name]
     *
     * will be handled by this endpoint.
     * It will select the plugin specified by plugin_name and pass all the data received from the provider
     *
     * @return void
     *
     * @since  4.0.0
     */
    public function oauthcallback()
    {
        try {
            // Load plugin names
            $pluginName = $this->input->getString('plugin', null);
            $plugins    = PluginHelper::getPlugin('filesystem');

            // If plugin name was not found in parameters redirect back to control panel
            if (!$pluginName || !$this->containsPlugin($plugins, $pluginName)) {
                throw new \Exception('Plugin not found!');
            }

            // Check if the plugin is disabled, if so redirect to control panel
            if (!PluginHelper::isEnabled('filesystem', $pluginName)) {
                throw new \Exception('Plugin ' . $pluginName . ' is disabled.');
            }

            // Only import our required plugin, not entire group
            PluginHelper::importPlugin('filesystem', $pluginName);

            // Event parameters
            $eventParameters = ['context' => $pluginName, 'input' => $this->input];
            $event           = new OAuthCallbackEvent('onFileSystemOAuthCallback', $eventParameters);

            // Get results from event
            $eventResults = (array) $this->app->triggerEvent('onFileSystemOAuthCallback', $event);

            // If event was not triggered in the selected Plugin, raise a warning and fallback to Control Panel
            if (!$eventResults) {
                throw new \Exception(
                    'Plugin ' . $pluginName . ' should have implemented onFileSystemOAuthCallback method'
                );
            }

            $action  = $eventResults['action'] ?? null;

            // If there are any messages display them
            if (isset($eventResults['message'])) {
                $message     = $eventResults['message'];
                $messageType = ($eventResults['message_type'] ?? '');

                $this->app->enqueueMessage($message, $messageType);
            }

            /**
             * Execute actions defined by the plugin
             * Supported actions
             *  - close         : Closes the current window, use this only for windows opened by javascript
             *  - redirect      : Redirect to a URI defined in 'redirect_uri' parameter, if not fallback to control panel
             *  - media-manager : Redirect to Media Manager
             *  - control-panel : Redirect to Control Panel
             */
            switch ($action) {
                case 'close':
                    /**
                     * Close a window opened by developer
                     * Use this for close New Windows opened for OAuth Process
                     */
                    $this->setRedirect(Route::_('index.php?option=com_media&view=plugin&action=close', false));
                    break;

                case 'redirect':
                    // Redirect browser to any page specified by the user
                    if (!isset($eventResults['redirect_uri'])) {
                        throw new \Exception("Redirect URI must be set in the plugin");
                    }

                    $this->setRedirect($eventResults['redirect_uri']);
                    break;

                case 'control-panel':
                    // Redirect browser to Control Panel
                    $this->setRedirect(Route::_('index.php', false));
                    break;

                case 'media-manager':
                default:
                    // Redirect browser to Media Manager
                    $this->setRedirect(Route::_('index.php?option=com_media&view=media', false));
            }
        } catch (\Exception $e) {
            // Display any error
            $this->app->enqueueMessage($e->getMessage(), 'error');
            $this->setRedirect(Route::_('index.php', false));
        }

        // Redirect
        $this->redirect();
    }

    /**
     * Check whether a plugin exists in given plugin array.
     *
     * @param   array   $plugins     Array of plugin names
     * @param   string  $pluginName  Plugin name to look up
     *
     * @return bool
     *
     * @since  4.0.0
     */
    private function containsPlugin($plugins, $pluginName)
    {
        foreach ($plugins as $plugin) {
            if ($plugin->name == $pluginName) {
                return true;
            }
        }

        return false;
    }
}
