<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Workflow\Administrator\View\Workflow\HtmlView $this */

$arrow = $this->getLanguage()->isRtl() ? 'arrow-left' : 'arrow-right';
if (empty($this->automationLog)) : ?>
    <div class="alert alert-info">
        <span class="icon-info-circle" aria-hidden="true"></span>
        <?php echo Text::_('COM_WORKFLOW_LOG_EMPTY'); ?>
    </div>
    <?php
    return;
endif;
?>
<table class="table">
    <caption class="visually-hidden"><?php echo Text::_('COM_WORKFLOW_LOG_TAB'); ?></caption>
    <thead>
        <tr>
            <th scope="col"><?php echo Text::_('COM_WORKFLOW_LOGS_EXECUTED_AT'); ?></th>
            <th scope="col"><?php echo Text::_('COM_WORKFLOW_LOGS_ITEM'); ?></th>
            <th scope="col"><?php echo Text::_('JCATEGORY'); ?></th>
            <th scope="col"><?php echo Text::_('COM_WORKFLOW_LOGS_TRANSITION'); ?></th>
            <th scope="col"><?php echo Text::_('COM_WORKFLOW_LOGS_STAGES'); ?></th>
            <th scope="col"><?php echo Text::_('COM_WORKFLOW_LOGS_RUN_AS'); ?></th>
            <th scope="col" class="text-center"><?php echo Text::_('COM_WORKFLOW_LOGS_RESULT'); ?></th>
            <th scope="col"><?php echo Text::_('COM_WORKFLOW_LOGS_NOTE'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($this->automationLog as $entry) :
            $extensionParts = explode('.', (string) $entry->extension);
            $itemEditLink   = (!empty($extensionParts[0]) && !empty($extensionParts[1]))
                ? Route::_('index.php?option=' . $extensionParts[0] . '&task=' . $extensionParts[1] . '.edit&id=' . (int) $entry->item_id)
                : '';
            $itemTitle      = (string) ($entry->item_title ?? '');
            $itemLabel      = $itemTitle !== '' ? $itemTitle : (string) (int) $entry->item_id;
            ?>
            <tr>
                <td><?php echo HTMLHelper::_('date', $entry->executed_at, Text::_('DATE_FORMAT_LC5')); ?></td>
                <th scope="row">
                    <?php if ($itemEditLink) : ?>
                        <a href="<?php echo $itemEditLink; ?>"><?php echo $this->escape($itemLabel); ?></a>
                    <?php else : ?>
                        <?php echo $this->escape($itemLabel); ?>
                    <?php endif; ?>
                    <?php if ($itemTitle !== '') : ?>
                        <div class="small"><?php echo Text::sprintf('COM_WORKFLOW_LOGS_ITEM_ID_INLINE', (int) $entry->item_id); ?></div>
                    <?php endif; ?>
                </th>
                <td>
                    <?php if (!empty($entry->item_category_id)) : ?>
                        <a href="<?php echo Route::_('index.php?option=com_categories&task=category.edit&id=' . (int) $entry->item_category_id . '&extension=' . urlencode((string) $entry->item_category_extension)); ?>">
                            <?php echo $this->escape($entry->item_category_title); ?>
                        </a>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    // No permission check: reaching this tab already required core.edit on the
                    // workflow these transitions belong to.
                    $transitionEdit = Route::_(
                        'index.php?option=com_workflow&task=transition.edit&id=' . (int) $entry->transition_id
                            . '&workflow_id=' . (int) $entry->workflow_id
                            . '&extension=' . $this->escape((string) $entry->extension)
                    );
                    ?>
                    <a href="<?php echo $transitionEdit; ?>">
                        <?php echo $this->escape(Text::_((string) $entry->transition_title)); ?>
                    </a>
                </td>
                <td>
                    <?php if ($entry->from_stage !== $entry->to_stage) : ?>
                        <span class="badge bg-secondary"><?php echo $this->escape(Text::_((string) $entry->from_stage)); ?></span>
                        <span class="icon-<?php echo $arrow; ?> icon-fw" aria-hidden="true"></span>
                        <span class="badge bg-secondary"><?php echo $this->escape(Text::_((string) $entry->to_stage)); ?></span>
                    <?php endif; ?>
                </td>
                <td><?php echo $this->escape((string) $entry->run_as_name); ?></td>
                <td class="text-center">
                    <?php if ((int) $entry->exit_code === 0) : ?>
                        <span class="badge bg-success"><?php echo Text::_('JYES'); ?></span>
                    <?php else : ?>
                        <span class="badge bg-danger"><?php echo Text::_('JNO'); ?></span>
                    <?php endif; ?>
                </td>
                <td><?php echo $this->escape((string) $entry->note); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<p>
    <a href="<?php echo Route::_('index.php?option=com_workflow&view=logs&extension=' . $this->escape($this->extension) . ($this->section ? '.' . $this->section : '')); ?>">
        <?php echo Text::_('COM_WORKFLOW_LOG_VIEW_ALL'); ?>
    </a>
</p>
