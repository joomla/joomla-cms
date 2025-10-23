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
use Joomla\CMS\Table\Table;
use Joomla\Component\Guidedtours\Administrator\Helper\GuidedtoursHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Item Model for a single tour.
 *
 * @since 4.3.0
 */

class StepModel extends AdminModel
{
    /**
     * The prefix to use with controller messages.
     *
     * @var   string
     * @since 4.3.0
     */
    protected $text_prefix = 'COM_GUIDEDTOURS';

    /**
     * Type alias for content type
     *
     * @var   string
     * @since 4.3.0
     */
    public $typeAlias = 'com_guidedtours.step';

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
     *
     * @since   4.3.0
     */
    protected function canDelete($record)
    {
        if (empty($record->id) || $record->published != -2) {
            return false;
        }

        return parent::canDelete($record);
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success.
     *
     * @since   4.3.0
     */
    public function save($data)
    {
        $table = $this->getTable();
        $input = Factory::getApplication()->getInput();

        $tour = $this->getTable('Tour');

        $tour->load($data['tour_id']);

        // Language keys must include GUIDEDTOUR to prevent save issues
        if (str_contains($data['description'], 'GUIDEDTOUR')) {
            $data['description'] = strip_tags($data['description']);
        }

        // Make sure we use the correct extension when editing an existing tour
        $key = $table->getKeyName();
        $pk  = $data[$key] ?? (int) $this->getState($this->getName() . '.id');

        if ($pk > 0) {
            $table->load($pk);

            if ((int) $table->tour_id) {
                $data['tour_id'] = (int) $table->tour_id;
            }
        }

        if ($input->get('task') == 'save2copy') {
            $origTable = $this->getTable();
            $origTable->load($input->getInt('id'));

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
     * @since   4.3.0
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
     * Method to get a table object, load it if necessary.
     *
     * @param   string $type    The table name. Optional.
     * @param   string $prefix  The class prefix. Optional.
     * @param   array  $config  Configuration array for model. Optional.
     *
     * @return  Table  A Table object
     *
     * @since   4.3.0
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
     * @return  \JForm|boolean  A Form object on success, false on failure
     *
     * @since   4.3.0
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

        // Modify the form based on access controls.
        if (!$this->canEditState((object) $item)) {
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
     * @since  4.3.0
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
     * @return  \stdClass|boolean  Object on success, false on failure.
     *
     * @since   4.3.0
     */
    public function getItem($pk = null)
    {
        if ($result = parent::getItem($pk)) {
            $app = Factory::getApplication();

            /** @var \Joomla\Component\Guidedtours\Administrator\Model\TourModel $tourModel */
            $tourModel = $app->bootComponent('com_guidedtours')
            ->getMVCFactory()->createModel('Tour', 'Administrator', ['ignore_request' => true]);

            if (!empty($result->id)) {
                // Editing an existing step
                $tour = $tourModel->getItem($result->tour_id);

                GuidedtoursHelper::loadTranslationFiles($tour->uid, true);

                $result->title_translation       = Text::_($result->title);
                $result->description_translation = Text::_($result->description);
            } else {
                // Creating a new step so we get the tour id from the session data
                $tourId = $app->getUserState('com_guidedtours.tour_id');
                $tour   = $tourModel->getItem($tourId);

                // Sets step language to parent tour language
                $result->language = !empty($tour->language) ? $tour->language : '*';

                // Set the step's tour id
                $result->tour_id = $tourId;
            }
        }

        return $result;
    }
}
