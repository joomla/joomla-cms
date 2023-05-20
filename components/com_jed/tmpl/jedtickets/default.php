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

use Jed\Component\Jed\Site\Helper\JedHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$user        = JedHelper::getUser();
$userId      = $user->get('id');
$listOrder   = $this->state->get('list.ordering');
$listDirn    = $this->state->get('list.direction');
$isLoggedIn  = JedHelper::IsLoggedIn();
$redirectURL = JedHelper::getLoginlink();

$canCreate = $isLoggedIn;
$canEdit   = $isLoggedIn;


// Import CSS
//$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
//$wa->useStyle('com_jed.list');
if (!$isLoggedIn) {
    try {
        $app = Factory::getApplication();
    } catch (Exception $e) {
        throw new Exception($e->getMessage(), $e->getCode());
    }

    $app->enqueueMessage(Text::_('COM_JED_JEDTICKETS_NO_ACCESS'), 'success');
    $app->redirect($redirectURL);
} else {
    ?>

    <form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
          name="adminForm" id="adminForm">
        <?php echo '<fieldset class="mytickets"><legend>' . Text::_('COM_JED_JEDTICKETS_LIST_HEADER') . '</legend>' . Text::_('COM_JED_JEDTICKETS_LIST_DESCR') . '</fieldset>'; ?>
        <?php if (!empty($this->filterForm)) {
            echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
        } ?>
        <div class="table-responsive">
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
                        <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.`ticket_status`', $listDirn, $listOrder); ?>
                    </th>
                    <th class='left'>
                        <?php echo HTMLHelper::_('searchtools.sort', 'COM_JED_JEDTICKETS_FIELD_ALLOCATED_GROUP_LABEL', 'a.`allocated_group`', $listDirn, $listOrder); ?>
                    </th>


                    <?php if ($canEdit) : ?>
                        <th class="center">
                            <?php echo Text::_('COM_JED_JEDTICKETS_ACTIONS'); ?>
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
                    <?php $canEdit = JedHelper::getUser()->id == $item->created_by; ?>

                    <tr class="row<?php echo $i % 2; ?>">


                        <td>

                            <?php echo $item->categorytype_string; ?>
                        </td>
                        <td>

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
                                echo $d->format("d M y H:i");
                            } catch (Exception $e) {
                            }
                            ?>
                        </td>


                        <td>

                            <?php echo $item->ticket_status; ?>
                        </td>


                        <td>

                            <?php echo $item->ticketallocatedgroup_string; ?>
                        </td>


                        <?php if ($canEdit) : ?>
                            <td class="center">
                                <a
                                        href="<?php echo Route::_('index.php?option=com_jed&task=jedticket.edit&id=' . $item->id, false, 2); ?>"
                                        class="btn btn-mini" type="button"><i class="icon-edit"></i></a>


                            </td>
                        <?php endif; ?>

                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($canCreate) : ?>
            <a href="<?php echo Route::_('index.php?option=com_jed&task=jedticketform.edit&id=0', false, 0); ?>"
               class="btn btn-success btn-small"><i
                        class="icon-plus"></i>
                <?php echo Text::_('COM_JED_GENERAL_ADD_ITEM_LABEL'); ?></a>
        <?php endif; ?>

        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>

    <?php
}
?>
