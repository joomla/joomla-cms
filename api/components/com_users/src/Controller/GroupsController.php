<?php

/**
 * @package     Joomla.API
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Api\Controller;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\MVC\Controller\ApiController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The groups controller
 *
 * @since  4.0.0
 */
class GroupsController extends ApiController
{
    /**
     * The content type of the item.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $contentType = 'groups';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  3.0
     */
    protected $default_view = 'groups';

    /**
     * Removes an item.
     *
     * @param   integer  $id  The primary key to delete item.
     *
     * @return  void
     *
     * @since   5.4.6
     */
    public function delete($id = null)
    {
        if (!$this->app->getIdentity()->authorise('core.admin', $this->option)) {
            throw new NotAllowed('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED', 403);
        }

        parent::delete($id);
    }

    /**
     * Method to check if you can add a new record.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  boolean
     *
     * @since   5.4.6
     */
    public function allowAdd($data = [])
    {
        // Overrides the default behavior to check the core.admin permission.
        return $this->app->getIdentity()->authorise('core.admin', $this->option);
    }

    /**
     * Method to check if you can edit an existing record.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key; default is id.
     *
     * @return  boolean
     *
     * @since   5.4.6
     */
    public function allowEdit($data = [], $key = 'id')
    {
        // Overrides the default behavior to check the core.admin permission.
        return $this->app->getIdentity()->authorise('core.admin', $this->option);
    }
}
