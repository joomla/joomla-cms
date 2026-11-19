<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Workflow\Administrator\Automation\RelativeTime;

/**
 * Renders a table of upcoming automated transitions.
 *
 * @var   array  $displayData
 * @var   \Joomla\Component\Workflow\Administrator\Automation\UpcomingTransition[]  $items
 * @var   boolean  $showWorkflow  Whether to show the owning workflow as a column.
 */
$items        = $displayData['items'] ?? [];
$showWorkflow = (bool) ($displayData['showWorkflow'] ?? false);
$arrow        = Factory::getApplication()->getLanguage()->isRtl() ? 'arrow-left' : 'arrow-right';

if (empty($items)) : ?>
    <div class="alert alert-info">
        <span class="icon-info-circle" aria-hidden="true"></span>
        <?php echo Text::_('COM_WORKFLOW_UPCOMING_EMPTY'); ?>
    </div>
    <?php
    return;
endif;

$unitKeys = [
    'minutes' => 'COM_WORKFLOW_AUTOMATION_UNIT_MINUTES',
    'hours'   => 'COM_WORKFLOW_AUTOMATION_UNIT_HOURS',
    'days'    => 'COM_WORKFLOW_AUTOMATION_UNIT_DAYS',
    'months'  => 'COM_WORKFLOW_AUTOMATION_UNIT_MONTHS',
];
?>
<table class="table">
    <caption class="visually-hidden"><?php echo Text::_('COM_WORKFLOW_UPCOMING_TAB'); ?></caption>
    <thead>
        <tr>
            <th scope="col"><?php echo Text::_('COM_WORKFLOW_UPCOMING_COL_ITEM'); ?></th>
            <?php if ($showWorkflow) : ?>
                <th scope="col"><?php echo Text::_('COM_WORKFLOW_UPCOMING_COL_WORKFLOW'); ?></th>
            <?php endif; ?>
            <th scope="col"><?php echo Text::_('COM_WORKFLOW_UPCOMING_COL_MOVE'); ?></th>
            <th scope="col"><?php echo Text::_('COM_WORKFLOW_UPCOMING_COL_FIRES'); ?></th>
            <th scope="col"><?php echo Text::_('COM_WORKFLOW_UPCOMING_COL_TRIGGER'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $transition) : ?>
            <tr>
                <th scope="row">
                    <?php
                    $itemLabel = $transition->itemTitle !== ''
                        ? $transition->itemTitle
                        : Text::sprintf('COM_WORKFLOW_UPCOMING_ITEM_FALLBACK', $transition->itemId);
                    ?>
                    <?php if ($transition->editUrl !== '') : ?>
                        <a href="<?php echo Route::_($transition->editUrl); ?>">
                            <?php echo htmlspecialchars($itemLabel, ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    <?php else : ?>
                        <?php echo htmlspecialchars($itemLabel, ENT_QUOTES, 'UTF-8'); ?>
                    <?php endif; ?>
                </th>
                <?php if ($showWorkflow) : ?>
                    <td>
                        <?php $workflowLabel = htmlspecialchars(Text::_($transition->workflowTitle), ENT_QUOTES, 'UTF-8'); ?>
                        <?php if ($transition->workflowId > 0) : ?>
                            <a href="<?php echo Route::_('index.php?option=com_workflow&task=workflow.edit&id=' . $transition->workflowId . '&extension=' . urlencode($transition->extension)); ?>">
                                <?php echo $workflowLabel; ?>
                            </a>
                        <?php else : ?>
                            <?php echo $workflowLabel; ?>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>
                <td>
                    <?php echo htmlspecialchars(Text::_($transition->transitionTitle), ENT_QUOTES, 'UTF-8'); ?>
                    <?php if ($transition->fromStage !== $transition->toStage) : ?>
                        <div class="small mt-1">
                            <span class="badge bg-secondary"><?php echo htmlspecialchars(Text::_($transition->fromStage), ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="icon-<?php echo $arrow; ?> icon-fw" aria-hidden="true"></span>
                            <span class="badge bg-secondary"><?php echo htmlspecialchars(Text::_($transition->toStage), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($transition->status === 'needs_attention') : ?>
                        <span class="badge bg-danger"><?php echo Text::_('COM_WORKFLOW_UPCOMING_STATUS_ATTENTION'); ?></span>
                    <?php elseif ($transition->status === 'rule_error') : ?>
                        <span class="badge bg-warning text-dark"><?php echo Text::_('COM_WORKFLOW_UPCOMING_STATUS_RULE_ERROR'); ?></span>
                    <?php elseif ($transition->status === 'not_scheduled') : ?>
                        <span class="badge bg-secondary"><?php echo Text::_('COM_WORKFLOW_UPCOMING_STATUS_NOT_SCHEDULED'); ?></span>
                    <?php elseif ($transition->firesAt === null) : ?>
                        <span class="badge bg-secondary"><?php echo Text::_('COM_WORKFLOW_UPCOMING_STATUS_NOT_SCHEDULED'); ?></span>
                    <?php else : ?>
                        <div><?php echo RelativeTime::until($transition->firesAt); ?></div>
                        <div class="small"><?php echo HTMLHelper::_('date', $transition->firesAt->format('Y-m-d H:i:s'), Text::_('DATE_FORMAT_LC2')); ?></div>
                        <?php if ($transition->hasCondition) : ?>
                            <span class="badge bg-info"><?php echo Text::_('COM_WORKFLOW_UPCOMING_SUBJECT_CONDITION'); ?></span>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php
                    // Shown whatever the status: the fault may belong to another rule on this stage.
                    if ($transition->failureReason !== '') : ?>
                        <div class="small text-warning-emphasis mt-1">
                            <span class="icon-warning" aria-hidden="true"></span>
                            <?php echo htmlspecialchars($transition->failureReason, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <?php if ($transition->failedAt !== null) : ?>
                            <div class="small">
                                <?php echo Text::sprintf(
                                    'COM_WORKFLOW_UPCOMING_LAST_FAILED',
                                    HTMLHelper::_('date', $transition->failedAt->format('Y-m-d H:i:s'), Text::_('DATE_FORMAT_LC2'))
                                ); ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($transition->ruleType === 'cron') : ?>
                        <?php echo Text::sprintf('COM_WORKFLOW_UPCOMING_TRIGGER_CRON', '<code>' . htmlspecialchars((string) $transition->cronExpression, ENT_QUOTES, 'UTF-8') . '</code>'); ?>
                    <?php else : ?>
                        <?php
                        $unitKey = $unitKeys[$transition->delayUnit] ?? '';
                        $unit    = $unitKey !== '' ? Text::_($unitKey) : (string) $transition->delayUnit;
                        echo Text::sprintf('COM_WORKFLOW_UPCOMING_TRIGGER_DELAY', (int) $transition->delayValue, $unit);
                        ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
