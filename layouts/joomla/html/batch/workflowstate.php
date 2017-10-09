<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Layout variables
 * ---------------------
 * None
 */

?>
<label id="batch-access-lbl" for="batch-workflowstate-id" class="modalTooltip" title="<?php echo JHtml::_('tooltipText', 'JLIB_HTML_BATCH_ACCESS_LABEL', 'JLIB_HTML_BATCH_ACCESS_LABEL_DESC'); ?>">
	<?php echo JText::_('JLIB_HTML_BATCH_WORKFLOW_STATE_LABEL'); ?>
</label>
<!--<select name="batch[workflowstate_id]" class="custom-select" id="batch-workflowstate">
    <option value=""><?php /*echo JText::_('JLIB_HTML_BATCH_WORKFLOWSTATE_NOCHANGE'); */?></option>
	<?php /*echo JHtml::_('select.options', JHtml::_('workflowstate.existing'), 'value', 'text'); */?>
</select>-->


<?php

$attr = array(
	'id'        => 'batch-workflowstate-id'
);

$vaaa = JHtml::_('workflowstate.existing');

echo JHtml::_('select.groupedlist', $vaaa, 'batch[workflowstate_id]',''); ?>