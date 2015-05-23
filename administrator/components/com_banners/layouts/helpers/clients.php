<?php
/**
 * @package     Joomla.Admin
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

extract($displayData);

JHtml::_('bootstrap.tooltip');

// Create the batch selector to change the client on a selection list.
?>
<label id="batch-client-lbl" for="batch-client" class="hasTooltip" title="<?php echo
JHtml::tooltipText('COM_BANNERS_BATCH_CLIENT_LABEL', 'COM_BANNERS_BATCH_CLIENT_LABEL_DESC'); ?>">
	<?php echo JText::_('COM_BANNERS_BATCH_CLIENT_LABEL'); ?>
</label>
<select name="batch[client_id]" id="batch-client-id">
	<option value=""><?php echo JText::_('COM_BANNERS_BATCH_CLIENT_NOCHANGE'); ?></option>
	<option value="0"><?php echo JText::_('COM_BANNERS_NO_CLIENT'); ?></option>
	<?php echo JHtml::_('select.options', $options, 'value', 'text'); ?>
</select>
