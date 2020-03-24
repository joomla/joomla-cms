<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

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
<label id="batch-user-lbl" for="batch-user">
	<?php echo Text::_('JLIB_HTML_BATCH_USER_LABEL'); ?>
</label>
<select name="batch[user_id]" class="custom-select" id="batch-user-id">
	<option value=""><?php echo Text::_('JLIB_HTML_BATCH_USER_NOCHANGE'); ?></option>
	<?php echo $optionNo; ?>
	<?php echo HTMLHelper::_('select.options', HTMLHelper::_('user.userlist'), 'value', 'text'); ?>
</select>
