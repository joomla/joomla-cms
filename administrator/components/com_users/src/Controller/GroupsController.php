<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Controller;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * User groups list controller class.
 *
 * @since  1.6
 */
class GroupsController extends AdminController
{
    /**
     * @var     string  The prefix to use with controller messages.
     * @since   1.6
     */
    protected $text_prefix = 'COM_USERS_GROUPS';

    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  object  The model.
     *
     * @since   1.6
     */
    public function getModel($name = 'Group', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Removes an item.
     *
     * Overrides Joomla\CMS\MVC\Controller\AdminController::delete to check the core.admin permission.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function delete()
    {
        if (!$this->app->getIdentity()->authorise('core.admin', $this->option)) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        parent::delete();
    }

    /**
     * Method to publish a list of records.
     *
     * Overrides Joomla\CMS\MVC\Controller\AdminController::publish to check the core.admin permission.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function publish()
    {
        if (!$this->app->getIdentity()->authorise('core.admin', $this->option)) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        parent::publish();
    }

    /**
     * Changes the order of one or more records.
     *
     * Overrides Joomla\CMS\MVC\Controller\AdminController::reorder to check the core.admin permission.
     *
     * @return  boolean  True on success
     *
     * @since   1.6
     */
    public function reorder()
    {
        if (!$this->app->getIdentity()->authorise('core.admin', $this->option)) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        return parent::reorder();
    }

    /**
     * Method to save the submitted ordering values for records.
     *
     * Overrides Joomla\CMS\MVC\Controller\AdminController::saveorder to check the core.admin permission.
     *
     * @return  boolean  True on success
     *
     * @since   1.6
     */
    public function saveorder()
    {
        if (!$this->app->getIdentity()->authorise('core.admin', $this->option)) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        return parent::saveorder();
    }

    /**
     * Check in of one or more records.
     *
     * Overrides Joomla\CMS\MVC\Controller\AdminController::checkin to check the core.admin permission.
     *
     * @return  boolean  True on success
     *
     * @since   1.6
     */
    public function checkin()
    {
        if (!$this->app->getIdentity()->authorise('core.admin', $this->option)) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        return parent::checkin();
    }
}
