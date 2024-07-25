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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Workflow Stages field.
 *
 * @since  4.0.0
 */
class WorkflowstageField extends GroupedlistField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since  4.0.0
     */
    protected $type = 'Workflowstage';

    /**
     * The component and section separated by ".".
     *
     * @var    string
     * @since  4.0.0
     */
    protected $extension = '';

    /**
     * Show only the stages which has an item attached
     *
     * @var     boolean
     * @since  4.0.0
     */
    protected $activeonly = false;

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @since  4.0.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $result = parent::setup($element, $value, $group);

        if ($result) {
            if (\strlen($element['extension'])) {
                $this->extension = (string) $element['extension'];
            } else {
                $this->extension = Factory::getApplication()->getInput()->getCmd('extension');
            }

            if ((string) $element['activeonly'] === '1' || (string) $element['activeonly'] === 'true') {
                $this->activeonly = true;
            }
        }

        return $result;
    }

    /**
     * Method to get the field option groups.
     *
     * @return  array[]  The field option objects as a nested array in groups.
     *
     * @since  4.0.0
     * @throws  \UnexpectedValueException
     */
    protected function getGroups()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Select distinct stages for existing articles
        $query
            ->select(
                [
                    'DISTINCT ' . $db->quoteName('ws.id', 'workflow_stage_id'),
                    $db->quoteName('ws.title', 'workflow_stage_title'),
                    $db->quoteName('w.title', 'workflow_title'),
                    $db->quoteName('w.id', 'workflow_id'),
                    $db->quoteName('w.ordering', 'ordering'),
                    $db->quoteName('ws.ordering', 'workflow_stage_ordering'),
                ]
            )
            ->from($db->quoteName('#__workflow_stages', 'ws'))
            ->from($db->quoteName('#__workflows', 'w'))
            ->where(
                [
                    $db->quoteName('ws.workflow_id') . ' = ' . $db->quoteName('w.id'),
                    $db->quoteName('w.extension') . ' = :extension',
                ]
            )
            ->bind(':extension', $this->extension)
            ->order(
                [
                    $db->quoteName('w.ordering'),
                    $db->quoteName('ws.ordering'),
                ]
            );

        if ($this->activeonly) {
            $query
                ->from($db->quoteName('#__workflow_associations', 'wa'))
                ->where(
                    [
                        $db->quoteName('wa.stage_id') . ' = ' . $db->quoteName('ws.id'),
                        $db->quoteName('wa.extension') . ' = :associationExtension',
                    ]
                )
                ->bind(':associationExtension', $this->extension);
        }

        $stages = $db->setQuery($query)->loadObjectList();

        $workflowStages = [];

        // Grouping the stages by workflow
        foreach ($stages as $stage) {
            // Using workflow ID to differentiate workflows having same title
            $workflowStageKey = Text::_($stage->workflow_title) . ' (' . $stage->workflow_id . ')';

            if (!\array_key_exists($workflowStageKey, $workflowStages)) {
                $workflowStages[$workflowStageKey] = [];
            }

            $workflowStages[$workflowStageKey][] = HTMLHelper::_('select.option', $stage->workflow_stage_id, Text::_($stage->workflow_stage_title));
        }

        // Merge any additional options in the XML definition.
        return array_merge(parent::getGroups(), $workflowStages);
    }
}
