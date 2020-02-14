<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */

defined('JPATH_PLATFORM') or die;

require JPATH_SITE . '/components/com_jce/editor/libraries/classes/editor.php';

require JPATH_ADMINISTRATOR . '/components/com_jce/helpers/plugins.php';
require JPATH_ADMINISTRATOR . '/components/com_jce/helpers/profiles.php';

/**
 * Item Model for a Profile.
 *
 * @since       1.6
 */
class JceModelProfile extends JModelAdmin
{
    /**
     * The type alias for this content type.
     *
     * @var string
     *
     * @since  3.2
     */
    public $typeAlias = 'com_jce.profile';

    /**
     * The prefix to use with controller messages.
     *
     * @var string
     *
     * @since  1.6
     */
    protected $text_prefix = 'COM_JCE';

    /**
     * Returns a Table object, always creating it.
     *
     * @param type   $type   The table type to instantiate
     * @param string $prefix A prefix for the table class name. Optional
     * @param array  $config Configuration array for model. Optional
     *
     * @return JTable A database object
     *
     * @since   1.6
     */
    public function getTable($type = 'Profiles', $prefix = 'JceTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to allow derived classes to preprocess the form.
     *
     * @param JForm  $form  A JForm object
     * @param mixed  $data  The data expected for the form
     * @param string $group The name of the plugin group to import (defaults to "content")
     *
     * @see     JFormField
     * @since   1.6
     *
     * @throws Exception if there is an error in the form event
     */
    protected function preprocessForm(JForm $form, $data, $group = 'content')
    {
        if (!empty($data)) {
            $registry = new JRegistry($data->config);

            // process individual fields to remove default value if required
            $fields = $form->getFieldset();

            foreach ($fields as $field) {
                $name = $field->getAttribute('name');

                // get the field group and add the field name
                $group = (string) $field->group;

                // must be a grouped parameter, eg: editor, imgmanager etc.
                if (!$group) {
                    continue;
                }

                // create key from group and name
                $group = $group . '.' . $name;

                // explode group to array
                $parts = explode('.', $group);

                // remove "config" from group name so it matches params data object
                if ($parts[0] === "config") {
                    array_shift($parts);
                    $group = implode('.', $parts);
                }

                // reset the "default" attribute value if a value is set
                if ($registry->exists($group)) {                    
                    $form->setFieldAttribute($name, 'default', '', (string) $field->group);
                }
            }
        }

        parent::preprocessForm($form, $data);
    }

    public function getForm($data = array(), $loadData = true)
    {
        JFormHelper::addFieldPath('JPATH_ADMINISTRATOR/components/com_jce/models/fields');

        // Get the setup form.
        $form = $this->loadForm('com_jce.profile', 'profile', array('control' => 'jform', 'load_data' => false));

        if (!$form) {
            return false;
        }

        JFactory::getLanguage()->load('com_jce_pro', JPATH_SITE);

        // editor manifest
        $manifest = __DIR__ . '/forms/editor.xml';

        // load editor manifest
        if (is_file($manifest)) {
            if ($editor_xml = simplexml_load_file($manifest)) {
                $form->setField($editor_xml, 'config');
            }
        }

        // pro manifest
        $manifest = WF_EDITOR_LIBRARIES . '/pro/xml/image.xml';

        // load pro manifest
        if (is_file($manifest)) {
            if ($pro_xml = simplexml_load_file($manifest)) {
                $form->setField($pro_xml, 'config');
            }
        }

        $data = $this->loadFormData();

        // Allow for additional modification of the form, and events to be triggered.
        // We pass the data because plugins may require it.
        $this->preprocessForm($form, $data);

        // Load the data into the form after the plugins have operated.
        $form->bind($data);

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return mixed The data for the form
     *
     * @since   1.6
     */
    protected function loadFormData()
    {
        $data = $this->getItem();

        // convert 0 value to string containing both options
        if (empty($data->area)) {
            $data->area = '1,2';
        }

        $data->device = explode(',', $data->device);

        if (!empty($data->components)) {
            $data->components = explode(',', $data->components);
            $data->components_select = 1;
        }

        $data->types = explode(',', $data->types);

        $table = JTable::getInstance('user');
        $users = array();

        foreach (explode(',', $data->users) as $id) {
            if ($table->load((int) $id)) {
                $user = new StdClass();
                $user->value = $id;
                $user->text = htmlspecialchars($table->name, ENT_COMPAT, 'UTF-8');

                $users[] = $user;
            }
        }

        $data->users    = $users;
        $data->config   = $data->params; 

        return $data;
    }

    public function getRows()
    {
        $data = $this->getItem();

        $array = array();
        $rows = explode(';', $data->rows);

        $plugins = $this->getButtons();

        $i = 1;

        foreach ($rows as $row) {
            $groups = array();
            // remove spacers
            $row = str_replace(array('|', 'spacer'), '', $row);

            foreach (explode('spacer', $row) as $group) {
                // get items in group
                $items = explode(',', $group);
                $buttons = array();

                for ($x = 0; $x < count($items); ++$x) {
                    $name = $items[$x];

                    if ($name === 'spacer') {
                        unset($items[$x]);
                        continue;
                    }

                    // not in the list...
                    if (empty($name) || array_key_exists($name, $plugins) === false) {
                        continue;
                    }

                    // must be assigned...
                    if (!$plugins[$name]->active) {
                        continue;
                    }

                    // assign icon
                    $buttons[] = $plugins[$name];
                }

                $groups[] = $buttons;
            }

            $array[$i] = $groups;

            ++$i;
        }

        return $array;
    }

    /**
     * An array of buttons not in the current editor layout.
     *
     * @return array
     */
    public function getAvailableButtons()
    {
        $plugins = $this->getButtons();

        $available = array_filter($plugins, function ($plugin) {
            return !$plugin->active;
        });

        return $available;
    }

    public function getAdditionalPlugins()
    {
        $plugins = $this->getButtons();

        $additional = array_filter($plugins, function ($plugin) {
            return $plugin->editable && !$plugin->row;
        });

        return $additional;
    }

    public function getButtons()
    {
        $commands = $this->getCommands();
        $plugins = $this->getPlugins();

        return array_merge($commands, $plugins);
    }

    public function getCommands()
    {
        static $commands;

        if (empty($commands)) {
            $data = $this->getItem();
            $rows = preg_split('#[;,]#', $data->rows);

            $commands = array();

            foreach (JcePluginsHelper::getCommands() as $name => $command) {
                // set as active
                $command->active = in_array($name, $rows);
                $command->icon = explode(',', $command->icon);

                // set default empty value
                $command->image = '';

                // ui class, default is blank
                if (empty($command->class)) {
                    $command->class = '';
                }

                // cast row to integer
                $command->row = (int) $command->row;

                // cast editable to integer
                $command->editable = (int) $command->editable;

                // translate title
                $command->title = JText::_($command->title);

                // translate description
                $command->description = JText::_($command->description);

                $command->name = $name;

                $commands[$name] = $command;
            }
        }

        // merge plugins and commands
        return $commands;
    }

    public function getPlugins()
    {
        static $plugins;

        if (empty($plugins)) {
            $plugins = array();

            $data = $this->loadFormData();

            // array or profile plugin items
            $rows = explode(',', $data->plugins);

            // only need plugins with xml files
            foreach (JcePluginsHelper::getPlugins() as $name => $plugin) {
                $plugin->icon = empty($plugin->icon) ? array() : explode(',', $plugin->icon);

                // set as active if it is in the profile
                $plugin->active = in_array($plugin->name, $rows);

                // ui class, default is blank
                if (empty($plugin->class)) {
                    $plugin->class = '';
                }

                $plugin->class = preg_replace_callback('#\b([a-z0-9]+)-([a-z0-9]+)\b#', function ($matches) {
                    return 'mce' . ucfirst($matches[1]) . ucfirst($matches[2]);
                }, $plugin->class);

                // translate title
                $plugin->title = JText::_($plugin->title);

                // translate description
                $plugin->description = JText::_($plugin->description);

                // cast row to integer
                $plugin->row = (int) $plugin->row;

                // cast editable to integer
                $plugin->editable = (int) $plugin->editable;

                // plugin extensions
                $plugin->extensions = array();

                if (is_file($plugin->manifest)) {
                    $plugin->form = $this->loadForm('com_jce.profile.' . $plugin->name, $plugin->manifest, array('control' => 'jform[config]', 'load_data' => true), true, '//extension');

                    $fieldsets = $plugin->form->getFieldsets();

                    // no parameter fields
                    if (empty($fieldsets)) {
                        $plugin->form = false;
                        $plugins[$name] = $plugin;
                        continue;
                    }

                    // bind data to the form
                    $plugin->form->bind($data->params);

                    $extensions = JcePluginsHelper::getExtensions();

                    foreach ($extensions as $type => $items) {

                        $item = new StdClass;
                        $item->name = '';
                        $item->title = '';
                        $item->manifest = WF_EDITOR_LIBRARIES . '/xml/config/' . $type . '.xml';
                        $item->context = '';

                        array_unshift($items, $item);

                        foreach ($items as $p) {
                            // check for plugin fieldset using xpath, as fieldset can be empty
                            $fieldset = $plugin->form->getXml()->xpath('(//fieldset[@name="plugin.' . $type . '"])');

                            // not supported, move along...
                            if (empty($fieldset)) {
                                continue;
                            }

                            $context = (string) $fieldset[0]->attributes()->context;

                            // check for a context, eg: images, web, video
                            if ($context && !in_array($p->context, explode(',', $context))) {
                                continue;
                            }

                            if (is_file($p->manifest)) {
                                $path = array($plugin->name, $type, $p->name);

                                // create new extension object
                                $extension = new StdClass;

                                // set extension name as the plugin name
                                $extension->name = $p->name;

                                // set extension title
                                $extension->title = $p->title;

                                // load form
                                $extension->form = $this->loadForm('com_jce.profile.' . implode('.', $path), $p->manifest, array('control' => 'jform[config][' . $plugin->name . '][' . $type . ']', 'load_data' => true), true, '//extension');

                                // get fieldsets if any
                                $fieldsets = $extension->form->getFieldsets();

                                foreach ($fieldsets as $fieldset) {
                                    // load form
                                    $plugin->extensions[$type][$p->name] = $extension;

                                    if (!isset($data->params[$plugin->name])) {
                                        continue;
                                    }

                                    if (!isset($data->params[$plugin->name][$type])) {
                                        continue;
                                    }

                                    // bind data to the form
                                    $extension->form->bind($data->params[$plugin->name][$type]);
                                }
                            }
                        }
                    }
                }

                // add to array
                $plugins[$name] = $plugin;
            }
        }

        return $plugins;
    }

    /**
     * Prepare and sanitise the table data prior to saving.
     *
     * @param JTable $table A reference to a JTable object
     *
     * @since   1.6
     */
    protected function prepareTable($table)
    {
        $date = JFactory::getDate();
        $user = JFactory::getUser();

        foreach ($table->getProperties() as $key => $value) {
            switch ($key) {
                case 'name':
                case 'description':
                    $value = filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
                    break;
                case 'device':
                    $value = implode(',', filter_var_array($value, FILTER_SANITIZE_STRING));
                    break;
                case 'area':
                    if (is_array($value)) {
                        if (count($value) === 2) {
                            $value = 0;
                        } else {
                            $value = $value[0];
                        }
                    }

                    $value = $value;

                    break;
                case 'components':

                    if (is_array($value)) {
                        $value = implode(',', filter_var_array($value, FILTER_SANITIZE_STRING));
                    }

                    break;
                case 'types':
                case 'users':

                    if (is_string($value)) {
                        $value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
                    }

                    if (is_array($value)) {
                        $value = implode(',', filter_var_array($value, FILTER_SANITIZE_NUMBER_INT));
                    }

                    break;
                case 'plugins':
                    $value = preg_replace('#[^\w,]+#', '', $value);
                    break;
                case 'rows':
                    $value = preg_replace('#[^\w,;]+#', '', $value);
                    break;
                case 'params':
                    break;
            }

            $table->$key = $value;
        }

        if (empty($table->id)) {
            // Set ordering to the last item if not set
            if (empty($table->ordering)) {
                $db = $this->getDbo();
                $query = $db->getQuery(true)
                    ->select('MAX(ordering)')
                    ->from($db->quoteName('#__wf_profiles'));

                $db->setQuery($query);
                $max = $db->loadResult();

                $table->ordering = $max + 1;
            }
        }
    }

    public function validate($form, $data, $group = null)
    {
        $filter = JFilterInput::getInstance();

        // get unfiltered config data
        $config = isset($data['config']) ? $data['config'] : array();

        // get layout rows and plugins data
        $rows = isset($data['rows']) ? $data['rows'] : '';
        $plugins = isset($data['plugins']) ? $data['plugins'] : '';

        // clean layout rows and plugins data
        $data['rows'] = $filter->clean($rows, 'STRING');
        $data['plugins'] = $filter->clean($plugins, 'STRING');

        // add back config data
        $data['params'] = $filter->clean($config, 'ARRAY');

        if (empty($data['components']) || empty($data['components_select'])) {
            $data['components'] = '';
        }

        if (empty($data['users'])) {
            $data['users'] = '';
        }

        if (empty($data['types'])) {
            $data['types'] = '';
        }

        return $data;
    }

    private static function cleanParamData($data)
    {
        // clean up link plugin parameters
        if (isset($data['link'])) {
            $params = $data['link'];

            if (isset($params['dir'])) {
                if (!empty($params['dir']) && empty($params['direction'])) {
                    $params['direction'] = $params['dir'];
                }
                unset($params['dir']);
            }

            $data['link'] = $params;
        }

        return $data;
    }

    private static function isJson($value)
    {
        json_decode($value);
        return json_last_error() == JSON_ERROR_NONE;
    }

    /**
     * Method to save the form data.
     *
     * @param   array  The form data
     *
     * @return bool True on success
     *
     * @since    2.7
     */
    public function save($data)
    {
        $app = JFactory::getApplication();

        // get profile table
        $table = $this->getTable();

        // Alter the title for save as copy
        if ($app->input->get('task') == 'save2copy') {

            // Alter the title
            $name = $data['name'];

            while ($table->load(array('name' => $name))) {
                if ($name == $table->name) {
                    $name = Joomla\String\StringHelper::increment($name);
                }
            }

            $data['name'] = $name;
            $data['published'] = 0;
        }

        $key = $table->getKeyName();
        $pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');

        if ($pk && $table->load($pk)) {

            if (empty($data['rows'])) {
                $data['rows'] = $table->rows;
            }

            if (empty($data['plugins'])) {
                $data['plugins'] = $table->plugins;
            }

            $json = array();
            $params = empty($table->params) ? '' : $table->params;

            // convert params to json data array
            $params = (array) json_decode($params, true);

            $plugins = isset($data['plugins']) ? $data['plugins'] : $table->plugins;

            // get plugins
            $items = explode(',', $plugins);
            // add "editor"
            $items[] = 'editor';

            // make sure we have a value
            if (empty($data['params'])) {
                $data['params'] = array();
            }

            $data['params'] = self::cleanParamData($data['params']);

            // data for editor and plugins
            foreach ($items as $item) {
                // add config data
                if (array_key_exists($item, $data['params'])) {
                    $value = $data['params'][$item];
                    $json[$item] = filter_var_array($value, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
                }
            }

            // combine and encode as json string
            $data['params'] = json_encode(WFUtility::array_merge_recursive_distinct($params, $json));
        }

        if (parent::save($data)) {
            return true;
        }

        return false;
    }

    public function copy($ids)
    {
        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $table = $this->getTable();

        foreach ($ids as $id) {
            if (!$table->load($id)) {
                $this->setError($table->getError());
            } else {
                $name = JText::sprintf('WF_PROFILES_COPY_OF', $table->name);
                $table->name = $name;
                $table->id = 0;
                $table->published = 0;
            }

            // Check the row.
            if (!$table->check()) {
                $this->setError($table->getError());

                return false;
            }

            // Store the row.
            if (!$table->store()) {
                $this->setError($table->getError());

                return false;
            }
        }

        return true;
    }

    public function export($ids)
    {
        $db = JFactory::getDBO();

        $buffer = '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';
        $buffer .= "\n" . '<export type="profiles">';
        $buffer .= "\n\t" . '<profiles>';

        $private = array('id', 'checked_out', 'checked_out_time');

        foreach ($ids as $id) {
            $table = $this->getTable();

            if (!$table->load($id)) {
                continue;
            }

            $buffer .= "\n\t\t";
            $buffer .= '<profile>';

            $fields = $table->getProperties();

            foreach ($fields as $key => $value) {
                // skip some stuff
                if (in_array($key, $private)) {
                    continue;
                }

                // set published to 0
                if ($key === "published") {
                    $value = 0;
                }

                if ($key == 'params') {
                    $buffer .= "\n\t\t\t" . '<' . $key . '>' . trim($value) . '</' . $key . '>';
                } else {
                    $buffer .= "\n\t\t\t" . '<' . $key . '>' . JceProfilesHelper::encodeData($value) . '</' . $key . '>';
                }
            }

            $buffer .= "\n\t\t</profile>";
        }

        $buffer .= "\n\t</profiles>";
        $buffer .= "\n</export>";

        // set_time_limit doesn't work in safe mode
        if (!ini_get('safe_mode')) {
            @set_time_limit(0);
        }

        $name = 'jce_editor_profile_' . date('Y_m_d') . '.xml';

        $app = JFactory::getApplication();

        $app->allowCache(false);
        $app->setHeader('Content-Transfer-Encoding', 'binary');
        $app->setHeader('Content-Type', 'text/xml');
        $app->setHeader('Content-Disposition', 'attachment;filename="' . $name . '";');

        // set output content
        $app->setBody($buffer);

        // stream to client
        echo $app->toString();

        jexit();
    }

    /**
     * Process XML restore file.
     *
     * @param object $xml
     *
     * @return bool
     */
    public function import()
    {
        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        jimport('joomla.filesystem.file');

        $app = JFactory::getApplication();
        $tmp = $app->getCfg('tmp_path');

        jimport('joomla.filesystem.file');

        $file = $app->input->files->get('profile_file', null, 'raw');

        // check for valid uploaded file
        if (empty($file) || !is_uploaded_file($file['tmp_name'])) {
            $app->enqueueMessage(JText::_('WF_PROFILES_UPLOAD_NOFILE'), 'error');
            return false;
        }

        if ($file['error'] || $file['size'] < 1) {
            $app->enqueueMessage(JText::_('WF_PROFILES_UPLOAD_NOFILE'), 'error');
            return false;
        }

        // sanitize the file name
        $name = JFile::makeSafe($file['name']);

        if (empty($name)) {
            $app->enqueueMessage(JText::_('WF_PROFILES_IMPORT_ERROR'), 'error');
            return false;
        }

        // Build the appropriate paths.
        $config = JFactory::getConfig();
        $destination = $config->get('tmp_path') . '/' . $name;
        $source = $file['tmp_name'];

        // Move uploaded file.
        JFile::upload($source, $destination, false, true);

        if (!is_file($destination)) {
            $app->enqueueMessage(JText::_('WF_PROFILES_UPLOAD_FAILED'), 'error');
            return false;
        }

        $result = JceProfilesHelper::processImport($destination);

        if ($result === false) {
            $app->enqueueMessage(JText::_('WF_PROFILES_IMPORT_ERROR'), 'error');
            return false;
        }

        $app->enqueueMessage(JText::sprintf('WF_PROFILES_IMPORT_SUCCESS', $result));

        return true;
    }
}
