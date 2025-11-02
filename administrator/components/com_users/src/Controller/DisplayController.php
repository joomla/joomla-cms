<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Controller;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Users display controller.
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
    protected $default_view = 'users';

    /**
     * Checks whether a user can see this view.
     *
     * @param   string  $view  The view name.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    protected function canView($view)
    {
        $canDo = ContentHelper::getActions('com_users');

        switch ($view) {
            case 'groups':
            case 'group':
            case 'levels':
            case 'level':
                // Special permissions.
                return $canDo->get('core.admin');

            default:
                // Default permissions.
                return true;
        }
    }

    /**
     * Method to display a view.
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe URL parameters and their variable types.
     *                   @see        \Joomla\CMS\Filter\InputFilter::clean() for valid values.
     *
     * @return  BaseController|boolean  This object to support chaining or false on failure.
     *
     * @since   1.5
     */
    public function display($cachable = false, $urlparams = [])
    {
        $view   = $this->input->get('view', 'users');
        $layout = $this->input->get('layout', 'default');
        $id     = $this->input->getInt('id');

        if (!$this->canView($view)) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        // Check for edit form.
        if ($view === 'user' && $layout === 'edit' && !$this->checkEditId('com_users.edit.user', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            if (!\count($this->app->getMessageQueue())) {
                $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
            }

            $this->setRedirect(Route::_('index.php?option=com_users&view=users', false));

            return false;
        }

        if ($view === 'group' && $layout === 'edit' && !$this->checkEditId('com_users.edit.group', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            if (!\count($this->app->getMessageQueue())) {
                $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
            }

            $this->setRedirect(Route::_('index.php?option=com_users&view=groups', false));

            return false;
        }

        if ($view === 'level' && $layout === 'edit' && !$this->checkEditId('com_users.edit.level', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            if (!\count($this->app->getMessageQueue())) {
                $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
            }

            $this->setRedirect(Route::_('index.php?option=com_users&view=levels', false));

            return false;
        }

        if ($view === 'note' && $layout === 'edit' && !$this->checkEditId('com_users.edit.note', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            if (!\count($this->app->getMessageQueue())) {
                $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
            }

            $this->setRedirect(Route::_('index.php?option=com_users&view=notes', false));

            return false;
        }

        if (\in_array($view, ['captive', 'callback', 'methods', 'method'])) {
            $controller = $this->factory->createController($view, 'Administrator', [], $this->app, $this->input);
            $task       = $this->input->get('task', '');

            return $controller->execute($task);
        }

        return parent::display($cachable, $urlparams);
    }
}
