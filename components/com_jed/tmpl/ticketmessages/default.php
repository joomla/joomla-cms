<?php

/**
 * @package       JED
 *
 * @subpackage    TICKETS
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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
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
try {
    $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
} catch (Exception $e) {
}
$wa->useStyle('com_jed.list');
?>

<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm">

    <?php if (!empty($this->filterForm)) {
        echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
    } ?>
    <div class="table-responsive">
        <table class="table table-striped" id="ticketmessageList">
            <thead>
                <tr>
                    <?php if (isset($this->items[0]->state)) : ?>
                        <th width="5%">
                            <?php echo HTMLHelper::_('grid.sort', 'JPUBLISHED', 'a.state', $listDirn, $listOrder); ?>
                        </th>
                    <?php endif; ?>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'JGLOBAL_FIELD_ID_LABEL', 'a.id', $listDirn, $listOrder); ?>
                    </th>
                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_JED_TICKETMESSAGE_FIELD_SUBJECT_LABEL', 'a.subject', $listDirn, $listOrder); ?>
                    </th>
                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_JED_TICKETMESSAGE_FIELD_TICKET_ID_LABEL', 'a.ticket_id', $listDirn, $listOrder); ?>
                    </th>


                    <?php if ($canEdit || $canDelete) : ?>
                        <th class="center">
                            <?php echo Text::_('COM_JED_TICKETMESSAGES_ACTIONS'); ?>
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
                        <?php $canEdit = JedHelper::getUser()->id == $item->created_by; ?>
                    <?php endif; ?>

                    <tr class="row<?php echo $i % 2; ?>">

                        <?php if (isset($this->items[0]->state)) : ?>
                            <?php $class = ($canChange) ? 'active' : 'disabled'; ?>
                            <td class="center">
                            <a class="btn btn-micro <?php echo $class; ?>"
                               href="<?php echo ($canChange) ? Route::_('index.php?option=com_jed&task=ticketmessage.publish&id=' . $item->id . '&state=' . (($item->state + 1) % 2), false, 2) : '#'; ?>">
                                    <?php if ($item->state == 1) : ?>
                                        <i class="icon-publish"></i>
                                    <?php else : ?>
                                        <i class="icon-unpublish"></i>
                                    <?php endif; ?>
                                </a>
                            </td>
                        <?php endif; ?>

                        <td>

                            <?php echo $item->id; ?>
                        </td>
                        <td>
                            <?php $canCheckin = JedHelper::getUser()->authorise('core.manage', 'com_jed.' . $item->id) || $item->checked_out == JedHelper::getUser()->id; ?>
                            <?php if ($canCheckin && $item->checked_out > 0) : ?>
                                <a href="<?php echo Route::_('index.php?option=com_jed&task=ticketmessage.checkin&id=' . $item->id . '&' . Session::getFormToken() . '=1'); ?>"> <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'ticketmessage.', false); ?></a>
                            <?php endif; ?>
                            <a href="<?php echo Route::_('index.php?option=com_jed&view=ticketmessage&id=' . (int) $item->id); ?>">
                                <?php echo $this->escape($item->subject); ?></a>
                        </td>
                        <td>

                            <?php echo $item->ticket_id; ?>
                        </td>


                        <?php if ($canEdit || $canDelete) : ?>
                            <td class="center">
                                <?php $canCheckin = JedHelper::getUser()->authorise('core.manage', 'com_jed.' . $item->id) || $this->item->checked_out == JedHelper::getUser()->id; ?>
                            <?php if ($canEdit && $item->checked_out == 0) :
                                ?>                            <a
                                    href="<?php echo Route::_('index.php?option=com_jed&task=ticketmessage.edit&id=' . $item->id, false, 2); ?>"
                                    class="btn btn-mini" type="button"><i class="icon-edit"></i></a>
                            <?php endif; ?>
                                <?php if ($canDelete) : ?>
                                <a href="<?php echo Route::_('index.php?option=com_jed&task=ticketmessageform.remove&id=' . $item->id, false, 2); ?>"
                                   class="btn btn-mini delete-button" type="button"><i class="icon-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($canCreate) : ?>
        <a href="<?php echo Route::_('index.php?option=com_jed&task=ticketmessageform.edit&id=0', false, 0); ?>" class="btn btn-success btn-small"><i class="icon-plus"></i>
            <?php echo Text::_('COM_JED_GENERAL_ADD_ITEM_LABEL'); ?></a>
    <?php endif; ?>

    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
