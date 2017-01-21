<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

JHtml::_('script', 'com_associations/sidebyside.js', false, true);
JHtml::_('stylesheet', 'com_associations/sidebyside.css', array(), true);

$options = array(
			'layout'   => $this->app->input->get('layout', '', 'string'),
			'itemtype' => $this->itemtype,
			'id'       => $this->referenceId,
		);
?>
<button id="toogle-left-panel" class="btn btn-small" 
		data-show-reference="<?php echo JText::_('COM_ASSOCIATIONS_EDIT_SHOW_REFERENCE'); ?>"
		data-hide-reference="<?php echo JText::_('COM_ASSOCIATIONS_EDIT_HIDE_REFERENCE'); ?>"><?php echo JText::_('COM_ASSOCIATIONS_EDIT_HIDE_REFERENCE'); ?>
</button>

<form action="<?php echo JRoute::_('index.php?option=com_associations&view=association&' . http_build_query($options)); ?>" method="post" name="adminForm" id="adminForm" data-associatedview="<?php echo $this->typeName; ?>">
	<div class="sidebyside">
		<div class="outer-panel" id="left-panel">
			<div class="inner-panel">
				<h3><?php echo JText::_('COM_ASSOCIATIONS_REFERENCE_ITEM'); ?></h3>
				<iframe id="reference-association" name="reference-association"
					src="<?php echo JRoute::_($this->editUri . '&task=' . $this->typeName . '.edit&id=' . (int) $this->referenceId); ?>"
					height="400" width="400"
					data-action="edit"
					data-item="<?php echo $this->typeName; ?>"
					data-id="<?php echo $this->referenceId; ?>"
					data-language="<?php echo $this->referenceLanguage; ?>"
					data-editurl="<?php echo JRoute::_($this->editUri); ?>">
				</iframe>
			</div>
		</div>
		<div class="outer-panel" id="right-panel">
			<div class="inner-panel">
				<div class="language-selector">
					<h3 class="target-text"><?php echo JText::_('COM_ASSOCIATIONS_ASSOCIATED_ITEM'); ?></h3>
					<?php echo $this->form->getInput('modalassociation'); ?>
					<?php echo $this->form->getInput('itemlanguage'); ?>
				</div>
				<iframe id="target-association" name="target-association"
					src="<?php echo $this->defaultTargetSrc; ?>"
					height="400" width="400"
					data-action="<?php echo $this->targetAction; ?>"
					data-item="<?php echo $this->typeName; ?>"
					data-id="<?php echo $this->targetId; ?>"
					data-language="<?php echo $this->targetLanguage; ?>"
					data-editurl="<?php echo JRoute::_($this->editUri); ?>">
				</iframe>
			</div>
		</div>

	</div>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="target-id" id="target-id" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
