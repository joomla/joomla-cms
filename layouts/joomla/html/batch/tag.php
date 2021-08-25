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
