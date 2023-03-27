<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Controller;

use Joomla\CMS\MVC\Controller\FormController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller for a single Tour
 *
 * @since 4.3.0
 */
class TourController extends FormController
{
    /**
     * Method to check if you can add a new record.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function allowAdd($data = [])
    {
        return $this->app->getIdentity()->authorise('core.create', $this->option);
    }

    /**
     * Method to check if you can edit a record.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        $recordId = (int)$data[$key] ?? 0;
        $user     = $this->app->getIdentity();

        // Check "edit" permission on record asset
        if ($user->authorise('core.edit', 'com_guidedtours.tour.' . $recordId)) {
            return true;
        }

        // Check "edit own" permission on record asset
        if ($user->authorise('core.edit.own', 'com_guidedtours.tour.' . $recordId)) {
            // Need to do a lookup from the model to get the owner
            $record = $this->getModel()->getItem($recordId);

            if (empty($record)) {
                return false;
            }

            if ($record->created_by == $user->id) {
                return true;
            }
        }

        return false;
    }
}
