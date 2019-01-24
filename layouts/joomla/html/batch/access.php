<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Layout variables
 * ---------------------
 * None
 */

?>
<label id="batch-access-lbl" for="batch-access" class="modalTooltip" title="<?php echo JHtml::_('tooltipText', 'JLIB_HTML_BATCH_ACCESS_LABEL', 'JLIB_HTML_BATCH_ACCESS_LABEL_DESC'); ?>">
	<?php echo JText::_('JLIB_HTML_BATCH_ACCESS_LABEL'); ?></label>
	<?php echo JHtml::_(
		'access.assetgrouplist',
		'batch[assetgroup_id]', '',
		'class="inputbox"',
		array(
			'title' => JText::_('JLIB_HTML_BATCH_NOCHANGE'),
			'id' => 'batch-access'
		)
	); ?>
