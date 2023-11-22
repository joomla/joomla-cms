<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Templates\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Templates manager display controller.
 *
 * @since  1.6
 */
class DisplayController extends BaseController
{
    /**
     * @var     string  The default view.
     * @since   1.6
     */
    protected $default_view = 'styles';

    /**
     * Method to display a view.
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   boolean  $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
     *
     * @return  static|boolean   This object to support chaining or false on failure.
     *
     * @since   1.5
     */
    public function display($cachable = false, $urlparams = false)
    {
        $view   = $this->input->get('view', 'styles');
        $layout = $this->input->get('layout', 'default');
        $id     = $this->input->getInt('id');

        // For JSON requests
        if ($this->app->getDocument()->getType() == 'json') {
            return parent::display();
        }

        // Check for edit form.
        if ($view == 'style' && $layout == 'edit' && !$this->checkEditId('com_templates.edit.style', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            if (!\count($this->app->getMessageQueue())) {
                $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
            }

            $this->setRedirect(Route::_('index.php?option=com_templates&view=styles', false));

            return false;
        }

        return parent::display();
    }
}
