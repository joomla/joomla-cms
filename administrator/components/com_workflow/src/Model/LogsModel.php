<?php

/**
 * @package Joomla.Administrator
 * @subpackage com_workflow
 *
 * @copyright (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\Workflow\Administrator\Automation\ItemStorage;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Model for the automation execution log (read-only list).
 *
 * @since __DEPLOY_VERSION__
 */
class LogsModel extends ListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id',
                'l.id',
                'item_id',
                'l.item_id',
                'transition_id',
                'l.transition_id',
                'exit_code',
                'l.exit_code',
                'executed_at',
                'l.executed_at',
                'run_as_user_id',
                'l.run_as_user_id',
            ];
        }

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'l.executed_at', $direction = 'DESC')
    {
        /** @var \Joomla\CMS\Application\AdministratorApplication $app */
        $app       = Factory::getApplication();
        $extension = $app->getUserStateFromRequest($this->context . '.filter.extension', 'extension', null, 'cmd');
        $this->setState('filter.extension', $extension);

        // filter.search and filter.exit_code are auto-populated from filter_logs.xml.
        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.extension');
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.exit_code');

        return parent::getStoreId($id);
    }

    public function getListQuery()
    {
        $db                 = $this->getDatabase();
        $automationLogQuery = $db->createQuery();

        $automationLogQuery->select(
            [
                $db->quoteName('l.id'),
                $db->quoteName('l.item_id'),
                $db->quoteName('l.extension'),
                $db->quoteName('l.transition_id'),
                $db->quoteName('t.workflow_id'),
                $db->quoteName('l.from_stage_id'),
                $db->quoteName('l.to_stage_id'),
                $db->quoteName('l.run_as_user_id'),
                $db->quoteName('l.trigger_type'),
                $db->quoteName('l.exit_code'),
                $db->quoteName('l.note'),
                $db->quoteName('l.executed_at'),
                $db->quoteName('t.title', 'transition_title'),
                $db->quoteName('f_stage.title', 'from_stage'),
                $db->quoteName('t_stage.title', 'to_stage'),
                $db->quoteName('u.name', 'run_as_name'),
                $db->quoteName('s.requires_intervention'),
            ]
        )
            ->from($db->quoteName('#__workflow_automation_log', 'l'))
            ->join('LEFT', $db->quoteName('#__workflow_transitions', 't'), $db->quoteName('t.id') . ' = ' . $db->quoteName('l.transition_id'))
            ->join('LEFT', $db->quoteName('#__workflow_stages', 'f_stage'), $db->quoteName('f_stage.id') . ' = ' . $db->quoteName('l.from_stage_id'))
            ->join('LEFT', $db->quoteName('#__workflow_stages', 't_stage'), $db->quoteName('t_stage.id') . ' = ' . $db->quoteName('l.to_stage_id'))
            ->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('l.run_as_user_id'))
            ->join('LEFT', $db->quoteName('#__workflow_item_state', 's'), $db->quoteName('s.item_id') . ' = ' . $db->quoteName('l.item_id') . ' AND ' . $db->quoteName('s.extension') . ' = ' . $db->quoteName('l.extension'));

        if ($extension = (string) $this->getState('filter.extension')) {
            $automationLogQuery->where($db->quoteName('l.extension') . ' = :extension')
                ->bind(':extension', $extension);
        }

        $result = (string) $this->getState('filter.exit_code');

        if ($result === 'ok') {
            $automationLogQuery->where($db->quoteName('l.exit_code') . ' = 0');
        } elseif ($result === 'failed') {
            $automationLogQuery->where($db->quoteName('l.exit_code') . ' NOT IN (0, 4)');
        } elseif ($result === 'skipped') {
            $automationLogQuery->where($db->quoteName('l.exit_code') . ' = 4');
        }

        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (is_numeric($search)) {
                $itemId = (int) $search;
                $automationLogQuery->where($db->quoteName('l.item_id') . ' = :itemId')
                    ->bind(':itemId', $itemId, ParameterType::INTEGER);
            } else {
                $search = '%' . str_replace(' ', '%', trim($search)) . '%';

                $titleLocation = $extension !== ''
                    ? (new ItemStorage($db))->titleLocation($extension)
                    : null;

                if ($titleLocation !== null) {
                    $automationLogQuery->where(
                        '(' . $db->quoteName('l.note') . ' LIKE :search'
                            . ' OR ' . $db->quoteName('l.item_id') . ' IN ('
                            . 'SELECT ' . $db->quoteName($titleLocation['key'])
                            . ' FROM ' . $db->quoteName($titleLocation['table'])
                            . ' WHERE ' . $db->quoteName($titleLocation['titleColumn']) . ' LIKE :titleSearch'
                            . '))'
                    )
                        ->bind(':search', $search)
                        ->bind(':titleSearch', $search);
                } else {
                    // Without a title table to search, only the note can match.
                    $automationLogQuery->where($db->quoteName('l.note') . ' LIKE :search')
                        ->bind(':search', $search);
                }
            }
        }

        $orderColumn    = $this->state->get('list.ordering', 'l.executed_at');
        $orderDirection = strtoupper($this->state->get('list.direction', 'DESC'));

        if (!empty($orderColumn)) {
            $automationLogQuery->order($db->quoteName($db->escape($orderColumn)) . ' ' . $db->escape($orderDirection));
        }

        // Ties on executed_at would otherwise sort unpredictably, which breaks LIMIT/OFFSET paging.
        if ($orderColumn !== 'l.id') {
            $automationLogQuery->order($db->quoteName('l.id') . ' ' . $db->escape($orderDirection));
        }

        return $automationLogQuery;
    }

    /**
     * Adds each row's item title, which no join can supply.
     *
     * @return  object[]|false
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getItems()
    {
        $items = parent::getItems();

        if (empty($items)) {
            return $items;
        }

        return (new ItemStorage($this->getDatabase()))->annotateTitles($items);
    }

    /**
     * Clears the requires_intervention flag for an item so the scheduler retries it.
     *
     * Checks the permission itself, so it is safe to call from anywhere, not only the controller.
     *
     * @param   integer  $itemId     The content item id.
     * @param   string   $extension  The workflow extension, e.g. com_content.article.
     *
     * @return  boolean  True when the flag was cleared, false when the current user may not.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function clearIntervention(int $itemId, string $extension): bool
    {
        if (!$this->getCurrentUser()->authorise('core.admin', 'com_workflow')) {
            return false;
        }

        $db                     = $this->getDatabase();
        $clearInterventionQuery = $db->getQuery(true)
            ->update($db->quoteName('#__workflow_item_state'))
            ->set($db->quoteName('requires_intervention') . ' = 0')
            ->where($db->quoteName('item_id') . ' = :itemId')
            ->where($db->quoteName('extension') . ' = :extension')
            ->bind(':itemId', $itemId, ParameterType::INTEGER)
            ->bind(':extension', $extension, ParameterType::STRING);

        $db->setQuery($clearInterventionQuery)->execute();

        return true;
    }
}
