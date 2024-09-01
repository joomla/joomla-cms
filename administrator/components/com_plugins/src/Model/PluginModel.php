<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Plugins\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\Filesystem\Path;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Plugin model.
 *
 * @since  1.6
 */
class PluginModel extends AdminModel
{
    /**
     * @var     string  The help screen key for the module.
     * @since   1.6
     */
    protected $helpKey = 'Plugins:_Name_of_Plugin';

    /**
     * @var     string  The help screen base URL for the module.
     * @since   1.6
     */
    protected $helpURL;

    /**
     * @var     array  An array of cached plugin items.
     * @since   1.6
     */
    protected $_cache;

    /**
     * Constructor.
     *
     * @param   array                 $config   An optional associative array of configuration settings.
     * @param   ?MVCFactoryInterface  $factory  The factory.
     *
     * @see     \Joomla\CMS\MVC\Model\BaseDatabaseModel
     * @since   3.2
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config = array_merge(
            [
                'event_after_save'  => 'onExtensionAfterSave',
                'event_before_save' => 'onExtensionBeforeSave',
                'events_map'        => [
                    'save' => 'extension',
                ],
            ],
            $config
        );

        parent::__construct($config, $factory);
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form|bool  A Form object on success, false on failure.
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        // The folder and element vars are passed when saving the form.
        if (empty($data)) {
            $item    = $this->getItem();
            $folder  = $item->folder;
            $element = $item->element;
        } else {
            $folder  = ArrayHelper::getValue($data, 'folder', '', 'cmd');
            $element = ArrayHelper::getValue($data, 'element', '', 'cmd');
        }

        // Add the default fields directory
        Form::addFieldPath(JPATH_PLUGINS . '/' . $folder . '/' . $element . '/field');

        // These variables are used to add data from the plugin XML files.
        $this->setState('item.folder', $folder);
        $this->setState('item.element', $element);

        // Get the form.
        $form = $this->loadForm('com_plugins.plugin', 'plugin', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        // Modify the form based on access controls.
        if (!$this->canEditState((object) $data)) {
            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('enabled', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is a record you can edit.
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('enabled', 'filter', 'unset');
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
        $data = Factory::getApplication()->getUserState('com_plugins.edit.plugin.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_plugins.plugin', $data);

        return $data;
    }

    /**
     * Method to get a single record.
     *
     * @param   integer  $pk  The id of the primary key.
     *
     * @return  mixed  Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        $pk = (!empty($pk)) ? $pk : (int) $this->getState('plugin.id');

        $cacheId = $pk;

        if (\is_array($cacheId)) {
            $cacheId = serialize($cacheId);
        }

        if (!isset($this->_cache[$cacheId])) {
            // Get a row instance.
            $table = $this->getTable();

            // Attempt to load the row.
            $return = $table->load(\is_array($pk) ? $pk : ['extension_id' => $pk, 'type' => 'plugin']);

            // Check for a table object error.
            if ($return === false) {
                return false;
            }

            // Convert to the \Joomla\CMS\Object\CMSObject before adding other data.
            $properties             = $table->getProperties(1);
            $this->_cache[$cacheId] = ArrayHelper::toObject($properties, CMSObject::class);

            // Convert the params field to an array.
            $registry                       = new Registry($table->params);
            $this->_cache[$cacheId]->params = $registry->toArray();

            // Get the plugin XML.
            $path = Path::clean(JPATH_PLUGINS . '/' . $table->folder . '/' . $table->element . '/' . $table->element . '.xml');

            if (file_exists($path)) {
                $this->_cache[$cacheId]->xml = simplexml_load_file($path);
            } else {
                $this->_cache[$cacheId]->xml = null;
            }
        }

        return $this->_cache[$cacheId];
    }

    /**
     * Returns a reference to the Table object, always creating it.
     *
     * @param   string  $type    The table type to instantiate.
     * @param   string  $prefix  A prefix for the table class name. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  Table   A database object
     */
    public function getTable($type = 'Extension', $prefix = '\\Joomla\\CMS\\Table\\', $config = [])
    {
        return Table::getInstance($type, $prefix, $config);
    }

    /**
     * Auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState()
    {
        // Execute the parent method.
        parent::populateState();

        $app = Factory::getApplication();

        // Load the User state.
        $pk = $app->getInput()->getInt('extension_id');
        $this->setState('plugin.id', $pk);
    }

    /**
     * Preprocess the form.
     *
     * @param   Form    $form   A form object.
     * @param   mixed   $data   The data expected for the form.
     * @param   string  $group  Cache group name.
     *
     * @return  void
     *
     * @since   1.6
     *
     * @throws  \Exception if there is an error in the form event.
     */
    protected function preprocessForm(Form $form, $data, $group = 'content')
    {
        $folder  = $this->getState('item.folder');
        $element = $this->getState('item.element');
        $lang    = Factory::getLanguage();

        // Load the core and/or local language sys file(s) for the ordering field.
        $db    = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('element'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = :folder')
            ->bind(':folder', $folder);
        $db->setQuery($query);
        $elements = $db->loadColumn();

        foreach ($elements as $elementa) {
            $lang->load('plg_' . $folder . '_' . $elementa . '.sys', JPATH_ADMINISTRATOR)
                || $lang->load('plg_' . $folder . '_' . $elementa . '.sys', JPATH_PLUGINS . '/' . $folder . '/' . $elementa);
        }

        if (empty($folder) || empty($element)) {
            $app = Factory::getApplication();
            $app->redirect(Route::_('index.php?option=com_plugins&view=plugins', false));
        }

        $formFile = Path::clean(JPATH_PLUGINS . '/' . $folder . '/' . $element . '/' . $element . '.xml');

        if (!file_exists($formFile)) {
            throw new \Exception(Text::sprintf('COM_PLUGINS_ERROR_FILE_NOT_FOUND', $element . '.xml'));
        }

        // Load the core and/or local language file(s).
        $lang->load('plg_' . $folder . '_' . $element, JPATH_ADMINISTRATOR)
            || $lang->load('plg_' . $folder . '_' . $element, JPATH_PLUGINS . '/' . $folder . '/' . $element);

        if (file_exists($formFile)) {
            // Get the plugin form.
            if (!$form->loadFile($formFile, false, '//config')) {
                throw new \Exception(Text::_('JERROR_LOADFILE_FAILED'));
            }
        }

        // Attempt to load the xml file.
        if (!$xml = simplexml_load_file($formFile)) {
            throw new \Exception(Text::_('JERROR_LOADFILE_FAILED'));
        }

        // Get the help data from the XML file if present.
        $help = $xml->xpath('/extension/help');

        if (!empty($help)) {
            $helpKey = trim((string) $help[0]['key']);
            $helpURL = trim((string) $help[0]['url']);

            $this->helpKey = $helpKey ?: $this->helpKey;
            $this->helpURL = $helpURL ?: $this->helpURL;
        }

        // Trigger the default form events.
        parent::preprocessForm($form, $data, $group);
    }

    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param   object  $table  A record object.
     *
     * @return  array  An array of conditions to add to ordering queries.
     *
     * @since   1.6
     */
    protected function getReorderConditions($table)
    {
        $db = $this->getDatabase();

        return [
            $db->quoteName('type') . ' = ' . $db->quote($table->type),
            $db->quoteName('folder') . ' = ' . $db->quote($table->folder),
        ];
    }

    /**
     * Override method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function save($data)
    {
        // Setup type.
        $data['type'] = 'plugin';

        return parent::save($data);
    }

    /**
     * Get the necessary data to load an item help screen.
     *
     * @return  object  An object with key, url, and local properties for loading the item help screen.
     *
     * @since   1.6
     */
    public function getHelp()
    {
        return (object) ['key' => $this->helpKey, 'url' => $this->helpURL];
    }

    /**
     * Custom clean cache method, plugins are cached in 2 places for different clients.
     *
     * @param   string   $group     Cache group name.
     * @param   integer  $clientId  No longer used, will be removed without replacement
     *                              @deprecated   4.3 will be removed in 6.0
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function cleanCache($group = null, $clientId = 0)
    {
        parent::cleanCache('com_plugins');
    }
}
