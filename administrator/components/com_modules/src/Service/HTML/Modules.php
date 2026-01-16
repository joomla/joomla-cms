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
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Modules\Administrator\Helper\ModulesHelper;
use Joomla\Component\Templates\Administrator\Helper\TemplatesHelper;
use Joomla\Database\DatabaseAwareTrait;
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
    use DatabaseAwareTrait;

    /**
     * Generate the markup to display the item associations
     *
     * @param   int  $itemid  The menu item id
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     *
     * @throws \Exception If there is an error on the query
     */
    public function association($itemid)
    {
        // Defaults
        $html = '';

        // Get the associations
        $associations = ModulesHelper::getAssociations($itemid);

        if ($associations) {
            // Get the associated menu items
            $db    = $this->getDatabase();
            $query = $db->createQuery()
                ->select(
                    [
                        $db->quoteName('m.id'),
                        $db->quoteName('m.title'),
                        $db->quoteName('l.sef', 'lang_sef'),
                        $db->quoteName('l.lang_code'),
                        $db->quoteName('l.image'),
                        $db->quoteName('l.title', 'language_title'),
                    ]
                )
                ->from($db->quoteName('#__modules', 'm'))
                ->join('LEFT', $db->quoteName('#__languages', 'l'), $db->quoteName('m.language') . ' = ' . $db->quoteName('l.lang_code'))
                ->whereIn($db->quoteName('m.id'), array_values($associations))
                ->where($db->quoteName('m.id') . ' != :itemid')
                ->bind(':itemid', $itemid, ParameterType::INTEGER);
            $db->setQuery($query);

            try {
                $items = $db->loadObjectList('id');
            } catch (\RuntimeException $e) {
                throw new \Exception($e->getMessage(), 500);
            }

            // Construct html
            if ($items) {
                $languages         = LanguageHelper::getContentLanguages([0, 1]);
                $content_languages = array_column($languages, 'lang_code');

                foreach ($items as &$item) {
                    if (\in_array($item->lang_code, $content_languages)) {
                        $text    = $item->lang_code;
                        $url     = Route::_('index.php?option=com_modules&task=modules.edit&id=' . (int) $item->id);
                        $tooltip = '<strong>' . htmlspecialchars($item->language_title, ENT_QUOTES, 'UTF-8') . '</strong><br>'
                            . Text::sprintf('COM_MODULES_MODULE_SPRINTF', $item->title);
                        $classes = 'badge bg-secondary';

                        $item->link = '<a href="' . $url . '" class="' . $classes . '">' . $text . '</a>'
                            . '<div role="tooltip" id="tip-' . (int) $itemid . '-' . (int) $item->id . '">' . $tooltip . '</div>';
                    } else {
                        // Display warning if Content Language is trashed or deleted
                        Factory::getApplication()->enqueueMessage(Text::sprintf('JGLOBAL_ASSOCIATIONS_CONTENTLANGUAGE_WARNING', $item->lang_code), 'warning');
                    }
                }
            }

            $html = LayoutHelper::render('joomla.content.associations', $items);
        }

        return $html;
    }

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
        $options   = [];
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
        $options   = [];
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
            1 => [
                'unpublish',
                'COM_MODULES_EXTENSION_PUBLISHED_ENABLED',
                'COM_MODULES_HTML_UNPUBLISH_ENABLED',
                'COM_MODULES_EXTENSION_PUBLISHED_ENABLED',
                true,
                'publish',
                'publish',
            ],
            0 => [
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
        $option             = ModulesHelper::createOption('', Text::_('COM_MODULES_NONE'));
        $templateGroups[''] = ModulesHelper::createOptionGroup('', [$option]);

        // Add positions from templates
        $isTemplatePosition = false;

        foreach ($templates as $template) {
            $options = [];

            $positions = TemplatesHelper::getPositions($clientId, $template);

            if (\is_array($positions)) {
                foreach ($positions as $position) {
                    $text      = ModulesHelper::getTranslatedModulePosition($clientId, $template, $position) . ' [' . $position . ']';
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
            HTMLHelper::_('select.option', 'm', Text::_('JLIB_HTML_BATCH_MOVE')),
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
     * @deprecated  4.3 will be removed in 7.0
     *              Will be removed with no replacement
     */
    public function positionList($clientId = 0)
    {
        $clientId = (int) $clientId;
        $db       = $this->getDatabase();
        $query    = $db->createQuery()
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
        if (\count($options)) {
            if (\strlen($options[0]->text) < 1) {
                array_shift($options);
            }
        }

        return $options;
    }
}
