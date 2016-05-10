<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Layout variables
 * ---------------------
 * None
 */

?>
<label id="batch-tag-lbl" for="batch-tag-id" class="modalTooltip" title="<?php
echo JHtml::_('tooltipText', 'JLIB_HTML_BATCH_TAG_LABEL', 'JLIB_HTML_BATCH_TAG_LABEL_DESC'); ?>">
<?php echo JText::_('JLIB_HTML_BATCH_TAG_LABEL'); ?>
</label>
<select name="batch[tag]" class="inputbox" id="batch-tag-id">
	<option value=""><?php echo JText::_('JLIB_HTML_BATCH_TAG_NOCHANGE'); ?></option>
	<?php echo JHtml::_('select.options', JHtml::_('tag.tags', array('filter.published' => array(1))), 'value', 'text'); ?>
</select>
