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
<label id="batch-workflowstate-lbl" for="batch-workflowstate-id" class="modalTooltip" title="<?php echo JHtml::_('tooltipText', 'JLIB_HTML_BATCH_WORKFLOW_STATE_LABEL', 'JLIB_HTML_BATCH_WORKFLOW_STATE_LABEL_DESC'); ?>">
	<?php echo JText::_('JLIB_HTML_BATCH_WORKFLOW_STATE_LABEL'); ?>
</label>

<?php

$attr = array(
	'id'        => 'batch-workflowstate-id',
	'group.label' => 'text',
    'group.items' => null
);

$groups = JHtml::_('workflowstate.existing', array('title' => JText::_('JLIB_HTML_BATCH_WORKFLOW_STATE_NOCHANGE')));

echo JHtml::_('select.groupedlist', $groups, 'batch[workflowstate_id]', $attr);
