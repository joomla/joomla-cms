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
use Joomla\CMS\Language\Text;

$canEdit = Factory::getUser()->authorise('core.edit', 'com_jed');

if (!$canEdit && Factory::getUser()->authorise('core.edit.own', 'com_jed')) {
    $canEdit = Factory::getUser()->id == $this->item->created_by;
}
?>

<div class="item_fields">

    <table class="table">


        <tr>
            <th><?php echo Text::_('COM_JED_REVIEWCOMMENTS_FIELD_ID_LABEL'); ?></th>
            <td><?php echo $this->item->id; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_JED_REVIEWCOMMENTS_FIELD_REVIEW_ID_LABEL'); ?></th>
            <td><?php echo $this->item->review_id; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_JED_REVIEWCOMMENTS_FIELD_IP_ADDRESS_LABEL'); ?></th>
            <td><?php echo $this->item->ip_address; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_JED_GENERAL_FIELD_CREATED_ON_LABEL'); ?></th>
            <td><?php echo $this->item->created_on; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('JGLOBAL_FIELD_CREATED_BY_LABEL'); ?></th>
            <td><?php echo $this->item->created_by_name; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_JED_REVIEWCOMMENTS_FIELD_STATE_LABEL'); ?></th>
            <td>
            <i class="icon-<?php echo ($this->item->state == 1) ? 'publish' : 'unpublish'; ?>"></i></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_JED_REVIEWCOMMENTS_FIELD_COMMENTS_LABEL'); ?></th>
            <td><?php echo nl2br($this->item->comments); ?></td>
        </tr>

    </table>

</div>

<?php if ($canEdit) : ?>
    <a class="btn btn-outline-primary" href="<?php echo Route::_('index.php?option=com_jed&task=reviewcomment.edit&id=' . $this->item->id); ?>"><?php echo Text::_("JACTION_EDIT"); ?></a>

<?php endif; ?>

<?php if (Factory::getUser()->authorise('core.delete', 'com_jed.reviewcomment.' . $this->item->id)) : ?>
    <a class="btn btn-danger" rel="noopener noreferrer" href="#deleteModal" role="button" data-bs-toggle="modal">
        <?php echo Text::_("JACTION_DELETE"); ?>
    </a>

    <?php echo HTMLHelper::_(
        'bootstrap.renderModal',
        'deleteModal',
        array(
                                        'title'  => Text::_('JACTION_DELETE'),
                                        'height' => '50%',
                                        'width'  => '20%',

                                        'modalWidth'  => '50',
                                        'bodyHeight'  => '100',
                                        'footer' => '<button class="btn btn-outline-primary" data-bs-dismiss="modal">Close</button><a href="' . Route::_('index.php?option=com_jed&task=reviewcomment.remove&id=' . $this->item->id, false, 2) . '" class="btn btn-danger">' . Text::_('JACTION_DELETE') . '</a>'
                                    ),
        Text::sprintf('COM_JED_DELETE_CONFIRM', $this->item->id)
    ); ?>

<?php endif; ?>
