<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Administrator\Model;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Access\Rules;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Model for component configuration
 *
 * @since  3.2
 */
class ComponentModel extends FormModel
{
    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   3.2
     */
    protected function populateState()
    {
        $input = Factory::getApplication()->getInput();

        // Set the component (option) we are dealing with.
        $component = $input->get('component');

        $this->state->set('component.option', $component);

        // Set an alternative path for the configuration file.
        if ($path = $input->getString('path')) {
            $path = Path::clean(JPATH_SITE . '/' . $path);
            Path::check($path);
            $this->state->set('component.path', $path);
        }
    }

    /**
     * Method to get a form object.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed  A JForm object on success, false on failure
     *
     * @since   3.2
     */
    public function getForm($data = [], $loadData = true)
    {
        $state  = $this->getState();
        $option = $state->get('component.option');

        if ($path = $state->get('component.path')) {
            // Add the search path for the admin component config.xml file.
            Form::addFormPath($path);
        } else {
            // Add the search path for the admin component config.xml file.
            Form::addFormPath(JPATH_ADMINISTRATOR . '/components/' . $option);
        }

        // Get the form.
        $form = $this->loadForm(
            'com_config.component',
            'config',
            ['control' => 'jform', 'load_data' => $loadData],
            false,
            '/config'
        );

        if (empty($form)) {
            return false;
        }

        $lang = Factory::getLanguage();
        $lang->load($option, JPATH_BASE)
        || $lang->load($option, JPATH_BASE . "/components/$option");

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  array  The default data is an empty array.
     *
     * @since   4.0.0
     */
    protected function loadFormData()
    {
        $option = $this->getState()->get('component.option');

        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_config.edit.component.' . $option . '.data', []);

        if (empty($data)) {
            return $this->getComponent()->getParams()->toArray();
        }

        return $data;
    }

    /**
     * Get the component information.
     *
     * @return  object
     *
     * @since   3.2
     */
    public function getComponent()
    {
        $state  = $this->getState();
        $option = $state->get('component.option');

        // Load common and local language files.
        $lang = Factory::getLanguage();
        $lang->load($option, JPATH_BASE)
        || $lang->load($option, JPATH_BASE . "/components/$option");

        $result = ComponentHelper::getComponent($option);

        return $result;
    }

    /**
     * Method to save the configuration data.
     *
     * @param   array  $data  An array containing all global config data.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   3.2
     * @throws  \RuntimeException
     */
    public function save($data)
    {
        $table      = Table::getInstance('extension');
        $context    = $this->option . '.' . $this->name;
        PluginHelper::importPlugin('extension');

        // Check super user group.
        if (isset($data['params']) && !$this->getCurrentUser()->authorise('core.admin')) {
            $form = $this->getForm([], false);

            foreach ($form->getFieldsets() as $fieldset) {
                foreach ($form->getFieldset($fieldset->name) as $field) {
                    if (
                        $field->type === 'UserGroupList' && isset($data['params'][$field->fieldname])
                        && (int) $field->getAttribute('checksuperusergroup', 0) === 1
                        && Access::checkGroup($data['params'][$field->fieldname], 'core.admin')
                    ) {
                        throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
                    }
                }
            }
        }

        // Save the rules.
        if (isset($data['params']) && isset($data['params']['rules'])) {
            if (!$this->getCurrentUser()->authorise('core.admin', $data['option'])) {
                throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
            }

            $rules = new Rules($data['params']['rules']);
            $asset = Table::getInstance('asset');

            if (!$asset->loadByName($data['option'])) {
                $root = Table::getInstance('asset');
                $root->loadByName('root.1');
                $asset->name  = $data['option'];
                $asset->title = $data['option'];
                $asset->setLocation($root->id, 'last-child');
            }

            $asset->rules = (string) $rules;

            if (!$asset->check() || !$asset->store()) {
                throw new \RuntimeException($asset->getError());
            }

            // We don't need this anymore
            unset($data['option']);
            unset($data['params']['rules']);
        }

        // Load the previous Data
        if (!$table->load($data['id'])) {
            throw new \RuntimeException($table->getError());
        }

        unset($data['id']);

        // Bind the data.
        if (!$table->bind($data)) {
            throw new \RuntimeException($table->getError());
        }

        // Check the data.
        if (!$table->check()) {
            throw new \RuntimeException($table->getError());
        }

        $result = Factory::getApplication()->triggerEvent('onExtensionBeforeSave', [$context, $table, false]);

        // Store the data.
        if (in_array(false, $result, true) || !$table->store()) {
            throw new \RuntimeException($table->getError());
        }

        Factory::getApplication()->triggerEvent('onExtensionAfterSave', [$context, $table, false]);

        // Clean the component cache.
        $this->cleanCache('_system');

        return true;
    }
}
