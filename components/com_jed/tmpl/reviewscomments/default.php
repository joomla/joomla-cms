<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Jed\Component\Jed\Site\Helper\JedHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$user       = JedHelper::getUser();
$userId     = $user->get('id');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_jed');
$canEdit    = $user->authorise('core.edit', 'com_jed');
$canCheckin = $user->authorise('core.manage', 'com_jed');
$canChange  = $user->authorise('core.edit.state', 'com_jed');
$canDelete  = $user->authorise('core.delete', 'com_jed');

// Import CSS
try {
    $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
} catch (Exception $e) {
}
$wa->useStyle('com_jed.list');
?>

<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
      name="adminForm" id="adminForm">

    <div class="table-responsive">
        <table class="table table-striped" id="reviewcommentList">
            <thead>
            <tr>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_JED_REVIEWCOMMENTS_FIELD_ID_LABEL', 'a.id', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_JED_REVIEWCOMMENTS_FIELD_REVIEW_ID_LABEL', 'a.review_id', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_JED_REVIEWCOMMENTS_FIELD_IP_ADDRESS_LABEL', 'a.ip_address', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_JED_GENERAL_FIELD_CREATED_ON_LABEL', 'a.created_on', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'JGLOBAL_FIELD_CREATED_BY_LABEL', 'a.created_by', $listDirn, $listOrder); ?>
                    </th>

                    <th >
                        <?php echo HTMLHelper::_('grid.sort', 'JPUBLISHED', 'a.state', $listDirn, $listOrder); ?>
                    </th>

                        <?php if ($canEdit || $canDelete) : ?>
                    <th class="center">
                            <?php echo Text::_('COM_JED_REVIEWCOMMENTS_FIELD_ACTIONS_LABEL'); ?>
                    </th>
                        <?php endif; ?>

            </tr>
            </thead>
            <tfoot>
            <tr>
                <td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
            </tfoot>
            <tbody>
            <?php foreach ($this->items as $i => $item) : ?>
                <?php $canEdit = $user->authorise('core.edit', 'com_jed'); ?>
                <?php if (!$canEdit && $user->authorise('core.edit.own', 'com_jed')) : ?>
                    <?php $canEdit = Factory::getUser()->id == $item->created_by; ?>
                <?php endif; ?>

                <tr class="row<?php echo $i % 2; ?>">

                    <td>
                        <?php echo $item->id; ?>
                    </td>
                    <td>
                        <?php echo $item->review_id; ?>
                    </td>
                    <td>
                        <?php $canCheckin = Factory::getUser()->authorise('core.manage', 'com_jed.' . $item->id) || $item->checked_out == Factory::getUser()->id; ?>
                        <?php if ($canCheckin && $item->checked_out > 0) : ?>
                            <a href="<?php echo Route::_('index.php?option=com_jed&task=reviewcomment.checkin&id=' . $item->id . '&' . Session::getFormToken() . '=1'); ?>">
                            <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'reviewcomment.', false); ?></a>
                        <?php endif; ?>
                        <a href="<?php echo Route::_('index.php?option=com_jed&view=reviewcomment&id=' . (int) $item->id); ?>">
                            <?php echo $this->escape($item->ip_address); ?></a>
                    </td>
                    <td>
                        <?php echo $item->created_on; ?>
                    </td>
                    <td>
                                <?php echo Factory::getUser($item->created_by)->name; ?>
                    </td>
                    <td>
                        <?php $class = ($canChange) ? 'active' : 'disabled'; ?>
                        <a class="btn btn-micro <?php echo $class; ?>" href="<?php echo ($canChange) ? Route::_('index.php?option=com_jed&task=reviewcomment.publish&id=' . $item->id . '&state=' . (($item->state + 1) % 2), false, 2) : '#'; ?>">
                        <?php if ($item->state == 1) : ?>
                            <i class="icon-publish"></i>
                        <?php else : ?>
                            <i class="icon-unpublish"></i>
                        <?php endif; ?>
                        </a>
                    </td>
                    <?php if ($canEdit || $canDelete) : ?>
                        <td class="center">
                            <?php if ($canEdit) : ?>
                                <a href="<?php echo Route::_('index.php?option=com_jed&task=reviewcomment.edit&id=' . $item->id, false, 2); ?>" class="btn btn-mini" type="button"><i class="icon-edit" ></i></a>
                            <?php endif; ?>
                            <?php if ($canDelete) : ?>
                                <a href="<?php echo Route::_('index.php?option=com_jed&task=reviewcommentform.remove&id=' . $item->id, false, 2); ?>" class="btn btn-mini delete-button" type="button"><i class="icon-trash" ></i></a>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>

                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($canCreate) : ?>
        <a href="<?php echo Route::_('index.php?option=com_jed&task=reviewcommentform.edit&id=0', false, 0); ?>"
           class="btn btn-success btn-small"><i
                class="icon-plus"></i>
            <?php echo Text::_('JGLOBAL_FIELD_ADD'); ?></a>
    <?php endif; ?>

    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value=""/>
    <input type="hidden" name="filter_order_Dir" value=""/>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<?php
if ($canDelete) {
    $wa->addInlineScript("
			jQuery(document).ready(function () {
				jQuery('.delete-button').click(deleteItem);
			});

			function deleteItem() {

				if (!confirm(\"" . Text::_('COM_JED_DELETE_MESSAGE') . "\")) {
					return false;
				}
			}
		", [], [], ["jquery"]);
}
?>
