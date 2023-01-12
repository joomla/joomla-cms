<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Languages\Administrator\Model;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Languages Component Language Model
 *
 * @since  1.5
 */
class LanguageModel extends AdminModel
{
    /**
     * Constructor.
     *
     * @param   array                $config   An optional associative array of configuration settings.
     * @param   MVCFactoryInterface  $factory  The factory.
     *
     * @see     \Joomla\CMS\MVC\Model\BaseDatabaseModel
     * @since   3.2
     */
    public function __construct($config = [], MVCFactoryInterface $factory = null)
    {
        $config = array_merge(
            [
                'event_after_save'  => 'onExtensionAfterSave',
                'event_before_save' => 'onExtensionBeforeSave',
                'events_map'        => [
                    'save' => 'extension'
                ]
            ],
            $config
        );

        parent::__construct($config, $factory);
    }

    /**
     * Override to get the table.
     *
     * @param   string  $name     Name of the table.
     * @param   string  $prefix   Table name prefix.
     * @param   array   $options  Array of options.
     *
     * @return  Table
     *
     * @since   1.6
     */
    public function getTable($name = '', $prefix = '', $options = [])
    {
        return Table::getInstance('Language', 'Joomla\\CMS\\Table\\');
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState()
    {
        $app    = Factory::getApplication();
        $params = ComponentHelper::getParams('com_languages');

        // Load the User state.
        $langId = $app->input->getInt('lang_id');
        $this->setState('language.id', $langId);

        // Load the parameters.
        $this->setState('params', $params);
    }

    /**
     * Method to get a member item.
     *
     * @param   integer  $langId  The id of the member to get.
     *
     * @return  mixed  User data object on success, false on failure.
     *
     * @since   1.0
     */
    public function getItem($langId = null)
    {
        $langId = (!empty($langId)) ? $langId : (int) $this->getState('language.id');

        // Get a member row instance.
        $table = $this->getTable();

        // Attempt to load the row.
        $return = $table->load($langId);

        // Check for a table object error.
        if ($return === false && $table->getError()) {
            $this->setError($table->getError());

            return false;
        }

        // Set a valid accesslevel in case '0' is stored due to a bug in the installation SQL (was fixed with PR 2714).
        if ($table->access == '0') {
            $table->access = (int) Factory::getApplication()->get('access');
        }

        $properties = $table->getProperties(1);
        $value      = ArrayHelper::toObject($properties, CMSObject::class);

        return $value;
    }

    /**
     * Method to get the group form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  \Joomla\CMS\Form\Form|bool  A Form object on success, false on failure.
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_languages.language', 'language', ['control' => 'jform', 'load_data' => $loadData]);

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
        $data = Factory::getApplication()->getUserState('com_languages.edit.language.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_languages.language', $data);

        return $data;
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function save($data)
    {
        $langId = (!empty($data['lang_id'])) ? $data['lang_id'] : (int) $this->getState('language.id');
        $isNew  = true;

        PluginHelper::importPlugin($this->events_map['save']);

        $table   = $this->getTable();
        $context = $this->option . '.' . $this->name;

        // Load the row if saving an existing item.
        if ($langId > 0) {
            $table->load($langId);
            $isNew = false;
        }

        // Prevent white spaces, including East Asian double bytes.
        $spaces = ['/\xE3\x80\x80/', ' '];

        $data['lang_code'] = str_replace($spaces, '', $data['lang_code']);

        // Prevent saving an incorrect language tag
        if (!preg_match('#\b([a-z]{2,3})[-]([A-Z]{2})\b#', $data['lang_code'])) {
            $this->setError(Text::_('COM_LANGUAGES_ERROR_LANG_TAG'));

            return false;
        }

        $data['sef'] = str_replace($spaces, '', $data['sef']);
        $data['sef'] = ApplicationHelper::stringURLSafe($data['sef']);

        // Prevent saving an empty url language code
        if ($data['sef'] === '') {
            $this->setError(Text::_('COM_LANGUAGES_ERROR_SEF'));

            return false;
        }

        // Bind the data.
        if (!$table->bind($data)) {
            $this->setError($table->getError());

            return false;
        }

        // Check the data.
        if (!$table->check()) {
            $this->setError($table->getError());

            return false;
        }

        // Trigger the before save event.
        $result = Factory::getApplication()->triggerEvent($this->event_before_save, [$context, &$table, $isNew]);

        // Check the event responses.
        if (in_array(false, $result, true)) {
            $this->setError($table->getError());

            return false;
        }

        // Store the data.
        if (!$table->store()) {
            $this->setError($table->getError());

            return false;
        }

        // Trigger the after save event.
        Factory::getApplication()->triggerEvent($this->event_after_save, [$context, &$table, $isNew]);

        $this->setState('language.id', $table->lang_id);

        // Clean the cache.
        $this->cleanCache();

        return true;
    }

    /**
     * Custom clean cache method.
     *
     * @param   string   $group     Optional cache group name.
     * @param   integer  $clientId  @deprecated   5.0   No longer used.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function cleanCache($group = null, $clientId = 0)
    {
        parent::cleanCache('_system');
        parent::cleanCache('com_languages');
    }
}
