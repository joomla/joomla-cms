<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Task.WorkflowTransition
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Task\WorkflowTransition\Dto;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * One automation rule that is due to be considered for a single item this run.
 *
 * @since  __DEPLOY_VERSION__
 */
final class DueAutomation
{
    public int $rule_id;
    public int $transition_id;
    public int $from_stage_id;
    public int $to_stage_id;
    public ?int $delay_value;
    public ?string $delay_unit;
    public string $rule_type;
    public ?string $cron_expression;
    public ?string $item_filter;
    public ?string $fire_condition;
    public int $ordering;
    public int $item_id;
    public string $extension;
    public int $item_state_id;
    public string $entered_at;
    public int $run_as_user_id;
    public ?string $last_failure_reason;

    /**
     * Builds an instance from a database row.
     *
     * @param   object  $row  A joined schedule/transition/rule row.
     *
     * @return  self
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function fromRow(object $row): self
    {
        $candidate = new self();

        $candidate->rule_id             = (int) $row->rule_id;
        $candidate->transition_id       = (int) $row->transition_id;
        $candidate->from_stage_id       = (int) $row->from_stage_id;
        $candidate->to_stage_id         = (int) $row->to_stage_id;
        $candidate->delay_value         = $row->delay_value !== null ? (int) $row->delay_value : null;
        $candidate->delay_unit          = $row->delay_unit;
        $candidate->rule_type           = (string) $row->rule_type;
        $candidate->cron_expression     = $row->cron_expression;
        $candidate->item_filter         = $row->item_filter;
        $candidate->fire_condition      = $row->fire_condition;
        $candidate->ordering            = (int) $row->ordering;
        $candidate->item_id             = (int) $row->item_id;
        $candidate->extension           = (string) $row->extension;
        $candidate->item_state_id       = (int) $row->item_state_id;
        $candidate->entered_at          = (string) $row->entered_at;
        $candidate->run_as_user_id      = (int) $row->run_as_user_id;
        $candidate->last_failure_reason = $row->last_failure_reason;

        return $candidate;
    }
}
