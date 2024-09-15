<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Model;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Guidedtours\Administrator\Helper\GuidedtoursHelper;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Model class for tour
 *
 * @since  4.3.0
 */
class TourModel extends AdminModel
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
     * @var string
     * @since 4.3.0
     */
    public $typeAlias = 'com_guidedtours.tour';

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean True on success.
     *
     * @since  4.3.0
     */
    public function save($data)
    {
        $input = Factory::getApplication()->getInput();

        // Language keys must include GUIDEDTOUR to prevent save issues
        if (strpos($data['description'], 'GUIDEDTOUR') !== false) {
            $data['description'] = strip_tags($data['description']);
        }

        if ($input->get('task') == 'save2copy') {
            $origTable = clone $this->getTable();
            $origTable->load($input->getInt('id'));

            $data['published'] = 0;
        }

        // Set step language to parent tour language on save.
        $id   = $data['id'];
        $lang = $data['language'];

        $this->setStepsLanguage($id, $lang);

        return parent::save($data);
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param   \Joomla\CMS\Table\Table  $table  The Table object
     *
     * @return  void
     *
     * @since  4.3.0
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
                    ->from($db->quoteName('#__guidedtours'));
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
     * Abstract method for getting the form from the model.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return \JForm|boolean  A Form object on success, false on failure
     *
     * @since  4.3.0
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            'com_guidedtours.tour',
            'tour',
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

        $currentDate = Factory::getDate()->toSql();

        $form->setFieldAttribute('created', 'default', $currentDate);
        $form->setFieldAttribute('modified', 'default', $currentDate);

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
            'com_guidedtours.edit.tour.data',
            []
        );

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Method to get a single record by id or uid
     *
     * @param   integer|string  $pk  The id or uid of the tour.
     *
     * @return  \stdClass|boolean  Object on success, false on failure.
     *
     * @since   4.3.0
     */
    public function getItem($pk = null)
    {
        $pk    = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');

        $table = $this->getTable();

        if (\is_int($pk)) {
            $result = $table->load((int) $pk);
        } else {
            // Attempt to load the row by uid.
            $result = $table->load([ 'uid' => $pk ]);
        }

        // Check for a table object error.
        if ($result === false) {
            // If there was no underlying error, then the false means there simply was not a row in the db for this $pk.
            if (!$table->getError()) {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_NOT_EXIST'));
            } else {
                $this->setError($table->getError());
            }

            return false;
        }

        // Convert to the CMSObject before adding other data.
        $properties = $table->getProperties(1);
        $item       = ArrayHelper::toObject($properties, CMSObject::class);

        if (property_exists($item, 'params')) {
            $registry     = new Registry($item->params);
            $item->params = $registry->toArray();
        }

        if (!empty($item->uid)) {
            GuidedtoursHelper::loadTranslationFiles($item->uid, true);
        }

        if (!empty($item->id)) {
            $item->title_translation       = Text::_($item->title);
            $item->description_translation = Text::_($item->description);
        }

        return $item;
    }

    /**
     * Delete all steps if a tour is deleted
     *
     * @param   object  $pks  The primary key related to the tours.
     *
     * @return  boolean
     *
     * @since   4.3.0
     */
    public function delete(&$pks)
    {
        $pks   = ArrayHelper::toInteger((array) $pks);
        $table = $this->getTable();

        // Include the plugins for the delete events.
        PluginHelper::importPlugin($this->events_map['delete']);

        // Iterate the items to delete each one.
        foreach ($pks as $i => $pk) {
            if ($table->load($pk)) {
                if ($this->canDelete($table)) {
                    $context = $this->option . '.' . $this->name;

                    // Trigger the before delete event.
                    $result = Factory::getApplication()->triggerEvent($this->event_before_delete, [$context, $table]);

                    if (\in_array(false, $result, true)) {
                        $this->setError($table->getError());

                        return false;
                    }

                    $tourId = $table->id;

                    if (!$table->delete($pk)) {
                        $this->setError($table->getError());

                        return false;
                    }

                    // Delete of the tour has been successful, now delete the steps
                    $db    = $this->getDatabase();
                    $query = $db->getQuery(true)
                        ->delete($db->quoteName('#__guidedtour_steps'))
                        ->where($db->quoteName('tour_id') . '=' . $tourId);
                    $db->setQuery($query);
                    $db->execute();

                    // Trigger the after event.
                    Factory::getApplication()->triggerEvent($this->event_after_delete, [$context, $table]);
                } else {
                    // Prune items that you can't change.
                    unset($pks[$i]);
                    $error = $this->getError();

                    if ($error) {
                        Log::add($error, Log::WARNING, 'jerror');

                        return false;
                    }

                    Log::add(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), Log::WARNING, 'jerror');

                    return false;
                }
            } else {
                $this->setError($table->getError());

                return false;
            }
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }

    /**
     * Duplicate all steps if a tour is duplicated
     *
     * @param   object  $pks  The primary key related to the tours.
     *
     * @return  boolean
     *
     * @since   4.3.0
     */
    public function duplicate(&$pks)
    {
        $user = $this->getCurrentUser();
        $db   = $this->getDatabase();

        // Access checks.
        if (!$user->authorise('core.create', 'com_guidedtours')) {
            throw new \Exception(Text::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
        }

        $table = $this->getTable();

        $date = Factory::getDate()->toSql();

        foreach ($pks as $pk) {
            if ($table->load($pk, true)) {
                // Reset the id to create a new record.
                $table->id = 0;

                $table->published = 0;

                if (!$table->check() || !$table->store()) {
                    throw new \Exception($table->getError());
                }

                $pk = (int) $pk;

                $query = $db->getQuery(true)
                    ->select(
                        $db->quoteName(
                            [
                                'title',
                                'description',
                                'ordering',
                                'position',
                                'target',
                                'type',
                                'interactive_type',
                                'url',
                                'created',
                                'modified',
                                'checked_out_time',
                                'checked_out',
                                'language',
                                'params',
                                'note',
                            ]
                        )
                    )
                    ->from($db->quoteName('#__guidedtour_steps'))
                    ->where($db->quoteName('tour_id') . ' = :id')
                    ->bind(':id', $pk, ParameterType::INTEGER);

                $db->setQuery($query);
                $rows = $db->loadObjectList();

                if ($rows) {
                    $query = $db->getQuery(true)
                        ->insert($db->quoteName('#__guidedtour_steps'))
                        ->columns(
                            [
                                $db->quoteName('tour_id'),
                                $db->quoteName('title'),
                                $db->quoteName('description'),
                                $db->quoteName('ordering'),
                                $db->quoteName('position'),
                                $db->quoteName('target'),
                                $db->quoteName('type'),
                                $db->quoteName('interactive_type'),
                                $db->quoteName('url'),
                                $db->quoteName('created'),
                                $db->quoteName('created_by'),
                                $db->quoteName('modified'),
                                $db->quoteName('modified_by'),
                                $db->quoteName('language'),
                                $db->quoteName('params'),
                                $db->quoteName('note'),
                            ]
                        );

                    foreach ($rows as $step) {
                        $dataTypes = [
                            ParameterType::INTEGER,
                            ParameterType::STRING,
                            ParameterType::STRING,
                            ParameterType::INTEGER,
                            ParameterType::STRING,
                            ParameterType::STRING,
                            ParameterType::INTEGER,
                            ParameterType::INTEGER,
                            ParameterType::STRING,
                            ParameterType::STRING,
                            ParameterType::INTEGER,
                            ParameterType::STRING,
                            ParameterType::INTEGER,
                            ParameterType::STRING,
                            ParameterType::STRING,
                            ParameterType::STRING,
                        ];

                        $query->values(
                            implode(
                                ',',
                                $query->bindArray(
                                    [
                                        $table->id,
                                        $step->title,
                                        $step->description,
                                        $step->ordering,
                                        $step->position,
                                        $step->target,
                                        $step->type,
                                        $step->interactive_type,
                                        $step->url,
                                        $date,
                                        $user->id,
                                        $date,
                                        $user->id,
                                        $step->language,
                                        $step->params,
                                        $step->note,
                                    ],
                                    $dataTypes
                                )
                            )
                        );
                    }

                    $db->setQuery($query);

                    try {
                        $db->execute();
                    } catch (\RuntimeException $e) {
                        Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

                        return false;
                    }
                }
            } else {
                throw new \Exception($table->getError());
            }
        }

        // Clear tours cache
        $this->cleanCache();

        return true;
    }

    /**
     * Sets a tour's steps language
     *
     * @param   int     $id        Id of a tour
     * @param   string  $language  The language to apply to the steps belong the tour
     *
     * @return  boolean
     *
     * @since  4.3.0
     */
    protected function setStepsLanguage(int $id, string $language = '*'): bool
    {
        if ($id <= 0) {
            return false;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__guidedtour_steps'))
            ->set($db->quoteName('language') . ' = :language')
            ->where($db->quoteName('tour_id') . ' = :tourId')
            ->bind(':language', $language)
            ->bind(':tourId', $id, ParameterType::INTEGER);

        return $db->setQuery($query)
            ->execute();
    }

    /**
     * Sets a tour's autostart value
     *
     * @param   int  $id         Id of a tour
     * @param   int  $autostart  The autostart value of a tour
     *
     * @since  5.1.0
     */
    public function setAutostart($id, $autostart)
    {
        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->update($db->quoteName('#__guidedtours'))
            ->set($db->quoteName('autostart') . ' = :autostart')
            ->where($db->quoteName('id') . ' = :tourId')
            ->bind(':autostart', $autostart, ParameterType::INTEGER)
            ->bind(':tourId', $id, ParameterType::INTEGER);

        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Retrieve a tour's autostart value
     *
     * @param   string  $pk  the id or uid of a tour
     *
     * @return  boolean
     *
     * @since  5.1.0
     */
    public function isAutostart($pk): bool
    {
        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select($db->quoteName('autostart'))
            ->from($db->quoteName('#__guidedtours'))
            ->where($db->quoteName('published') . ' = 1');

        if (\is_integer($pk)) {
            $query->where($db->quoteName('id') . ' = :id')
                ->bind(':id', $pk, ParameterType::INTEGER);
        } else {
            $query->where($db->quoteName('uid') . ' = :uid')
                ->bind(':uid', $pk, ParameterType::STRING);
        }

        $db->setQuery($query);

        try {
            $result = $db->loadResult();
            if ($result === null) {
                return false;
            }
        } catch (\RuntimeException $e) {
            return false;
        }

        return $result;
    }

    /**
     * Save a tour state for a specific user.
     *
     * @param   int      $id       The id of the tour
     * @param   string   $state    The label of the state to be saved (completed, delayed or skipped)
     *
     * @return  boolean
     *
     * @since  5.2.0
     */
    public function saveTourUserState($id, $state = ''): bool
    {
        $user = $this->getCurrentUser();
        $db   = $this->getDatabase();

        $profileKey = 'guidedtour.id.' . $id;

        // Check if the profile key already exists.
        $query = $db->getQuery(true)
            ->select($db->quoteName('profile_value'))
            ->from($db->quoteName('#__user_profiles'))
            ->where($db->quoteName('user_id') . ' = :user_id')
            ->where($db->quoteName('profile_key') . ' = :profileKey')
            ->bind(':user_id', $user->id, ParameterType::INTEGER)
            ->bind(':profileKey', $profileKey, ParameterType::STRING);

        try {
            $result = $db->setQuery($query)->loadResult();
        } catch (\Exception $e) {
            return false;
        }

        $tourState = [];

        $tourState['state'] = $state;
        if ($state === 'delayed') {
            $tourState['time'] = Date::getInstance();
        }

        $profileObject = (object)[
            'user_id'       => $user->id,
            'profile_key'   => $profileKey,
            'profile_value' => json_encode($tourState),
            'ordering'      => 0,
        ];

        if (!\is_null($result)) {
            $values = json_decode($result, true);

            // The profile is updated only when delayed. 'Completed' and 'Skipped' are final
            if (!empty($values) && $values['state'] === 'delayed') {
                $db->updateObject('#__user_profiles', $profileObject, ['user_id', 'profile_key']);
            }
        } else {
            $db->insertObject('#__user_profiles', $profileObject);
        }

        return true;
    }
}
