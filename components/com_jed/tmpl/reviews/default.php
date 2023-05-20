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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Session\Session;
use Jed\Component\Jed\Site\Helper\JedHelper;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
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
    <?php if (!empty($this->filterForm)) {
        echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
    } ?>
    <div class="table-responsive">
        <table class="table table-striped" id="reviewList">
            <thead>
            <tr>
                
                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_JED_REVIEWS_FIELD_ID_LABEL', 'a.id', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_JED_REVIEWS_FIELD_EXTENSION_ID_LABEL', 'a.extension_id', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_JED_REVIEWS_FIELD_SUPPLY_OPTION_ID_LABEL', 'a.supply_option_id', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_JED_REVIEWS_FIELD_TITLE_LABEL', 'a.title', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'JALIAS', 'a.alias', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_JED_REVIEWS_FIELD_BODY_LABEL', 'a.body', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_JED_REVIEWS_FIELD_FUNCTIONALITY_LABEL', 'a.functionality', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_JED_REVIEWS_FIELD_EASE_OF_USE_LABEL', 'a.ease_of_use', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_JED_REVIEWS_FIELD_SUPPORT_LABEL', 'a.support', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_JED_REVIEWS_FIELD_SUPPORT_LABEL_COMMENT', 'a.support_comment', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_JED_REVIEWS_FIELD_DOCUMENTATION_LABEL', 'a.documentation', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_JED_REVIEWS_FIELD_DOCUMENTATION_LABEL_COMMENT', 'a.documentation_comment', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_JED_REVIEWS_FIELD_VALUE_FOR_MONEY_LABEL', 'a.value_for_money', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_JED_REVIEWS_FIELD_VALUE_FOR_MONEY_LABEL_COMMENT', 'a.value_for_money_comment', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_JED_REVIEWS_FIELD_OVERALL_SCORE_LABEL', 'a.overall_score', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_JED_REVIEWS_FIELD_USED_FOR_LABEL', 'a.used_for', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_JED_REVIEWS_FIELD_FLAGGED_LABEL', 'a.flagged', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_JED_REVIEWS_FIELD_IP_ADDRESS_LABEL', 'a.ip_address', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'JPUBLISHED', 'a.published', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_JED_GENERAL_FIELD_CREATED_ON_LABEL', 'a.created_on', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'JGLOBAL_FIELD_CREATED_BY_LABEL', 'a.created_by', $listDirn, $listOrder); ?>
                    </th>

                        <?php if ($canEdit || $canDelete) : ?>
                    <th class="center">
                            <?php echo Text::_('COM_JED_REVIEWS_ACTIONS'); ?>
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
                        <?php echo $item->extension_id; ?>
                    </td>
                    <td>
                        <?php echo $item->supply_option_id; ?>
                    </td>
                    <td>
                        <?php $canCheckin = Factory::getUser()->authorise('core.manage', 'com_jed.' . $item->id) || $item->checked_out == Factory::getUser()->id; ?>
                        <?php if ($canCheckin && $item->checked_out > 0) : ?>
                            <a href="<?php echo Route::_('index.php?option=com_jed&task=review.checkin&id=' . $item->id . '&' . Session::getFormToken() . '=1'); ?>">
                            <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'review.', false); ?></a>
                        <?php endif; ?>
                        <a href="<?php echo Route::_('index.php?option=com_jed&view=review&id=' . (int) $item->id); ?>">
                            <?php echo $this->escape($item->title); ?></a>
                    </td>
                    <td>
                        <?php echo $item->alias; ?>
                    </td>
                    <td>
                        <?php echo $item->body; ?>
                    </td>
                    <td>
                        <?php echo $item->functionality; ?>
                    </td>
                    <td>
                        <?php echo $item->ease_of_use; ?>
                    </td>
                    <td>
                        <?php echo $item->support; ?>
                    </td>
                    <td>
                        <?php echo $item->support_comment; ?>
                    </td>
                    <td>
                        <?php echo $item->documentation; ?>
                    </td>
                    <td>
                        <?php echo $item->documentation_comment; ?>
                    </td>
                    <td>
                        <?php echo $item->value_for_money; ?>
                    </td>
                    <td>
                        <?php echo $item->value_for_money_comment; ?>
                    </td>
                    <td>
                        <?php echo $item->overall_score; ?>
                    </td>
                    <td>
                        <?php echo $item->used_for; ?>
                    </td>
                    <td>
                        <?php echo $item->flagged; ?>
                    </td>
                    <td>
                        <?php echo $item->ip_address; ?>
                    </td>
                    <td>
                        <?php echo $item->published; ?>
                    </td>
                    <td>
                        <?php echo $item->created_on; ?>
                    </td>
                    <td>
                                <?php echo Factory::getUser($item->created_by)->name; ?>
                    </td>
                    <?php if ($canEdit || $canDelete) : ?>
                        <td class="center">
                            <?php $canCheckin = Factory::getUser()->authorise('core.manage', 'com_jed.' . $item->id) || $item->checked_out == Factory::getUser()->id; ?>

                            <?php if ($canEdit && $item->checked_out == 0) : ?>
                                <a href="<?php echo Route::_('index.php?option=com_jed&task=review.edit&id=' . $item->id, false, 2); ?>" class="btn btn-mini" type="button"><i class="icon-edit" ></i></a>
                            <?php endif; ?>
                            <?php if ($canDelete) : ?>
                                <a href="<?php echo Route::_('index.php?option=com_jed&task=reviewform.remove&id=' . $item->id, false, 2); ?>" class="btn btn-mini delete-button" type="button"><i class="icon-trash" ></i></a>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>

                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($canCreate) : ?>
        <a href="<?php echo Route::_('index.php?option=com_jed&task=reviewform.edit&id=0', false, 0); ?>"
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
