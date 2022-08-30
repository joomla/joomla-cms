<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Redirect\Administrator\Controller;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Redirect\Administrator\Helper\RedirectHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Redirect master display controller.
 *
 * @since  1.6
 */
class DisplayController extends BaseController
{
    /**
     * @var     string  The default view.
     * @since   1.6
     */
    protected $default_view = 'links';

    /**
     * Method to display a view.
     *
     * @param   boolean  $cachable   If true, the view output will be cached.
     * @param   mixed    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
     *
     * @return  static|boolean   This object to support chaining or false on failure.
     *
     * @since   1.5
     */
    public function display($cachable = false, $urlparams = false)
    {
        $view   = $this->input->get('view', 'links');
        $layout = $this->input->get('layout', 'default');
        $id     = $this->input->getInt('id');

        if ($view === 'links') {
            $pluginEnabled      = PluginHelper::isEnabled('system', 'redirect');
            $collectUrlsEnabled = RedirectHelper::collectUrlsEnabled();

            // Show messages about the enabled plugin and if the plugin should collect URLs
            if ($pluginEnabled && $collectUrlsEnabled) {
                $this->app->enqueueMessage(Text::sprintf('COM_REDIRECT_COLLECT_URLS_ENABLED', Text::_('COM_REDIRECT_PLUGIN_ENABLED')), 'notice');
            } else {
                $redirectPluginId = RedirectHelper::getRedirectPluginId();
                $link = HTMLHelper::_(
                    'link',
                    '#plugin' . $redirectPluginId . 'Modal',
                    Text::_('COM_REDIRECT_SYSTEM_PLUGIN'),
                    'class="alert-link" data-bs-toggle="modal" id="title-' . $redirectPluginId . '"'
                );

                if ($pluginEnabled && !$collectUrlsEnabled) {
                    $this->app->enqueueMessage(
                        Text::sprintf('COM_REDIRECT_COLLECT_MODAL_URLS_DISABLED', Text::_('COM_REDIRECT_PLUGIN_ENABLED'), $link),
                        'notice'
                    );
                } else {
                    $this->app->enqueueMessage(Text::sprintf('COM_REDIRECT_PLUGIN_MODAL_DISABLED', $link), 'error');
                }
            }
        }

        // Check for edit form.
        if ($view == 'link' && $layout == 'edit' && !$this->checkEditId('com_redirect.edit.link', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            if (!\count($this->app->getMessageQueue())) {
                $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
            }

            $this->setRedirect(Route::_('index.php?option=com_redirect&view=links', false));

            return false;
        }

        return parent::display();
    }
}
