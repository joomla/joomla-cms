<?php

/**
 * @package       JED
 *
 * @subpackage    Tickets
 *
 * @copyright     (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Jed\Component\Jed\Administrator\Helper\JedHelper;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/src/Helper/');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

// Import CSS
/*$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useStyle('com_jed.admin')
    ->useScript('com_jed.admin');
*/
$user      = JedHelper::getUser();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_jed');
$saveOrder = $listOrder == 'a.`ordering`';

if ($saveOrder) {
    $saveOrderingUrl = 'index.php?option=com_jed&task=jedtickets.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
    HTMLHelper::_('draggablelist.draggable');
}

// $sortFields = $this->getSortFields();
?>

<form action="<?php echo Route::_('index.php?option=com_jed&view=jedtickets'); ?>" method="post"
      name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

                <div class="clearfix"></div>
                <table class="table table-striped" id="jedticketList">
                    <thead>
                    <tr>

                        <th class='left'>
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_JED_JEDTICKETS_FIELD_TICKET_CATEGORY_TYPE_LABEL', 'a.`ticket_category_type`', $listDirn, $listOrder); ?>
                        </th>
                        <th class='left'>
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_JED_JEDTICKETS_FIELD_TICKET_SUBJECT_LABEL', 'a.`ticket_subject`', $listDirn, $listOrder); ?>
                        </th>

                        <th class='left'>
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_JED_GENERAL_FIELD_CREATED_ON_LABEL', 'a.`created_on`', $listDirn, $listOrder); ?>
                        </th>
                        <th class='left'>
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_FIELD_CREATED_BY_LABEL', 'a.`created_by`', $listDirn, $listOrder); ?>
                        </th>

                        <th class='left'>
                            <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.`ticket_status`', $listDirn, $listOrder); ?>
                        </th>
                        <th class='left'>
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_JED_JEDTICKETS_FIELD_ALLOCATED_GROUP_LABEL', 'a.`allocated_group`', $listDirn, $listOrder); ?>
                        </th>
                        <th class='left'>
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_JED_JEDTICKETS_FIELD_ALLOCATED_TO_LABEL', 'a.`allocated_to`', $listDirn, $listOrder); ?>
                        </th>
                        <th class='left'>
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_JED_JEDTICKETS_FIELD_LINKED_ITEM_TYPE_LABEL', 'a.`linked_item_type`', $listDirn, $listOrder); ?>
                        </th>


                        <th class='left'>
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_FIELD_ID_LABEL', 'a.`id`', $listDirn, $listOrder); ?>
                        </th>

                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
                            <?php echo $this->pagination->getListFooter(); ?>
                        </td>
                    </tr>
                    </tfoot>
                    <tbody <?php if ($saveOrder) :
                        ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" <?php
                           endif; ?>>
                    <?php foreach ($this->items as $i => $item) :
                        $ordering = ($listOrder == 'a.ordering');
                        $canCreate = $user->authorise('core.create', 'com_jed');
                        $canEdit = $user->authorise('core.edit', 'com_jed');
                        $canCheckin = $user->authorise('core.manage', 'com_jed');
                        $canChange = $user->authorise('core.edit.state', 'com_jed');
                        ?>
                        <tr class="row<?php echo $i % 2; ?>">


                            <td>

                                <?php echo $item->categorytype_string; ?>
                            </td>
                            <td>
                                <?php if (isset($item->checked_out) && $item->checked_out && ($canEdit || $canChange)) : ?>
                                    <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'jedtickets.', $canCheckin); ?>
                                <?php endif; ?>
                                <?php if ($canEdit) : ?>
                                    <a href="<?php echo Route::_('index.php?option=com_jed&task=jedticket.edit&id=' . (int) $item->id); ?>">
                                        <?php echo $this->escape($item->ticket_subject); ?></a>
                                <?php else : ?>
                                    <?php echo $this->escape($item->ticket_subject); ?>
                                <?php endif; ?>

                            </td>

                            <td>

                                <?php try {
                                    $d = new DateTime($item->created_on);
                                } catch (Exception $e) {
                                }
                                echo $d->format("d M y H:i"); ?>
                            </td>

                            <td>

                                <?php echo $item->created_by; ?>
                            </td>
                            <td>

                                <?php echo $item->ticket_status; ?>
                            </td>


                            <td>

                                <?php echo $item->ticketallocatedgroup_string; ?>
                            </td>
                            <td>

                                <?php echo $item->allocated_to; ?>
                            </td>
                            <td>

                                <?php echo $item->ticketlinkeditemtypes_string; ?>
                            </td>


                            <td>

                                <?php echo $item->id; ?>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <input type="hidden" name="task" value=""/>
                <input type="hidden" name="boxchecked" value="0"/>
                <input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>"/>
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
