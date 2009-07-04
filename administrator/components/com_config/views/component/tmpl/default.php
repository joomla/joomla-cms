<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');

JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
?>
<form action="index.php" method="post" name="adminForm" autocomplete="off">
	<fieldset>
		<div style="float: right">
			<button type="button" onclick="Joomla.submitform('save', this.form);window.top.setTimeout('window.parent.document.getElementById(\'sbox-window\').close()', 700);">
				<?php echo JText::_('Save');?></button>
			<button type="button" onclick="window.parent.SqueezeBox.close();">
				<?php echo JText::_('Cancel');?></button>
		</div>
		<div class="configuration" >
			<?php echo JText::_($this->component->name) ?>
		</div>
	</fieldset>

	<fieldset>
		<legend>
			<?php echo JText::_('Configuration');?>
		</legend>
		<?php echo $this->params->render();?>
	</fieldset>

	<input type="hidden" name="id" value="<?php echo $this->component->id;?>" />
	<input type="hidden" name="component" value="<?php echo $this->component->option;?>" />

	<input type="hidden" name="controller" value="component" />
	<input type="hidden" name="option" value="com_config" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
