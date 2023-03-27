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
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller for a single step
 *
 * @since 4.3.0
 */
class StepController extends FormController
{
    /**
     * Method override to check if you can add a new record.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function allowAdd($data = [])
    {
        $tourId = ArrayHelper::getValue($data, 'tour_id', $this->app->getUserState('com_guidedtours.tour_id', 0), 'int');

        if ($tourId) {
            // If the category has been passed in the data or URL check it.
            return $this->app->getIdentity()->authorise('core.create', 'com_guidedtours.tour.' . $tourId);
        }

        // In the absence of better information, revert to the component permissions.
        return parent::allowAdd();
    }

    /**
     * Method override to check if you can edit an existing record.
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
        $tourId   = (int)$data['tour_id'] ?? $this->app->getUserState('com_guidedtours.tour_id', 0);

        // Zero record (id:0), return component edit permission by calling parent controller method
        if (!$recordId) {
            return parent::allowEdit($data, $key);
        }

        // Check edit on the record asset
        if ($user->authorise('core.edit', 'com_guidedtours.tour.' . $tourId)) {
            return true;
        }

        // Check edit own on the record asset
        if ($user->authorise('core.edit.own', 'com_guidedtours.tour.' . $tourId)) {
            // Existing record already has an owner, get it
            $record = $this->getModel()->getItem($recordId);

            if (empty($record)) {
                return false;
            }

            // Grant if current user is owner of the record
            return $user->id == $record->created_by;
        }

        return false;
    }
}
