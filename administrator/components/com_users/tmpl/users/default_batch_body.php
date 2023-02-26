<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
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

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('joomla.batch-copymove');

?>

<div class="p-3">
    <form>
        <div class="form-group">
            <label id="batch-choose-action-lbl" class="control-label" for="batch-group-id">
                <?php echo Text::_('COM_USERS_BATCH_GROUP'); ?>
            </label>
            <div id="batch-choose-action" class="combo controls">
                <select class="form-select" name="batch[group_id]" id="batch-group-id">
                    <option value=""><?php echo Text::_('JSELECT'); ?></option>
                    <?php echo HTMLHelper::_('select.options', HTMLHelper::_('user.groups')); ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <fieldset>
                <legend>
                    <?php echo Text::_('COM_USERS_BATCH_ACTIONS'); ?>
                </legend>
                <?php echo HTMLHelper::_('select.radiolist', $options, 'batch[group_action]', '', 'value', 'text', 'add'); ?>
            </fieldset>
        </div>
        <div class="form-group">
            <fieldset id="batch-password-reset_id">
                <legend>
                    <?php echo Text::_('COM_USERS_REQUIRE_PASSWORD_RESET'); ?>
                </legend>
                <?php echo HTMLHelper::_('select.radiolist', $resetOptions, 'batch[reset_id]', '', 'value', 'text', ''); ?>
            </fieldset>
        </div>
    </form>
</div>
