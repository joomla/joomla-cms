<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Site\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\Path;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Config Module model.
 *
 * @since  3.2
 */
class ModulesModel extends FormModel
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
        $app = Factory::getApplication();

        // Load the User state.
        $pk = $app->getInput()->getInt('id');

        $this->setState('module.id', $pk);
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form  A Form object on success, false on failure
     *
     * @since   3.2
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_config.modules', 'modules', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to preprocess the form
     *
     * @param   Form    $form   A form object.
     * @param   mixed   $data   The data expected for the form.
     * @param   string  $group  The name of the plugin group to import (defaults to "content").
     *
     * @return  void
     *
     * @since   3.2
     * @throws  \Exception if there is an error loading the form.
     */
    protected function preprocessForm(Form $form, $data, $group = 'content')
    {
        $lang     = Factory::getLanguage();
        $module   = $this->getState()->get('module.name');
        $basePath = JPATH_BASE;

        $formFile = Path::clean($basePath . '/modules/' . $module . '/' . $module . '.xml');

        // Load the core and/or local language file(s).
        $lang->load($module, $basePath)
            || $lang->load($module, $basePath . '/modules/' . $module);

        if (file_exists($formFile)) {
            // Get the module form.
            if (!$form->loadFile($formFile, false, '//config')) {
                throw new \Exception(Text::_('JERROR_LOADFILE_FAILED'));
            }

            // Attempt to load the xml file.
            if (!$xml = simplexml_load_file($formFile)) {
                throw new \Exception(Text::_('JERROR_LOADFILE_FAILED'));
            }
        }

        // Load the default advanced params
        Form::addFormPath(JPATH_BASE . '/components/com_config/model/form');
        $form->loadFile('modules_advanced', false);

        // Trigger the default form events.
        parent::preprocessForm($form, $data, $group);
    }

    /**
     * Method to get list of module positions in current template
     *
     * @return  array
     *
     * @since   3.2
     */
    public function getPositions()
    {
        $lang         = Factory::getLanguage();
        $templateName = Factory::getApplication()->getTemplate();

        // Load templateDetails.xml file
        $path                     = Path::clean(JPATH_BASE . '/templates/' . $templateName . '/templateDetails.xml');
        $currentTemplatePositions = [];

        if (file_exists($path)) {
            $xml = simplexml_load_file($path);

            if (isset($xml->positions[0])) {
                // Load language files
                $lang->load('tpl_' . $templateName . '.sys', JPATH_BASE)
                || $lang->load('tpl_' . $templateName . '.sys', JPATH_BASE . '/templates/' . $templateName);

                foreach ($xml->positions[0] as $position) {
                    $value = (string) $position;
                    $text  = preg_replace('/[^a-zA-Z0-9_\-]/', '_', 'TPL_' . strtoupper($templateName) . '_POSITION_' . strtoupper($value));

                    // Construct list of positions
                    $currentTemplatePositions[] = self::createOption($value, Text::_($text) . ' [' . $value . ']');
                }
            }
        }

        $templateGroups = [];

        // Add an empty value to be able to deselect a module position
        $option             = self::createOption();
        $templateGroups[''] = self::createOptionGroup('', [$option]);

        $templateGroups[$templateName] = self::createOptionGroup($templateName, $currentTemplatePositions);

        // Add custom position to options
        $customGroupText = Text::_('COM_MODULES_CUSTOM_POSITION');

        $editPositions                    = true;
        $customPositions                  = self::getActivePositions(0, $editPositions);
        $templateGroups[$customGroupText] = self::createOptionGroup($customGroupText, $customPositions);

        return $templateGroups;
    }

    /**
     * Get a list of modules positions
     *
     * @param   integer  $clientId       Client ID
     * @param   boolean  $editPositions  Allow to edit the positions
     *
     * @return  array  A list of positions
     *
     * @since   3.6.3
     */
    public static function getActivePositions($clientId, $editPositions = false)
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('DISTINCT position')
            ->from($db->quoteName('#__modules'))
            ->where($db->quoteName('client_id') . ' = ' . (int) $clientId)
            ->order($db->quoteName('position'));

        $db->setQuery($query);

        try {
            $positions = $db->loadColumn();
            $positions = \is_array($positions) ? $positions : [];
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return;
        }

        // Build the list
        $options = [];

        foreach ($positions as $position) {
            if (!$position && !$editPositions) {
                $options[] = HTMLHelper::_('select.option', 'none', ':: ' . Text::_('JNONE') . ' ::');
            } else {
                $options[] = HTMLHelper::_('select.option', $position, $position);
            }
        }

        return $options;
    }

    /**
     * Create and return a new Option
     *
     * @param   string  $value  The option value [optional]
     * @param   string  $text   The option text [optional]
     *
     * @return  object  The option as an object (stdClass instance)
     *
     * @since   3.6.3
     */
    private static function createOption($value = '', $text = '')
    {
        if (empty($text)) {
            $text = $value;
        }

        $option        = new \stdClass();
        $option->value = $value;
        $option->text  = $text;

        return $option;
    }

    /**
     * Create and return a new Option Group
     *
     * @param   string  $label    Value and label for group [optional]
     * @param   array   $options  Array of options to insert into group [optional]
     *
     * @return  array  Return the new group as an array
     *
     * @since   3.6.3
     */
    private static function createOptionGroup($label = '', $options = [])
    {
        $group          = [];
        $group['value'] = $label;
        $group['text']  = $label;
        $group['items'] = $options;

        return $group;
    }
}
