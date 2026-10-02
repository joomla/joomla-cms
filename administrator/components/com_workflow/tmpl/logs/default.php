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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var \Joomla\Component\Workflow\Administrator\View\Logs\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$extension = $this->escape((string) $this->state->get('filter.extension'));
$user      = $this->getCurrentUser();
?>
<form action="<?php echo Route::_('index.php?option=com_workflow&view=logs&extension=' . $extension); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table">
                        <caption class="visually-hidden"><?php echo Text::_('COM_WORKFLOW_LOGS_TABLE_CAPTION'); ?></caption>
                        <thead>
                            <tr>
                                <th scope="col"><?php echo HTMLHelper::_('searchtools.sort', 'COM_WORKFLOW_LOGS_EXECUTED_AT', 'l.executed_at', $listDirn, $listOrder); ?></th>
                                <th scope="col"><?php echo HTMLHelper::_('searchtools.sort', 'COM_WORKFLOW_LOGS_ITEM', 'l.item_id', $listDirn, $listOrder); ?></th>
                                <th scope="col"><?php echo Text::_('JCATEGORY'); ?></th>
                                <th scope="col"><?php echo Text::_('COM_WORKFLOW_LOGS_TRANSITION'); ?></th>
                                <th scope="col"><?php echo Text::_('COM_WORKFLOW_LOGS_STAGES'); ?></th>
                                <th scope="col"><?php echo Text::_('COM_WORKFLOW_LOGS_RUN_AS'); ?></th>
                                <th scope="col" class="text-center"><?php echo HTMLHelper::_('searchtools.sort', 'COM_WORKFLOW_LOGS_RESULT', 'l.exit_code', $listDirn, $listOrder); ?></th>
                                <th scope="col"><?php echo Text::_('COM_WORKFLOW_LOGS_NOTE'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->items as $item) :
                                $parts = explode('.', (string) $item->extension);
                                $editLink = (!empty($parts[0]) && !empty($parts[1])) ? Route::_('index.php?option=' . $parts[0] . '&task=' . $parts[1] . '.edit&id=' . (int) $item->item_id) : '';

                                $itemTitle = (string) ($item->item_title ?? '');
                                $itemLabel = $itemTitle !== '' ? $itemTitle : (string) (int) $item->item_id;

                                $resetLink = ((int) $item->exit_code !== 0 && !empty($item->requires_intervention))
                                    ? Route::_('index.php?option=com_workflow&task=logs.reset&item_id=' . (int) $item->item_id . '&extension=' . urlencode($item->extension) . '&' . Session::getFormToken() . '=1')
                                    : '';
                                ?>
                                <tr>
                                    <td><?php echo HTMLHelper::_('date', $item->executed_at, Text::_('DATE_FORMAT_LC5')); ?></td>
                                    <th scope="row">
                                        <?php if ($editLink) : ?>
                                            <a href="<?php echo $editLink; ?>"><?php echo $this->escape($itemLabel); ?></a>
                                        <?php else : ?>
                                            <?php echo $this->escape($itemLabel); ?>
                                        <?php endif; ?>
                                        <?php if ($itemTitle !== '') : ?>
                                            <div class="small"><?php echo Text::sprintf('COM_WORKFLOW_LOGS_ITEM_ID_INLINE', (int) $item->item_id); ?></div>
                                        <?php endif; ?>
                                    </th>
                                    <td>
                                        <?php if (!empty($item->item_category_id)) : ?>
                                            <a href="<?php echo Route::_('index.php?option=com_categories&task=category.edit&id=' . (int) $item->item_category_id . '&extension=' . urlencode((string) $item->item_category_extension)); ?>">
                                                <?php echo $this->escape($item->item_category_title); ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $transitionLabel = $item->transition_title
                                            ? $this->escape(Text::_($item->transition_title))
                                            : (string) (int) $item->transition_id;

                                        $transitionEdit = $user->authorise('core.edit', $parts[0] . '.transition.' . (int) $item->transition_id)
                                            ? Route::_(
                                                'index.php?option=com_workflow&task=transition.edit&id=' . (int) $item->transition_id
                                                    . '&workflow_id=' . (int) $item->workflow_id
                                                    . '&extension=' . $this->escape((string) $item->extension)
                                            )
                                            : '';
                                        ?>
                                        <?php if ($transitionEdit) : ?>
                                            <a href="<?php echo $transitionEdit; ?>"><?php echo $transitionLabel; ?></a>
                                        <?php else : ?>
                                            <?php echo $transitionLabel; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($item->from_stage_id !== $item->to_stage_id) : ?>
                                            <?php echo $item->from_stage ? $this->escape(Text::_($item->from_stage)) : (int) $item->from_stage_id; ?>
                                            <span aria-hidden="true">&#8594;</span>
                                            <?php echo $item->to_stage ? $this->escape(Text::_($item->to_stage)) : (int) $item->to_stage_id; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $item->run_as_name ? $this->escape($item->run_as_name) : Text::_('COM_WORKFLOW_LOGS_RUN_AS_SYSTEM'); ?></td>
                                    <td class="text-center">
                                        <?php if ((int) $item->exit_code === 0) : ?>
                                            <span class="badge bg-success"><?php echo Text::_('COM_WORKFLOW_LOGS_RESULT_OK'); ?></span>
                                        <?php elseif ((int) $item->exit_code === 4) : ?>
                                            <span class="badge bg-secondary"><?php echo Text::_('COM_WORKFLOW_LOGS_RESULT_SKIPPED'); ?></span>
                                        <?php else : ?>
                                            <span class="badge bg-danger"><?php echo Text::_('COM_WORKFLOW_LOGS_RESULT_FAILED'); ?></span>
                                            <?php if ($resetLink) : ?>
                                                <div class="mt-1">
                                                    <a href="<?php echo $resetLink; ?>" class="btn btn-sm btn-secondary"><?php echo Text::_('COM_WORKFLOW_LOGS_RESET'); ?></a>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $item->note ? $this->escape($item->note) : ''; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php echo $this->pagination->getListFooter(); ?>
                <?php endif; ?>

                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
