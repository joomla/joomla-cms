<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
// JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.modal');
JHtml::_('formbehavior.chosen', 'select');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tabstate');

$input = JFactory::getApplication()->input;

JHtml::_('script', 'system/jquery.Jcrop.min.js', false, true);
JHtml::_('stylesheet', 'system/jquery.Jcrop.min.css', array(), true);

?>
<script type="text/javascript">
	jQuery(document).ready(function($){

		// Hide all the folder when the page loads
		$('.folder ul, .component-folder ul').hide();

		// Display the tree after loading
		$('.directory-tree').removeClass("directory-tree");

		// Show all the lists in the path of an open file
		$('.show > ul').show();

		// Stop the default action of anchor tag on a click event
		$('.folder-url, .component-folder-url').click(function(event){
			event.preventDefault();
		});

		// Prevent the click event from proliferating
		$('.file, .component-file-url').bind('click',function(e){
			e.stopPropagation();
		});

		// Toggle the child indented list on a click event
		$('.folder, .component-folder').bind('click',function(e){
			$(this).children('ul').toggle();
			e.stopPropagation();
		});

		// New file tree
		$('#fileModal .folder-url').bind('click',function(e){
			$('.folder-url').removeClass('selected');
			e.stopPropagation();
			$('#fileModal input.address').val($(this).attr('data-id'));
			$(this).addClass('selected');
		});

		// Folder manager tree
		$('#folderModal .folder-url').bind('click',function(e){
			$('.folder-url').removeClass('selected');
			e.stopPropagation();
			$('#folderModal input.address').val($(this).attr('data-id'));
			$(this).addClass('selected');
		});

		
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

#deleteFolder {
	margin: 0;
}

#image-crop {
	max-width: 100% !important;
	width: auto;
	height: auto;
}

.directory-tree {
	display: none;
}

.tree-holder {
	overflow-x: auto;
}
</style>

<div class="row-fluid">
	<div class="span12">
		<p class="well well-small lead">
			<?php echo JText::_('COM_MEDIA_EDITOR_FILENAME') . ' : ' . $this->image['path']; ?>
		</p>

	</div>
</div>
<div class="row-fluid">

	<div class="span9">


<!-- Display Image with Crop -->
		<img id="image-crop"
			src="<?php echo $this->image['address'] . '?' . time(); ?>" />
		<form
			action="<?php echo JRoute::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>"
			method="post" name="adminForm" id="adminForm" class="form-horizontal">
			<fieldset class="adminform">
				<input type="hidden" id="x" name="x" /> <input type="hidden" id="y"
					name="y" /> <input type="hidden" id="h" name="h" /> <input
					type="hidden" id="w" name="w" /> <input type="hidden" name="task"
					value="" />
				<?php echo JHtml::_('form.token'); ?>
			</fieldset>
		</form>


	</div>
	<div class="span3">
		<?php echo $this->loadTemplate('actions'); ?>
	</div>

</div>

<!-- Rename Modal -->
<form
	action="<?php echo JRoute::_('index.php?option=com_templates&task=template.renameFile&id=' . $input->getInt('id') . '&file=' . $this->file); ?>"
	method="post">
	<div id="renameModal" class="modal hide fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"
				aria-hidden="true">&times;</button>
			<h3>
				<?php echo JText::sprintf('COM_TEMPLATES_RENAME_FILE', $this->file); ?>
			</h3>
		</div>
		<div class="modal-body">
			<div id="template-manager-css" class="form-horizontal">
				<div class="control-group">
					<label for="new_name" class="control-label hasTooltip"
						title="<?php echo JHtml::tooltipText(JText::_('COM_TEMPLATES_NEW_FILE_NAME')); ?>"><?php echo JText::_('COM_TEMPLATES_NEW_FILE_NAME')?>
					</label>
					<div class="controls">
						<input class="input-xlarge" type="text" name="new_name" required />
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<a href="#" class="btn" data-dismiss="modal"><?php echo JText::_('COM_TEMPLATES_TEMPLATE_CLOSE'); ?>
			</a>
			<button class="btn btn-primary" type="submit">
				<?php echo JText::_('COM_TEMPLATES_BUTTON_RENAME'); ?>
			</button>
		</div>
	</div>
	<?php echo JHtml::_('form.token'); ?>
</form>

<!-- Resize Modal -->
<form
	action="<?php echo JRoute::_('index.php?option=com_templates&task=template.resizeImage&id=' . $input->getInt('id') . '&file=' . $this->file); ?>"
	method="post">
	<div id="resizeModal" class="modal hide fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"
				aria-hidden="true">&times;</button>
			<h3>
				<?php echo JText::_('COM_TEMPLATES_RESIZE_IMAGE'); ?>
			</h3>
		</div>
		<div class="modal-body">
			<div id="template-manager-css" class="form-horizontal">
				<div class="control-group">
					<label for="height" class="control-label hasTooltip"
						title="<?php echo JHtml::tooltipText('COM_TEMPLATES_IMAGE_HEIGHT'); ?>"><?php echo JText::_('COM_TEMPLATES_IMAGE_HEIGHT')?>
					</label>
					<div class="controls">
						<input class="input-xlarge" type="number" name="height"
							placeholder="<?php echo $this->image['height']; ?> px" required />
					</div>
					<br /> <label for="width" class="control-label hasTooltip"
						title="<?php echo JHtml::tooltipText('COM_TEMPLATES_IMAGE_WIDTH'); ?>"><?php echo JText::_('COM_TEMPLATES_IMAGE_WIDTH')?>
					</label>
					<div class="controls">
						<input class="input-xlarge" type="number" name="width"
							placeholder="<?php echo $this->image['width']; ?> px" required />
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<a href="#" class="btn" data-dismiss="modal"><?php echo JText::_('COM_TEMPLATES_TEMPLATE_CLOSE'); ?>
			</a>
			<button class="btn btn-primary" type="submit">
				<?php echo JText::_('COM_TEMPLATES_BUTTON_RESIZE'); ?>
			</button>
		</div>
	</div>
	<?php echo JHtml::_('form.token'); ?>
</form>

