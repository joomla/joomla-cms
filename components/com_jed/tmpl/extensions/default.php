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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Jed\Component\Jed\Administrator\Helper\JedHelper;

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

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useStyle('com_jed.jazstyle');
?>

<div class="jed-cards-wrapper margin-bottom-half">
    <div class="jed-container">
        <h2 class="heading heading--m"><?php echo $this->items[0]->category_title; ?> Extensions</h2>
        <p class="font-size-s"><?php echo $this->items[0]->category_hierarchy; ?></p>
        <ul class="jed-grid jed-grid--1-1-1">
            <?php foreach ($this->items as $item) : ?>
                <?php echo LayoutHelper::render('cards.extension', [
                    'image'         => $item->logo,
                    'title'         => $item->title,
                    'developer'     => $item->developer,
                    'score_string'  => $item->score_string,
                    'score'         => $item->score,
                    'reviews'       => $item->review_string,
                    'compatibility' => $item->version,
                    'description'   => $item->description,
                    'type'          => $item->type,
                    'category'      => $item->category_title,
                    'link'          => Route::_(sprintf('index.php?option=com_jed&view=extension&catid=%s&id=%s', $item->primary_category_id, $item->id))
                ]); ?>
            <?php endforeach; ?>
        </ul>
    </div>
</div>


<?php echo $this->pagination->getPaginationLinks(); ?>
<!--Hide for now-->
<?php /*<div style="display: none;">
    <form action="<?php echo Route::_('index.php?option=com_jed&view=extensions'); ?>" id="extensionForm" name="extensionForm" method="post">
        <?php echo $this->filterForm->renderFieldset('filter'); ?>
        <button type="submit"><?php echo Text::_('COM_JED_FORM_SEARCH'); ?></button>
        <button type="button" class="js-extensionsForm-button-reset"><?php echo Text::_('COM_JED_FORM_RESET'); ?></button>
    </form>
</div>

<?php /*
<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
      name="adminForm" id="adminForm">

    <div class="table-responsive">
        <table class="table table-striped" id="extensionList">
            <thead>
            <tr>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort',  'JGLOBAL_FIELD_ID_LABEL', 'a.id', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort',  'COM_JED_EXTENSIONS_TITLE', 'a.title', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort',  'JALIAS', 'a.alias', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort',  'COM_JED_EXTENSIONS_JOOMLA_VERSIONS', 'a.joomla_versions', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort',  'COM_JED_EXTENSIONS_POPULAR', 'a.popular', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort',  'COM_JED_EXTENSIONS_REQUIRES_REGISTRATION', 'a.requires_registration', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort',  'COM_JED_EXTENSIONS_GPL_LICENSE_TYPE', 'a.gpl_license_type', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort',  'COM_JED_EXTENSIONS_CAN_UPDATE', 'a.can_update', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort',  'COM_JED_EXTENSIONS_INCLUDES', 'a.includes', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort',  'COM_JED_EXTENSIONS_PRIMARY_CATEGORY_ID', 'a.primary_category_id', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort',  'COM_JED_EXTENSIONS_APPROVED', 'a.approved', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort',  'COM_JED_EXTENSIONS_APPROVED_TIME', 'a.approved_time', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort',  'JPUBLISHED', 'a.published', $listDirn, $listOrder); ?>
                    </th>

                    <th >
                        <?php echo HTMLHelper::_('grid.sort', 'JPUBLISHED', 'a.state', $listDirn, $listOrder); ?>
                    </th>

                        <?php if ($canEdit || $canDelete): ?>
                    <th class="center">
                        <?php echo Text::_('COM_JED_EXTENSIONS_ACTIONS'); ?>
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
                <?php if (!$canEdit && $user->authorise('core.edit.own', 'com_jed')): ?>
                <?php $canEdit = JedHelper::getUser()->id == $item->created_by; ?>
                <?php endif; ?>

                <tr class="row<?php echo $i % 2; ?>">

                    <td>
                        <?php echo $item->id; ?>
                    </td>
                    <td>
                        <?php $canCheckin = JedHelper::getUser()->authorise('core.manage', 'com_jed.' . $item->id) || $item->checked_out == JedHelper::getUser()->id; ?>
                        <?php if($canCheckin && $item->checked_out > 0) : ?>
                            <a href="<?php echo Route::_('index.php?option=com_jed&task=extension.checkin&id=' . $item->id .'&'. Session::getFormToken() .'=1'); ?>">
                            <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'extension.', false); ?></a>
                        <?php endif; ?>
                        <a href="<?php echo Route::_('index.php?option=com_jed&view=extension&id='.(int) $item->id . '&catid=' . (int) $item->primary_category_id); ?>">
                            <?php echo $this->escape($item->title); ?></a>
                    </td>
                    <td>
                        <?php echo $item->alias; ?>
                    </td>
                    <td>
                        <?php echo $item->joomla_versions; ?>
                    </td>
                    <td>
                        <?php echo $item->popular; ?>
                    </td>
                    <td>
                        <?php echo $item->requires_registration; ?>
                    </td>
                    <td>
                        <?php echo $item->gpl_license_type; ?>
                    </td>
                    <td>
                        <?php echo $item->can_update; ?>
                    </td>
                    <td>
                        <?php echo $item->includes; ?>
                    </td>
                    <td>
                        <?php echo $item->primary_category_id_name; ?>
                    </td>
                    <td>
                        <?php echo $item->approved; ?>
                    </td>
                    <td>
                        <?php echo $item->approved_time; ?>
                    </td>
                    <td>
                        <?php echo $item->published; ?>
                    </td>
                    <td>
                        <?php $class = ($canChange) ? 'active' : 'disabled'; ?>
                        <a class="btn btn-micro <?php echo $class; ?>" href="<?php echo ($canChange) ? Route::_('index.php?option=com_jed&task=extension.publish&id=' . $item->id . '&state=' . (($item->state + 1) % 2), false, 2) : '#'; ?>">
                        <?php if ($item->state == 1): ?>
                            <i class="icon-publish"></i>
                        <?php else: ?>
                            <i class="icon-unpublish"></i>
                        <?php endif; ?>
                        </a>
                    </td>
                    <?php if ($canEdit || $canDelete): ?>
                        <td class="center">
                            <?php $canCheckin = JedHelper::getUser()->authorise('core.manage', 'com_jed.' . $item->id) || $item->checked_out == JedHelper::getUser()->id; ?>

                            <?php if($canEdit && $item->checked_out == 0): ?>
                                <a href="<?php echo Route::_('index.php?option=com_jed&task=extension.edit&id=' . $item->id, false, 2); ?>" class="btn btn-mini" type="button"><i class="icon-edit" ></i></a>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                                <a href="<?php echo Route::_('index.php?option=com_jed&task=extensionform.remove&id=' . $item->id, false, 2); ?>" class="btn btn-mini delete-button" type="button"><i class="icon-trash" ></i></a>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>

                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($canCreate) : ?>
        <a href="<?php echo Route::_('index.php?option=com_jed&task=extensionform.edit&id=0', false, 0); ?>"
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
    if($canDelete) {
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
*/ ?>
