<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

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
	$optionNo = '<option value="0">' . JText::_('JLIB_HTML_BATCH_USER_NOUSER') . '</option>';
}
?>
<label id="batch-user-lbl" for="batch-user" class="modalTooltip" title="<?php
echo JHtml::_('tooltipText', 'JLIB_HTML_BATCH_USER_LABEL', 'JLIB_HTML_BATCH_USER_LABEL_DESC'); ?>">
	<?php echo JText::_('JLIB_HTML_BATCH_USER_LABEL'); ?>
</label>
<select name="batch[user_id]" class="inputbox" id="batch-user-id">
	<option value=""><?php echo JText::_('JLIB_HTML_BATCH_USER_NOCHANGE'); ?></option>
	<?php echo $optionNo; ?>
	<?php echo JHtml::_('select.options', JHtml::_('user.userlist'), 'value', 'text'); ?>
</select>
