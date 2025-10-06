<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Versioning\VersionableModelTrait;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Client model.
 *
 * @since  1.6
 */
class ClientModel extends AdminModel
{
    use VersionableModelTrait;

    /**
     * The type alias for this content type.
     *
     * @var    string
     * @since  3.2
     */
    public $typeAlias = 'com_banners.client';

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
     *
     * @since   1.6
     */
    protected function canDelete($record)
    {
        if (empty($record->id) || $record->state != -2) {
            return false;
        }

        if (!empty($record->catid)) {
            return $this->getCurrentUser()->authorise('core.delete', 'com_banners.category.' . (int) $record->catid);
        }

        return parent::canDelete($record);
    }

    /**
     * Method to test whether a record can have its state changed.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to change the state of the record.
     *                   Defaults to the permission set in the component.
     *
     * @since   1.6
     */
    protected function canEditState($record)
    {
        $user = $this->getCurrentUser();

        if (!empty($record->catid)) {
            return $user->authorise('core.edit.state', 'com_banners.category.' . (int) $record->catid);
        }

        return $user->authorise('core.edit.state', 'com_banners');
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  \Joomla\CMS\Form\Form|boolean  A Form object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_banners.client', 'client', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_banners.edit.client.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_banners.client', $data);

        return $data;
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param   Table  $table  A Table object.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function prepareTable($table)
    {
        $table->name = htmlspecialchars_decode($table->name, ENT_QUOTES);
    }

        /**
     * Override save to prevent duplicate client names when saving as copy.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   5.3.5
     */
    public function save($data)
    {
        $table = $this->getTable();

        // Check for duplicate name BEFORE saving
        if (isset($data['name']) && !empty($data['name']))
        {
            $existing = $this->getTable();

            // Load a record with the same name
            if ($existing->load(['name' => $data['name']]))
            {
                // If this is a new record or a copy, block it
                if (empty($data['id']) || (int) $existing->id !== (int) $data['id'])
                {
                    $this->setError(\Joomla\CMS\Language\Text::_('COM_BANNERS_CLIENT_ERROR_DUPLICATE_NAME'));
                    return false; // Stop saving
                }
            }
        }

        // No duplicate found, continue saving
        return parent::save($data);
    }



}
