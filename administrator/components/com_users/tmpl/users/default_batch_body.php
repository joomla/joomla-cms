<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// Create the copy/move options.
$options = array(
	HTMLHelper::_('select.option', 'add', Text::_('COM_USERS_BATCH_ADD')),
	HTMLHelper::_('select.option', 'del', Text::_('COM_USERS_BATCH_DELETE')),
	HTMLHelper::_('select.option', 'set', Text::_('COM_USERS_BATCH_SET'))
);

// Create the reset password options.
$resetOptions = array(
	HTMLHelper::_('select.option', '', Text::_('COM_USERS_NO_ACTION')),
	HTMLHelper::_('select.option', 'yes', Text::_('JYES')),
	HTMLHelper::_('select.option', 'no', Text::_('JNO'))
);

?>

<div class="container">
	<form>
		<div class="form-group">
			<label id="batch-choose-action-lbl" class="control-label" for="batch-group-id">
				<?php echo Text::_('COM_USERS_BATCH_GROUP'); ?>
			</label>
			<div id="batch-choose-action" class="combo controls">
                <select class="custom-select" name="batch[group_id]" id="batch-group-id">
                    <option value=""><?php echo Text::_('JSELECT'); ?></option>
                    <?php echo HTMLHelper::_('select.options', HTMLHelper::_('user.groups')); ?>
                </select>
			</div>
		</div>
        <div class="form-group">
            <label><?php echo Text::_('COM_USERS_REQUIRE_PASSWORD_RESET'); ?></label>
            <?php echo HTMLHelper::_('select.radiolist', $resetOptions, 'batch[reset_id]', '', 'value', 'text', ''); ?>
        </div>
	</form>
</div>
