<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   boolean  $noUser  Inject an option for no user?
 */

$optionNo = '';

if ($noUser) {
    $optionNo = '<option value="0">' . Text::_('JLIB_HTML_BATCH_USER_NOUSER') . '</option>';
}
?>
<label id="batch-user-lbl" for="batch-user-id">
    <?php echo Text::_('JLIB_HTML_BATCH_USER_LABEL'); ?>
</label>
<select name="batch[user_id]" class="form-select" id="batch-user-id">
    <option value=""><?php echo Text::_('JLIB_HTML_BATCH_USER_NOCHANGE'); ?></option>
    <?php echo $optionNo; ?>
    <?php echo HTMLHelper::_('select.options', HTMLHelper::_('user.userlist'), 'value', 'text'); ?>
</select>
