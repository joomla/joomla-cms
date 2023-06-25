<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Modules manager display controller.
 *
 * @since  1.6
 */
class DisplayController extends BaseController
{
    /**
     * The default view.
     *
     * @var    string
     * @since  1.6
     */
    protected $default_view = 'modules';

    /**
     * Method to display a view.
     *
     * @param   boolean        $cachable   If true, the view output will be cached
     * @param   array|boolean  $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}
     *
     * @return  static|boolean   This object to support chaining or false on failure.
     *
     * @since   1.5
     */
    public function display($cachable = false, $urlparams = false)
    {
        $layout = $this->input->get('layout', 'edit');
        $id     = $this->input->getInt('id');

        // Verify client
        $clientId = $this->input->post->getInt('client_id');

        if (!is_null($clientId)) {
            $uri = Uri::getInstance();

            if ((int) $uri->getVar('client_id') !== (int) $clientId) {
                $this->setRedirect(Route::_('index.php?option=com_modules&view=modules&client_id=' . $clientId, false));

                return false;
            }
        }

        // Check for edit form.
        if ($layout == 'edit' && !$this->checkEditId('com_modules.edit.module', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            if (!\count($this->app->getMessageQueue())) {
                $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
            }

            $this->setRedirect(Route::_('index.php?option=com_modules&view=modules&client_id=' . $this->input->getInt('client_id'), false));

            return false;
        }

        // Check if we have a mod_menu module set to All languages or a mod_menu module for each admin language.
        $factory = $this->app->bootComponent('menus')->getMVCFactory();

        if ($langMissing = $factory->createModel('Menus', 'Administrator')->getMissingModuleLanguages()) {
            $this->app->enqueueMessage(Text::sprintf('JMENU_MULTILANG_WARNING_MISSING_MODULES', implode(', ', $langMissing)), 'warning');
        }

        return parent::display();
    }
}
