<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Utility class working with workflow states select lists
 *
 * @since  4.0.0
 */
abstract class WorkflowStage
{
    /**
     * Get a list of the available workflow stages.
     *
     * @param   array  $options  An array of options for the control
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public static function existing($options)
    {
        // Get the database object and a new query object.
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        // Build the query.
        $query->select(
            [
                $db->quoteName('ws.id', 'workflow_stage_id'),
                $db->quoteName('ws.title', 'workflow_stage_title'),
                $db->quoteName('w.id', 'workflow_id'),
                $db->quoteName('w.title', 'workflow_title'),
            ]
        )
            ->from($db->quoteName('#__workflow_stages', 'ws'))
            ->join('LEFT', $db->quoteName('#__workflows', 'w'), $db->quoteName('w.id') . ' = ' . $db->quoteName('ws.workflow_id'))
            ->where($db->quoteName('w.published') . ' = 1')
            ->order($db->quoteName('ws.ordering'));

        // Set the query and load the options.
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

        $prefix = [[
            HTMLHelper::_('select.option', '', $options['title']),
        ]];

        return array_merge($prefix, $workflowStages);
    }
}
