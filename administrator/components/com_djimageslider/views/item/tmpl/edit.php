<?php
/**
 * @version $Id$
 * @package DJ-ImageSlider
 * @subpackage DJ-ImageSlider Component
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 *
 * DJ-ImageSlider is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-ImageSlider is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-ImageSlider. If not, see <http://www.gnu.org/licenses/>.
 *
 */

// No direct access.
defined('_JEXEC') or die;

JHtml::_('behavior.framework');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
if(version_compare(JVERSION, '3.0', '>=')) JHtml::_('formbehavior.chosen', 'select'); /* J!3.0 only */
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'item.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task, document.getElementById('item-form'));
		}
		else {
			alert("<?php echo $this->escape(JText::_('COM_DJIMAGESLIDER_VALIDATION_FORM_FAILED'));?>");
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_djimageslider&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate form-horizontal">
	<div class="<?php echo $this->classes->row ?>">	
	<div class="<?php echo $this->classes->col ?>7">
		<fieldset class="adminform">
		
			<h3><?php echo empty($this->item->id) ? JText::_('COM_DJIMAGESLIDER_NEW') : JText::sprintf('COM_DJIMAGESLIDER_EDIT', $this->item->id); ?></h3>				
			
			<div class="tab-content">
				
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('title'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('catid'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('catid'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('image'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('image'); ?></div>
				</div>
				<div style="clear:both"></div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('description'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('description'); ?></div>
				</div>
				
			</div>
		</fieldset>
	</div>

	<div class="<?php echo $this->classes->col ?>5">
		
		<fieldset class="panelform well" >
		
			<h3><?php echo JText::_('COM_DJIMAGESLIDER_PUBLISHING_OPTIONS'); ?></h3>
			
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('published'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('published'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('publish_up'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('publish_up'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('publish_down'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('publish_down'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
				</div>
			
		</fieldset>
		
		<?php echo $this->loadTemplate('params'); ?>
		
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
	</div>
</form>

<div class="clr"></div>
