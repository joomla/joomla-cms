<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.modal');
JHtml::_('formbehavior.chosen', 'select');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tabstate');

$input = JFactory::getApplication()->input;

JHtml::_('script', 'system/jquery.Jcrop.min.js', false, true);
JHtml::_('stylesheet', 'system/jquery.Jcrop.min.css', array(), true);

JFormHelper::addFieldPath(JPATH_COMPONENT . '/model/field');

?>
<script type="text/javascript">
	jQuery(document).ready(function($){

			var jcrop_api;

			// Configuration for image cropping
			$('#image-crop').Jcrop({
				onChange:   showCoords,
				onSelect:   showCoords,
				onRelease:  clearCoords,
				trueSize:   [<?php echo $this->image['width']; ?>,<?php echo $this->image['height']; ?>]
			},function(){
				jcrop_api = this;
			});

			// Function for calculating the crop coordinates
			function showCoords(c)
			{
				$('#x').val(c.x);
				$('#y').val(c.y);
				$('#w').val(c.w);
				$('#h').val(c.h);
			};

			// Function for clearing the coordinates
			function clearCoords()
			{
				$('#adminForm input').val('');
			};


			// Functions for automatic calculate when resizing
			$('#resize-height').bind('keyup mouseup', function()
			{
				var currentWidth = $("#current-width").val();
				var currentHeight = $("#current-height").val();
				$("#resize-width").val(Math.round(($(this).val()/currentHeight) * currentWidth));					    
			});

			$('#resize-width').bind('keyup mouseup', function()
			{
				var currentWidth = $("#current-width").val();
				var currentHeight = $("#current-height").val();
				$("#resize-height").val(Math.round(($(this).val()/currentWidth) * currentHeight));					    
			});

	});
	
</script>

<style>

/* Styles for modals */
.selected {
	background: #08c;
	color: #fff;
}

.selected:hover {
	background: #08c !important;
	color: #fff;
}

.modal-body .column {
	width: 50%;
	float: left;
}

#image-crop {
	max-width: 100% !important;
	width: auto;
	height: auto;
}

</style>

<div class="row-fluid">

	<script type="text/javascript">
		Joomla.submitbutton = function(task)
		{
			if (task == 'media.crop.editor' && document.formvalidator.isValid(document.id('media-crop-form')))
			{
				Joomla.submitform(task, document.getElementById('media-crop-form'));
			}
			else if (task == 'media.cancel.editor' || document.formvalidator.isValid(document.id('media-form')))
			{
				Joomla.submitform(task, document.getElementById('media-form'));
			}
		}
	</script>

	<div class="span9">

		<!-- Display Image with Crop -->
		<img id="image-crop"
			src="<?php echo $this->image['address'] . '?' . time(); ?>" />

		<form
			action="<?php echo JRoute::_('index.php?option=com_media&controller=media.crop.editor&folder=' . $this->folder . '&file=' . $this->file . '&id=' . $this->id); ?>"
			method="post" name="adminForm" id="media-crop-form" class="form-horizontal">
			<fieldset class="adminform">
				<input type="hidden" id="x" name="x" />
				<input type="hidden" id="y"	name="y" />
				<input type="hidden" id="h" name="h" />
				<input type="hidden" id="w" name="w" />
				<input type="hidden" name="task" value="" />
				<?php echo JHtml::_('form.token'); ?>
			</fieldset>
		</form>

	</div>

	<div class="span3">

		<form action="<?php echo JRoute::_('index.php?option=com_media&folder=' . $this->folder . '&file=' . $this->file . '&id=' . $this->id); ?>" method="post" name="adminForm" id="media-form" class="form-validate">

					<?php echo $this->loadTemplate('properties'); ?>

					<input type="hidden" name="task" value="" />
					<?php echo JHtml::_('form.token'); ?>
		</form>
			
	</div>

</div>

<!-- Resize Modal -->
<form
	action="<?php echo JRoute::_('index.php?option=com_media&controller=media.resize.editor&folder=' . $this->folder . '&file=' . $this->file . '&id=' . $this->id); ?>"
	method="post">
	<div id="resizeModal" class="modal hide fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"
				aria-hidden="true">&times;</button>
			<h3>
				<?php echo JText::_('COM_MEDIA_EDITOR_RESIZE_IMAGE'); ?>
			</h3>
		</div>
		<div class="modal-body">
			<div id="" class="form-horizontal">
				<div class="control-group">
					<label for="height" class="control-label hasTooltip"
						title="<?php echo JHtml::tooltipText('COM_MEDIA_EDITOR_IMAGE_HEIGHT'); ?>"><?php echo JText::_('COM_MEDIA_EDITOR_IMAGE_HEIGHT')?>
					</label>
					<div class="controls">
						<input class="span3" type="number" name="height" id="resize-height"
							placeholder="<?php echo $this->image['height']; ?> " value="<?php echo $this->image['height']; ?> " required />
						<span class="help-inline">px</span>
					</div>
					<br /> <label for="width" class="control-label hasTooltip"
						title="<?php echo JHtml::tooltipText('COM_MEDIA_EDITOR_IMAGE_WIDTH'); ?>"><?php echo JText::_('COM_MEDIA_EDITOR_IMAGE_WIDTH')?>
					</label>
					<div class="controls">
						<input class="span3" type="number" name="width" id="resize-width"
							placeholder="<?php echo $this->image['height']; ?> " value="<?php echo $this->image['width']; ?> " required />
						<span class="help-inline">px</span>
					</div>
				</div>
			<input type="hidden" id="current-height" value="<?php echo $this->image['height']; ?>" />	
			<input type="hidden" id="current-width" value="<?php echo $this->image['width']; ?>" />
			</div>
		</div>
		<div class="modal-footer">
			<a href="#" class="btn" data-dismiss="modal"><?php echo JText::_('COM_MEDIA_CLOSE'); ?>
			</a>
			<button class="btn btn-primary" type="submit">
				<?php echo JText::_('COM_MEDIA_EDITOR_BUTTON_RESIZE'); ?>
			</button>
		</div>
	</div>
	<?php echo JHtml::_('form.token'); ?>
</form>

<!-- Rotate Modal -->
<form
	action="<?php echo JRoute::_('index.php?option=com_media&controller=media.rotate.editor&folder=' . $this->folder . '&file=' . $this->file . '&id=' . $this->id); ?>"
	method="post">
	<div id="rotateModal" class="modal hide fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"
				aria-hidden="true">&times;</button>
			<h3>
				<?php echo JText::_('COM_MEDIA_EDITOR_ROTATE_IMAGE'); ?>
			</h3>
		</div>
		<div class="modal-body">
			<div id="" class="form-horizontal">
				<div class="control-group">
					<label for="angle" class="control-label hasTooltip"
						title="<?php echo JHtml::tooltipText('COM_MEDIA_EDITOR_IMAGE_ANGLE'); ?>"><?php echo JText::_('COM_MEDIA_EDITOR_IMAGE_ANGLE')?>
					</label>
					<div class="controls">
						<input class="input-xlarge" type="number" name="angle"
							placeholder="0" required />
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<a href="#" class="btn" data-dismiss="modal"><?php echo JText::_('COM_MEDIA_CLOSE'); ?>
			</a>
			<button class="btn btn-primary" type="submit">
				<?php echo JText::_('COM_MEDIA_EDITOR_BUTTON_ROTATE'); ?>
			</button>
		</div>
	</div>
	<?php echo JHtml::_('form.token'); ?>
</form>

<!-- Filter Modal -->
<form
	action="<?php echo JRoute::_('index.php?option=com_media&controller=media.filter.editor&folder=' . $this->folder . '&file=' . $this->file . '&id=' . $this->id); ?>"
	method="post">
	<div id="filterModal" class="modal hide fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"
				aria-hidden="true">&times;</button>
			<h3>
				<?php echo JText::_('COM_MEDIA_EDITOR_FILTER_IMAGE'); ?>
			</h3>
		</div>
		<div class="modal-body">
			<div id="" class="form-horizontal" >
				<div class="control-group">
					<?php 
						$mediafilter = JFormHelper::loadFieldType('Mediafilter', false);
					?>
						<?php echo $mediafilter->getLabel();?>
					<div class="controls">
						<?php echo $mediafilter->getInput(); ?>
					</div>
					<br /> 
					<!-- Only for filters require a value -->
					<label for="value" class="control-label hasTooltip"
						title="<?php echo JHtml::tooltipText('COM_MEDIA_EDITOR_IMAGE_FILTER_VALUE'); ?>"><?php echo JText::_('COM_MEDIA_EDITOR_IMAGE_FILTER_VALUE')?>
					</label>
					<div class="controls">
						<input class="input-small" type="number" name="value"
							placeholder="0" />
						<span class="help-inline"><span class="label label-default">Require Only for "brightness", "contrast", "smooth" filters</span></span>
					</div>

				</div>
			</div>
		</div>
		<div class="modal-footer">
			<a href="#" class="btn" data-dismiss="modal"><?php echo JText::_('COM_MEDIA_CLOSE'); ?>
			</a>
			<button class="btn btn-primary" type="submit">
				<?php echo JText::_('COM_MEDIA_EDITOR_BUTTON_FILTER'); ?>
			</button>
		</div>
	</div>
	<?php echo JHtml::_('form.token'); ?>
</form>

<!-- Thumbs Modal -->
<form
	action="<?php echo JRoute::_('index.php?option=com_media&controller=media.thumbs.editor&folder=' . $this->folder . '&file=' . $this->file . '&id=' . $this->id); ?>"
	method="post">
	<div id="thumbsModal" class="modal hide fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"
				aria-hidden="true">&times;</button>
			<h3>
				<?php echo JText::_('COM_MEDIA_EDITOR_THUMBS_IMAGE'); ?>
			</h3>
		</div>
		<div class="modal-body">
			<div id="" class="form-horizontal">
				<div class="control-group">
					<label for="s" class="control-label hasTooltip"
						title="<?php echo JHtml::tooltipText('COM_MEDIA_EDITOR_IMAGE_THUMBS_SIZE'); ?>"><?php echo JText::_('COM_MEDIA_EDITOR_IMAGE_THUMBS_SIZE')?>
					</label>
					<div class="controls">
						<input class="input-xlarge" type="text" name="s"
							placeholder="100x100" required />
					</div>
				</div>

				<div class="control-group">
					<?php 
						$mediathumbs = JFormHelper::loadFieldType('Mediathumbs', false);

						echo $mediathumbs->getLabel();
					?>
					<div class="controls">
						<?php echo $mediathumbs->getInput(); ?>
					</div>
				</div>

				<div class="control-group">
					<label for="t" class="control-label hasTooltip"
						title="<?php echo JHtml::tooltipText('COM_MEDIA_EDITOR_IMAGE_THUMBS_FOLDER'); ?>"><?php echo JText::_('COM_MEDIA_EDITOR_IMAGE_THUMBS_FOLDER')?>
					</label>
					<div class="controls">
						<input class="input-xlarge" type="text" name="t" required />
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<a href="#" class="btn" data-dismiss="modal"><?php echo JText::_('COM_MEDIA_CLOSE'); ?>
			</a>
			<button class="btn btn-primary" type="submit">
				<?php echo JText::_('COM_MEDIA_EDITOR_BUTTON_THUMBS'); ?>
			</button>
		</div>
	</div>
	<?php echo JHtml::_('form.token'); ?>
</form>
