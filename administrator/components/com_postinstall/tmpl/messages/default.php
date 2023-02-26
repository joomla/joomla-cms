<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_postinstall
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$adminFormClass = count($this->extension_options) > 1 ? 'form-inline mb-3' : 'visually-hidden';
?>

<form action="index.php" method="post" name="adminForm" class="<?php echo $adminFormClass; ?>" id="adminForm">
    <input type="hidden" name="option" value="com_postinstall">
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
    <label for="eid" class="me-sm-2"><?php echo Text::_('COM_POSTINSTALL_MESSAGES_FOR'); ?></label>
    <?php echo HTMLHelper::_('select.genericlist', $this->extension_options, 'eid', array('onchange' => 'this.form.submit()', 'class' => 'form-select'), 'value', 'text', $this->eid, 'eid'); ?>
</form>

<?php foreach ($this->items as $item) : ?>
    <?php if ($item->enabled === 1) : ?>
        <div class="card card-outline-secondary mb-3">
            <div class="card-body">
                <h3><?php echo Text::_($item->title_key); ?></h3>
                <p class="small">
                    <?php echo Text::sprintf('COM_POSTINSTALL_LBL_SINCEVERSION', $item->version_introduced); ?>
                </p>
                <div>
                    <?php echo Text::_($item->description_key); ?>
                    <?php if ($item->type !== 'message') : ?>
                    <a href="<?php echo Route::_('index.php?option=com_postinstall&view=messages&task=message.action&id=' . $item->postinstall_message_id . '&' . $this->token . '=1'); ?>" class="btn btn-primary">
                        <?php echo Text::_($item->action_key); ?>
                    </a>
                    <?php endif; ?>
                    <?php if (Factory::getApplication()->getIdentity()->authorise('core.edit.state', 'com_postinstall')) : ?>
                    <a href="<?php echo Route::_('index.php?option=com_postinstall&view=messages&task=message.unpublish&id=' . $item->postinstall_message_id . '&' . $this->token . '=1'); ?>" class="btn btn-danger btn-sm">
                        <?php echo Text::_('COM_POSTINSTALL_BTN_HIDE'); ?>
                    </a>
                    <a href="<?php echo Route::_('index.php?option=com_postinstall&view=messages&task=message.archive&id=' . $item->postinstall_message_id . '&' . $this->token . '=1'); ?>" class="btn btn-danger btn-sm">
                        <?php echo Text::_('COM_POSTINSTALL_BTN_ARCHIVE'); ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php elseif ($item->enabled === 2) : ?>
        <div class="card card-outline-secondary mb-3">
            <div class="card-body">
                <h3><?php echo Text::_($item->title_key); ?></h3>
                <div>
                    <?php if (Factory::getApplication()->getIdentity()->authorise('core.edit.state', 'com_postinstall')) : ?>
                        <a href="<?php echo Route::_('index.php?option=com_postinstall&view=messages&task=message.unpublish&id=' . $item->postinstall_message_id . '&' . $this->token . '=1'); ?>" class="btn btn-danger btn-sm">
                            <?php echo Text::_('COM_POSTINSTALL_BTN_HIDE'); ?>
                        </a>
                        <a href="<?php echo Route::_('index.php?option=com_postinstall&view=messages&task=message.republish&id=' . $item->postinstall_message_id . '&' . $this->token . '=1'); ?>" class="btn btn-success btn-sm">
                            <?php echo Text::_('COM_POSTINSTALL_BTN_REPUBLISH'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
