<?php

/**
 * @package     Joomla.API
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Api\Controller;

use Joomla\CMS\MVC\Controller\ApiController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The levels controller
 *
 * @since  4.0.0
 */
class LevelsController extends ApiController
{
    /**
     * The content type of the item.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $contentType = 'levels';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $default_view = 'levels';

    /**
     * Method to allow extended classes to manipulate the data to be saved for an extension.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  array
     *
     * @since   5.4.7
     */
    protected function preprocessSaveData(array $data): array
    {
        if ($this->input->getMethod() === 'PATCH') {
            $data['rules'] = json_decode($data['rules'], true);
        }

        return $data;
    }

    /**
     * Method to check if it's allowed to delete a record
     *
     * @return  boolean
     *
     * @since   5.4.9
     */
    protected function allowDelete(): bool
    {
        // Overrides the default behavior to check the core.admin permission.
        return $this->app->getIdentity()->authorise('core.admin', $this->option);
    }

    /**
     * Method to check if you can add a new record.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  boolean
     *
     * @since   5.4.9
     */
    protected function allowAdd($data = [])
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
     * @since   5.4.9
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        // Overrides the default behavior to check the core.admin permission.
        return $this->app->getIdentity()->authorise('core.admin', $this->option);
    }
}
