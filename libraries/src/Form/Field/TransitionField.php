<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Workflow Transitions field.
 *
 * @since  4.0.0
 */
class TransitionField extends GroupedlistField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $type = 'Transition';

    /**
     * The component and section separated by ".".
     *
     * @var    string
     * @since  4.0.0
     */
    protected $extension;

    /**
     * The workflow stage to use.
     *
     * @var   integer
     */
    protected $workflowStage;

    /**
     * Method to setup the extension
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @since   4.0.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $result = parent::setup($element, $value, $group);

        if ($result) {
            $input = Factory::getApplication()->getInput();

            if (\strlen($element['extension'])) {
                $this->extension = (string) $element['extension'];
            } else {
                $this->extension = $input->getCmd('extension');
            }

            if (\strlen($element['workflow_stage'])) {
                $this->workflowStage = (int) $element['workflow_stage'];
            } else {
                $this->workflowStage = $input->getInt('id');
            }
        }

        return $result;
    }

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array[]  An array of HTMLHelper options.
     *
     * @since  4.0.0
     */
    protected function getGroups()
    {
        // Initialise variable.
        $db            = $this->getDatabase();
        $extension     = $this->extension;
        $workflowStage = (int) $this->workflowStage;

        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('t.id', 'value'),
                    $db->quoteName('t.title', 'text'),
                ]
            )
            ->from(
                [
                    $db->quoteName('#__workflow_transitions', 't'),
                    $db->quoteName('#__workflow_stages', 's'),
                    $db->quoteName('#__workflow_stages', 's2'),
                ]
            )
            ->whereIn($db->quoteName('t.from_stage_id'), [-1, $workflowStage])
            ->where(
                [
                    $db->quoteName('t.to_stage_id') . ' = ' . $db->quoteName('s.id'),
                    $db->quoteName('s.workflow_id') . ' = ' . $db->quoteName('s2.workflow_id'),
                    $db->quoteName('s.workflow_id') . ' = ' . $db->quoteName('t.workflow_id'),
                    $db->quoteName('s2.id') . ' = :stage1',
                    $db->quoteName('t.published') . ' = 1',
                    $db->quoteName('s.published') . ' = 1',
                ]
            )
            ->bind(':stage1', $workflowStage, ParameterType::INTEGER)
            ->order($db->quoteName('t.ordering'));

        $items = $db->setQuery($query)->loadObjectList();

        Factory::getLanguage()->load('com_workflow', JPATH_ADMINISTRATOR);

        $parts = explode('.', $extension);

        $component = reset($parts);

        if (\count($items)) {
            $user = Factory::getUser();

            $items = array_filter(
                $items,
                function ($item) use ($user, $component) {
                    return $user->authorise('core.execute.transition', $component . '.transition.' . $item->value);
                }
            );

            foreach ($items as $item) {
                $item->text = Text::_($item->text);
            }
        }

        // Get workflow stage title
        $query = $db->getQuery(true)
            ->select($db->quoteName('title'))
            ->from($db->quoteName('#__workflow_stages'))
            ->where($db->quoteName('id') . ' = :stage')
            ->bind(':stage', $workflowStage, ParameterType::INTEGER);

        $workflowName = $db->setQuery($query)->loadResult();

        $default = [[HTMLHelper::_('select.option', '', Text::_($workflowName))]];

        $groups = parent::getGroups();

        if (\count($items)) {
            $groups[Text::_('COM_CONTENT_RUN_TRANSITION')] = $items;
        }

        if (\count($groups)) {
            $default[][] = HTMLHelper::_('select.option', '-1', '--------', ['disable' => true]);
        }

        // Merge with defaults
        return array_merge($default, $groups);
    }
}
