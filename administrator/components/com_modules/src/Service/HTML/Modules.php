<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Administrator\Service\HTML;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Modules\Administrator\Helper\ModulesHelper;
use Joomla\Component\Templates\Administrator\Helper\TemplatesHelper;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTMLHelper module helper class.
 *
 * @since  1.6
 */
class Modules
{
    /**
     * Builds an array of template options
     *
     * @param   integer  $clientId  The client id.
     * @param   string   $state     The state of the template.
     *
     * @return  array
     */
    public function templates($clientId = 0, $state = '')
    {
        $options   = [];
        $templates = ModulesHelper::getTemplates($clientId, $state);

        foreach ($templates as $template) {
            $options[] = HTMLHelper::_('select.option', $template->element, $template->name);
        }

        return $options;
    }

    /**
     * Builds an array of template type options
     *
     * @return  array
     */
    public function types()
    {
        $options = [];
        $options[] = HTMLHelper::_('select.option', 'user', 'COM_MODULES_OPTION_POSITION_USER_DEFINED');
        $options[] = HTMLHelper::_('select.option', 'template', 'COM_MODULES_OPTION_POSITION_TEMPLATE_DEFINED');

        return $options;
    }

    /**
     * Builds an array of template state options
     *
     * @return  array
     */
    public function templateStates()
    {
        $options = [];
        $options[] = HTMLHelper::_('select.option', '1', 'JENABLED');
        $options[] = HTMLHelper::_('select.option', '0', 'JDISABLED');

        return $options;
    }

    /**
     * Returns a published state on a grid
     *
     * @param   integer  $value     The state value.
     * @param   integer  $i         The row index
     * @param   boolean  $enabled   An optional setting for access control on the action.
     * @param   string   $checkbox  An optional prefix for checkboxes.
     *
     * @return  string        The Html code
     *
     * @see     HTMLHelperJGrid::state
     * @since   1.7.1
     */
    public function state($value, $i, $enabled = true, $checkbox = 'cb')
    {
        $states = [
            1  => [
                'unpublish',
                'COM_MODULES_EXTENSION_PUBLISHED_ENABLED',
                'COM_MODULES_HTML_UNPUBLISH_ENABLED',
                'COM_MODULES_EXTENSION_PUBLISHED_ENABLED',
                true,
                'publish',
                'publish',
            ],
            0  => [
                'publish',
                'COM_MODULES_EXTENSION_UNPUBLISHED_ENABLED',
                'COM_MODULES_HTML_PUBLISH_ENABLED',
                'COM_MODULES_EXTENSION_UNPUBLISHED_ENABLED',
                true,
                'unpublish',
                'unpublish',
            ],
            -1 => [
                'unpublish',
                'COM_MODULES_EXTENSION_PUBLISHED_DISABLED',
                'COM_MODULES_HTML_UNPUBLISH_DISABLED',
                'COM_MODULES_EXTENSION_PUBLISHED_DISABLED',
                true,
                'warning',
                'warning',
            ],
            -2 => [
                'publish',
                'COM_MODULES_EXTENSION_UNPUBLISHED_DISABLED',
                'COM_MODULES_HTML_PUBLISH_DISABLED',
                'COM_MODULES_EXTENSION_UNPUBLISHED_DISABLED',
                true,
                'unpublish',
                'unpublish',
            ],
        ];

        return HTMLHelper::_('jgrid.state', $states, $value, $i, 'modules.', $enabled, true, $checkbox);
    }

    /**
     * Display a batch widget for the module position selector.
     *
     * @param   integer  $clientId          The client ID.
     * @param   integer  $state             The state of the module (enabled, unenabled, trashed).
     * @param   string   $selectedPosition  The currently selected position for the module.
     *
     * @return  string   The necessary positions for the widget.
     *
     * @since   2.5
     */
    public function positions($clientId, $state = 1, $selectedPosition = '')
    {
        $templates      = array_keys(ModulesHelper::getTemplates($clientId, $state));
        $templateGroups = [];

        // Add an empty value to be able to deselect a module position
        $option = ModulesHelper::createOption('', Text::_('COM_MODULES_NONE'));
        $templateGroups[''] = ModulesHelper::createOptionGroup('', [$option]);

        // Add positions from templates
        $isTemplatePosition = false;

        foreach ($templates as $template) {
            $options = [];

            $positions = TemplatesHelper::getPositions($clientId, $template);

            if (is_array($positions)) {
                foreach ($positions as $position) {
                    $text = ModulesHelper::getTranslatedModulePosition($clientId, $template, $position) . ' [' . $position . ']';
                    $options[] = ModulesHelper::createOption($position, $text);

                    if (!$isTemplatePosition && $selectedPosition === $position) {
                        $isTemplatePosition = true;
                    }
                }

                $options = ArrayHelper::sortObjects($options, 'text');
            }

            $templateGroups[$template] = ModulesHelper::createOptionGroup(ucfirst($template), $options);
        }

        // Add custom position to options
        $customGroupText = Text::_('COM_MODULES_CUSTOM_POSITION');
        $editPositions   = true;
        $customPositions = ModulesHelper::getPositions($clientId, $editPositions);

        $app = Factory::getApplication();

        $position = $app->getUserState('com_modules.modules.' . $clientId . '.filter.position');

        if ($position) {
            $customPositions[] = HTMLHelper::_('select.option', $position);

            $customPositions = array_unique($customPositions, SORT_REGULAR);
        }

        $templateGroups[$customGroupText] = ModulesHelper::createOptionGroup($customGroupText, $customPositions);

        return $templateGroups;
    }

    /**
     * Get a select with the batch action options
     *
     * @return  void
     */
    public function batchOptions()
    {
        // Create the copy/move options.
        $options = [
            HTMLHelper::_('select.option', 'c', Text::_('JLIB_HTML_BATCH_COPY')),
            HTMLHelper::_('select.option', 'm', Text::_('JLIB_HTML_BATCH_MOVE'))
        ];

        echo HTMLHelper::_('select.radiolist', $options, 'batch[move_copy]', '', 'value', 'text', 'm');
    }

    /**
     * Method to get the field options.
     *
     * @param   integer  $clientId  The client ID
     *
     * @return  array  The field option objects.
     *
     * @since   2.5
     *
     * @deprecated  5.0 Will be removed with no replacement
     */
    public function positionList($clientId = 0)
    {
        $clientId = (int) $clientId;
        $db       = Factory::getDbo();
        $query    = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('position', 'value'))
            ->select($db->quoteName('position', 'text'))
            ->from($db->quoteName('#__modules'))
            ->where($db->quoteName('client_id') . ' = :clientid')
            ->order($db->quoteName('position'))
            ->bind(':clientid', $clientId, ParameterType::INTEGER);

        // Get the options.
        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        // Pop the first item off the array if it's blank
        if (count($options)) {
            if (strlen($options[0]->text) < 1) {
                array_shift($options);
            }
        }

        return $options;
    }
}
