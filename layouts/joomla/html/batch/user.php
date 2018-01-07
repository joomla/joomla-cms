<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Layout variables
 * ---------------------
 *
 * @var  boolean   $noUser Inject an option for no user?
 */

extract($displayData);

$optionNo = '';

if ($noUser)
{
	$optionNo = '<option value="0">' . Text::_('JLIB_HTML_BATCH_USER_NOUSER') . '</option>';
}
?>
<label id="batch-user-lbl" for="batch-user" class="modalTooltip" title="<?php
echo HTMLHelper::_('tooltipText', 'JLIB_HTML_BATCH_USER_LABEL', 'JLIB_HTML_BATCH_USER_LABEL_DESC'); ?>">
	<?php echo Text::_('JLIB_HTML_BATCH_USER_LABEL'); ?>
</label>
<select name="batch[user_id]" class="custom-select" id="batch-user-id">
	<option value=""><?php echo Text::_('JLIB_HTML_BATCH_USER_NOCHANGE'); ?></option>
	<?php echo $optionNo; ?>
	<?php echo HTMLHelper::_('select.options', HTMLHelper::_('user.userlist'), 'value', 'text'); ?>
</select>
