<?php

/**
 * @package Joomla.Administrator
 * @subpackage com_workflow
 *
 * @copyright (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Automation;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * One item's next automated transition, as shown in the upcoming-transitions view.
 *
 * @since  __DEPLOY_VERSION__
 */

final class UpcomingTransition
{
    /**
     * @param   integer         $itemId          The content item id.
     * @param   string          $extension       The workflow extension, e.g. com_content.article.
     * @param   string          $itemTitle       The item's title, or '' when it could not be resolved.
     * @param   string          $editUrl         Un-routed admin edit link, or '' when not linkable.
     * @param   string          $fromStage       Title of the stage the item is leaving.
     * @param   string          $toStage         Title of the stage the item moves to.
     * @param   string          $transitionTitle  Title of the transition that will run.
     * @param   \DateTime|null  $firesAt         When the move is due (UTC), or null if uncomputable.
     * @param   string          $status          scheduled | needs_attention | not_scheduled | rule_error.
     * @param   string          $failureReason   Why the rule could not be read, or '' when it could.
     * @param   \DateTime|null  $failedAt        When the scheduler stored the fault, or null if found now.
     * @param   string          $ruleType        delay | cron.
     * @param   integer|null    $delayValue      Delay amount for a delay rule.
     * @param   string|null     $delayUnit       minutes | hours | days | months.
     * @param   string|null     $cronExpression  Cron expression for a cron rule.
     * @param   boolean         $hasCondition    Whether a fire condition gates this move.
     * @param   string          $workflowTitle   Title of the owning workflow.
     * @param   integer         $workflowId      Id of the owning workflow, or 0 when unknown.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(
        public readonly int $itemId,
        public readonly string $extension,
        public readonly string $itemTitle,
        public readonly string $editUrl,
        public readonly string $fromStage,
        public readonly string $toStage,
        public readonly string $transitionTitle,
        public readonly ?\DateTime $firesAt,
        public readonly string $status,
        public readonly string $failureReason,
        public readonly ?\DateTime $failedAt,
        public readonly string $ruleType,
        public readonly ?int $delayValue,
        public readonly ?string $delayUnit,
        public readonly ?string $cronExpression,
        public readonly bool $hasCondition,
        public readonly string $workflowTitle = '',
        public readonly int $workflowId = 0
    ) {
    }
}
