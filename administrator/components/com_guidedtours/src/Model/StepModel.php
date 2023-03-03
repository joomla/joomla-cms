<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Item Model for a single tour.
 *
 * @since __DEPLOY_VERSION__
 */

class StepModel extends AdminModel
{
    /**
     * The prefix to use with controller messages.
     *
     * @var   string
     * @since __DEPLOY_VERSION__
     */
    protected $text_prefix = 'COM_GUIDEDTOURS';

    /**
     * Type alias for content type
     *
     * @var   string
     * @since __DEPLOY_VERSION__
     */
    public $typeAlias = 'com_guidedtours.step';

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function canDelete($record)
    {
        if (empty($record->id) || $record->published != -2) {
            return false;
        }

        $table = $this->getTable('Tour', 'Administrator');

        $table->load($record->tour_id);

        $app       = Factory::getApplication();
        $extension = $app->getUserStateFromRequest('com_guidedtours.step.filter.extension', 'extension', null, 'cmd');

        $parts = explode('.', $extension);

        $component = reset($parts);

        if (!$this->getCurrentUser()->authorise('core.delete', $component . '.state.' . (int) $record->id)) {
            $this->setError(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));

            return false;
        }

        return true;
    }

    /**
     * Method to change the title
     *
     * @param   integer  $categoryId  The id of the category.
     * @param   string   $alias       The alias.
     * @param   string   $title       The title.
     *
     * @return  array  Contains the modified title and alias.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function generateNewTitle($categoryId, $alias, $title)
    {
        // Alter the title
        $table = $this->getTable();

        while ($table->load(['title' => $title])) {
            $title = StringHelper::increment($title);
        }

        return [$title, $alias];
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function save($data)
    {
        $table      = $this->getTable();
        $context    = $this->option . '.' . $this->name;
        $app        = Factory::getApplication();
        $input      = $app->input;
        $tourID     = $app->getUserStateFromRequest($context . '.filter.tour_id', 'tour_id', 0, 'int');

        if (empty($data['tour_id'])) {
            $data['tour_id'] = $tourID;
        }

        $tour = $this->getTable('Tour');

        $tour->load($data['tour_id']);

        // Language keys must include GUIDEDTOUR to prevent save issues
        if (strpos($data['description'], 'GUIDEDTOUR') !== false) {
            $data['description'] = strip_tags($data['description']);
        }

        // Make sure we use the correct extension when editing an existing tour
        $key = $table->getKeyName();
        $pk  = isset($data[$key]) ? $data[$key] : (int) $this->getState($this->getName() . '.id');

        if ($pk > 0) {
            $table->load($pk);

            if ((int) $table->tour_id) {
                $data['tour_id'] = (int) $table->tour_id;
            }
        }

        if ($input->get('task') == 'save2copy') {
            $origTable = clone $this->getTable();
            $origTable->load($input->getInt('id'));

            if ($data['title'] == $origTable->title) {
                list($title)   = $this->generateNewTitle(0, '', $data['title']);
                $data['title'] = $title;
            }

            $data['published'] = 0;
        }

        return parent::save($data);
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param   \Joomla\CMS\Table\Table  $table  The Table object
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function prepareTable($table)
    {
        $date = Factory::getDate()->toSql();

        $table->title = htmlspecialchars_decode($table->title, ENT_QUOTES);

        if (empty($table->id)) {
            // Set the values
            $table->created = $date;

            // Set ordering to the last item if not set
            if (empty($table->ordering)) {
                $db    = $this->getDatabase();
                $query = $db->getQuery(true)
                    ->select('MAX(ordering)')
                    ->from($db->quoteName('#__guidedtour_steps'));
                $db->setQuery($query);
                $max = $db->loadResult();

                $table->ordering = $max + 1;
            }
        } else {
            // Set the values
            $table->modified    = $date;
            $table->modified_by = $this->getCurrentUser()->id;
        }
    }

    /**
     * Method to test whether a record can have its state changed.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to change the state of the record.
     * Defaults to the permission set in the component.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function canEditState($record)
    {
        $user      = $this->getCurrentUser();
        $app       = Factory::getApplication();
        $context   = $this->option . '.' . $this->name;
        $extension = $app->getUserStateFromRequest($context . '.filter.extension', 'extension', null, 'cmd');

        if (!\property_exists($record, 'tour_id')) {
            $tourID          = $app->getUserStateFromRequest($context . '.filter.tour_id', 'tour_id', 0, 'int');
            $record->tour_id = $tourID;
        }

        // Check for existing tour.
        if (!empty($record->id)) {
            return $user->authorise('core.edit.state', $extension . '.state.' . (int) $record->id);
        }

        // Default to component settings if tour isn't known.
        return $user->authorise('core.edit.state', $extension);
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string $type    The table name. Optional.
     * @param   string $prefix  The class prefix. Optional.
     * @param   array  $config  Configuration array for model. Optional.
     *
     * @return  Table  A Table object
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception
     */
    public function getTable($type = 'Step', $prefix = 'Administrator', $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Abstract method for getting the form from the model.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  \JForm|boolean  A JForm object on success, false on failure
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            'com_guidedtours.step',
            'step',
            [
                'control'   => 'jform',
                'load_data' => $loadData,
            ]
        );

        if (empty($form)) {
            return false;
        }

        $id = $data['id'] ?? $form->getValue('id');

        $item = $this->getItem($id);

        $canEditState = $this->canEditState((object) $item);

        // Modify the form based on access controls.
        if (!$canEditState) {
            $form->setFieldAttribute('published', 'disabled', 'true');
            $form->setFieldAttribute('published', 'required', 'false');
            $form->setFieldAttribute('published', 'filter', 'unset');
        }

        // Disables language field selection
        $form->setFieldAttribute('language', 'readonly', 'true');

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return mixed  The data for the form.
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState(
            'com_guidedtours.edit.step.data',
            []
        );

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Method to get a single record.
     *
     * @param   integer  $pk  The id of the primary key.
     *
     * @return  CMSObject|boolean  Object on success, false on failure.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getItem($pk = null)
    {
        $lang = Factory::getLanguage();
        $lang->load('com_guidedtours.sys', JPATH_ADMINISTRATOR);

        if ($result = parent::getItem($pk)) {
            if (!empty($result->id)) {
                $result->title_translation       = Text::_($result->title);
                $result->description_translation = Text::_($result->description);
            } else {
                $app    = Factory::getApplication();
                $tourID = $app->getUserState('com_guidedtours.tour_id');

                /** @var \Joomla\Component\Guidedtours\Administrator\Model\TourModel $tourModel */
                $tourModel = $app->bootComponent('com_guidedtours')
                    ->getMVCFactory()->createModel('Tour', 'Administrator', ['ignore_request' => true]);

                $tour         = $tourModel->getItem($tourID);
                $tourLanguage = !empty($tour->language) ? $tour->language : '*';

                // Sets step language to parent tour language
                $result->language = $tourLanguage;
            }
        }

        return $result;
    }
}
