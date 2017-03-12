<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('bootstrap.tooltip');

$input = JFactory::getApplication()->input;
if ($this->type == 'image')
{
	JHtml::_('script', 'system/jquery.Jcrop.min.js', array('version' => 'auto', 'relative' => true));
	JHtml::_('stylesheet', 'system/jquery.Jcrop.min.css', array('version' => 'auto', 'relative' => true));
}
JFactory::getDocument()->addScriptDeclaration("
jQuery(document).ready(function($){
	// Hide all the folder when the page loads
	$('.folder ul, .component-folder ul').hide();
	// Display the tree after loading
	$('.directory-tree').removeClass('directory-tree');
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
});");
if ($this->type == 'image')
{
	JFactory::getDocument()->addScriptDeclaration("
		jQuery(document).ready(function() {
			var jcrop_api;
			// Configuration for image cropping
			$('#image-crop').Jcrop({
				onChange:   showCoords,
				onSelect:   showCoords,
				onRelease:  clearCoords,
				trueSize:   " . $this->image['width'] . "," . $this->image['height'] . "]
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
		});");
}
JFactory::getDocument()->addStyleDeclaration('
	/* Styles for modals */
	.selected{
		background: #08c;
		color: #fff;
	}
	.selected:hover{
		background: #08c !important;
		color: #fff;
	}
	.modal-body .column {
		width: 50%; float: left;
	}
	#deleteFolder{
		margin: 0;
	}
	#image-crop{
		max-width: 100% !important;
		width: auto;
		height: auto;
	}
	.directory-tree{
		display: none;
	}
	.tree-holder{
		overflow-x: auto;
	}
');
if ($this->type == 'font')
{
	JFactory::getDocument()->addStyleDeclaration(
			"/* Styles for font preview */
		@font-face
		{
			font-family: previewFont;
			src: url('" . $this->font['address'] . "')
		}
		.font-preview{
			font-family: previewFont !important;
		}"
	);
}
?>
<div class="width-60 fltlft">

	<?php if ($this->type != 'home'): ?>
		<div  id="deleteModal" class="modal hide fade">
			<fieldset>
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3><?php echo JText::_('COM_TEMPLATES_ARE_YOU_SURE');?></h3>
				</div>
				<div class="modal-body">
					<p><?php echo JText::sprintf('COM_TEMPLATES_MODAL_FILE_DELETE', $this->fileName); ?></p>
				</div>
				<div class="modal-footer">
					<form method="post" action="">
						<input type="hidden" name="option" value="com_templates" />
						<input type="hidden" name="task" value="template.delete" />
						<input type="hidden" name="id" value="<?php echo $input->getInt('id'); ?>" />
						<input type="hidden" name="file" value="<?php echo $this->file; ?>" />
						<?php echo JHtml::_('form.token'); ?>
						<a href="#" class="btn" data-dismiss="modal"><?php echo JText::_('COM_TEMPLATES_TEMPLATE_CLOSE'); ?></a>
						<button type="submit"><?php echo JText::_('COM_TEMPLATES_BUTTON_DELETE');?></button>
					</form>
				</div>
			</fieldset>
		</div>
	<?php endif; ?>
	<div  id="folderModal" class="modal hide fade">
		<fieldset>
			<legend><?php echo JText::_('COM_TEMPLATES_MANAGE_FOLDERS');?></legend>
			<div class="modal-body">
				<div class="width-50 fltlft">
					<form method="post" action="<?php echo JRoute::_('index.php?option=com_templates&task=template.createFolder&id=' . $input->getInt('id') . '&file=' . $this->file); ?>">
						<fieldset>
							<label><?php echo JText::_('COM_TEMPLATES_FOLDER_NAME');?></label>
							<input type="text" name="name" required />
							<input type="hidden" class="address" name="address" />

							<input type="submit" value="<?php echo JText::_('COM_TEMPLATES_BUTTON_CREATE');?>" class="btn btn-primary" />
						</fieldset>
					</form>
				</div>
				<div class="width-50 fltlft">
					<?php echo $this->loadTemplate('folders');?>
				</div>
			</div>
			<div class="modal-footer">
				<form id="deleteFolder" method="post" action="<?php echo JRoute::_('index.php?option=com_templates&task=template.deleteFolder&id=' . $input->getInt('id') . '&file=' . $this->file); ?>">
					<fieldset>
						<a href="#" class="btn" data-dismiss="modal"><?php echo JText::_('COM_TEMPLATES_TEMPLATE_CLOSE'); ?></a>
						<input type="hidden" class="address" name="address" />
						<input type="submit" value="<?php echo JText::_('COM_TEMPLATES_BUTTON_DELETE');?>" class="btn btn-danger" />
					</fieldset>
				</form>
			</div>
		</fieldset>
	</div>

	<div  id="fileModal" class="modal hide fade">
		<fieldset>
			<legend><?php echo JText::_('COM_TEMPLATES_BUTTON_FILE');?></legend>
			<div class="modal-body">
				<div class="width-50 fltlft">
					<form method="post" action="<?php echo JRoute::_('index.php?option=com_templates&task=template.createFile&id=' . $input->getInt('id') . '&file=' . $this->file); ?>">
						<fieldset>
							<label><?php echo JText::_('COM_TEMPLATES_NEW_FILE_TYPE');?></label>
							<select name="type" required >
								<option value="null">- <?php echo JText::_('COM_TEMPLATES_NEW_FILE_SELECT');?> -</option>
								<option value="css">css</option>
								<option value="php">php</option>
								<option value="js">js</option>
								<option value="xml">xml</option>
								<option value="ini">ini</option>
								<option value="less">less</option>
								<option value="txt">txt</option>
							</select>
							<br />
							<label><?php echo JText::_('COM_TEMPLATES_FILE_NAME');?></label>
							<input type="text" name="name" required />
							<input type="hidden" class="address" name="address" />

							<input type="submit" value="<?php echo JText::_('COM_TEMPLATES_BUTTON_CREATE');?>" class="btn btn-primary" />
						</fieldset>
					</form>
					<br />
					<form method="post" action="<?php echo JRoute::_('index.php?option=com_templates&task=template.uploadFile&id=' . $input->getInt('id') . '&file=' . $this->file); ?>"
						  enctype="multipart/form-data" >
						<fieldset>
							<input type="hidden" class="address" name="address" />
							<input type="file" name="files" required />
							<input type="submit" value="<?php echo JText::_('COM_TEMPLATES_BUTTON_UPLOAD');?>" class="btn btn-primary" /><br>
							<?php $cMax    = $this->state->get('params')->get('upload_limit'); ?>
							<?php $maxSize = JHtml::_('number.bytes', JUtility::getMaxUploadSize($cMax . 'MB')); ?>
							<?php echo JText::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', $maxSize); ?>
						</fieldset>
					</form>
					<br />
					<?php if ($this->type != 'home'): ?>
						<form method="post" action="<?php echo JRoute::_('index.php?option=com_templates&task=template.copyFile&id=' . $input->getInt('id') . '&file=' . $this->file); ?>"
							  enctype="multipart/form-data" >
							<fieldset>
								<input type="hidden" class="address" name="address" />
								<div class="control-group">
									<label for="new_name" class="control-label hasTooltip" title="<?php echo JHtml::_('tooltipText', 'COM_TEMPLATES_FILE_NEW_NAME_DESC'); ?>"><?php echo JText::_('COM_TEMPLATES_FILE_NEW_NAME_LABEL')?></label>
									<div class="controls">
										<input type="text" id="new_name" name="new_name" required />
									</div>
								</div>
								<input type="submit" value="<?php echo JText::_('COM_TEMPLATES_BUTTON_COPY_FILE');?>" class="btn btn-primary" />
							</fieldset>
						</form>
					<?php endif; ?>
				</div>
				<div class="width-50 fltlft">
					<?php echo $this->loadTemplate('folders');?>
				</div>
			</div>
			<div class="modal-footer">
				<a href="#" class="btn" data-dismiss="modal"><?php echo JText::_('COM_TEMPLATES_TEMPLATE_CLOSE'); ?></a>
			</div>
		</fieldset>
	</div>

	<?php if ($this->type != 'home'): ?>
		<form action="<?php echo JRoute::_('index.php?option=com_templates&task=template.resizeImage&id=' . $input->getInt('id') . '&file=' . $this->file); ?>"
			  method="post" >
			<div  id="resizeModal" class="modal hide fade">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3><?php echo JText::_('COM_TEMPLATES_RESIZE_IMAGE'); ?></h3>
				</div>
				<div class="modal-body">
					<div id="template-manager-css" class="form-horizontal">
						<div class="control-group">
							<label for="height" class="control-label hasTooltip" title="<?php echo JHtml::_('tooltipText', 'COM_TEMPLATES_IMAGE_HEIGHT'); ?>"><?php echo JText::_('COM_TEMPLATES_IMAGE_HEIGHT')?></label>
							<div class="controls">
								<input class="input-xlarge" type="number" name="height" placeholder="<?php echo $this->image['height']; ?> px" required />
							</div>
							<br />
							<label for="width" class="control-label hasTooltip" title="<?php echo JHtml::_('tooltipText', 'COM_TEMPLATES_IMAGE_WIDTH'); ?>"><?php echo JText::_('COM_TEMPLATES_IMAGE_WIDTH')?></label>
							<div class="controls">
								<input class="input-xlarge" type="number" name="width" placeholder="<?php echo $this->image['width']; ?> px" required />
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<a href="#" class="btn" data-dismiss="modal"><?php echo JText::_('COM_TEMPLATES_TEMPLATE_CLOSE'); ?></a>
					<button class="btn btn-primary" type="submit"><?php echo JText::_('COM_TEMPLATES_BUTTON_RESIZE'); ?></button>
				</div>
			</div>
			<?php echo JHtml::_('form.token'); ?>
		</form>
	<?php endif; ?>

	<?php if ($this->type == 'home'): ?>
		<form action="<?php echo JRoute::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
			<div id="home-box" style="text-align: justify;">
				<h1><p><?php echo JText::_('COM_TEMPLATES_HOME_HEADING'); ?></p></h1>
				<p><?php echo JText::_('COM_TEMPLATES_HOME_TEXT'); ?></p>
				<p>
					<a href="https://docs.joomla.org/J3.x:How_to_use_the_Template_Manager" target="_blank">
						<?php echo JText::_('COM_TEMPLATES_HOME_BUTTON'); ?>
					</a>
				</p>
			</div>
		</form>
	<?php endif; ?>
	<?php if ($this->type == 'file'): ?>
		<form action="<?php echo JRoute::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
			<fieldset class="adminform">
				<legend><?php echo JText::_('COM_TEMPLATES_SOURCE_CODE');?></legend>
				<p class="label"><?php echo JText::_('COM_TEMPLATES_TOGGLE_FULL_SCREEN'); ?></p>
				<div class="clr"></div>
				<div class="editor-border">
					<?php echo $this->form->getInput('source'); ?>
				</div>
				<input type="hidden" name="task" value="" />
				<?php echo JHtml::_('form.token'); ?>


				<?php echo $this->form->getInput('extension_id'); ?>
				<?php echo $this->form->getInput('filename'); ?>
			</fieldset>
		</form>
	<?php endif; ?>
	<?php if ($this->type == 'image'): ?>
		<div id="image-box"><img id="image-crop" src="<?php echo $this->image['address'] . '?' . time(); ?>" /></div>
		<form action="<?php echo JRoute::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm">
			<input type ="hidden" id="x" name="x" />
			<input type ="hidden" id="y" name="y" />
			<input type ="hidden" id="h" name="h" />
			<input type ="hidden" id="w" name="w" />
			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</form>
	<?php endif; ?>
	<?php if ($this->type == 'archive'): ?>
		<legend><?php echo JText::_('COM_TEMPLATES_FILE_CONTENT_PREVIEW'); ?></legend>
		<form action="<?php echo JRoute::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
			<fieldset>
				<ul class="nav nav-list">
					<?php foreach ($this->archive as $file): ?>
						<li>
							<?php if (substr($file, -1) === DIRECTORY_SEPARATOR): ?>
								<span class="icon-folder"></span>&nbsp;<?php echo $file; ?>
							<?php endif; ?>
							<?php if (substr($file, -1) != DIRECTORY_SEPARATOR): ?>
								<span class="icon-file"></span>&nbsp;<?php echo $file; ?>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</fieldset>
			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>

		</form>
	<?php endif; ?>
	<?php if ($this->type == 'font'): ?>
		<div class="font-preview">
			<form action="<?php echo JRoute::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
				<fieldset class="adminform">
					<legend><?php echo JText::_('COM_TEMPLATES_SOURCE_CODE');?></legend>
					<p class="lead">H1</p><h1>Quickly gaze at Joomla! views from HTML, CSS, JavaScript and XML </h1>
					<p class="lead">H2</p><h2>Quickly gaze at Joomla! views from HTML, CSS, JavaScript and XML </h2>
					<p class="lead">H3</p><h3>Quickly gaze at Joomla! views from HTML, CSS, JavaScript and XML </h3>
					<p class="lead">H4</p><h4>Quickly gaze at Joomla! views from HTML, CSS, JavaScript and XML </h4>
					<p class="lead">H5</p><h5>Quickly gaze at Joomla! views from HTML, CSS, JavaScript and XML </h5>
					<p class="lead">H6</p> <h6>Quickly gaze at Joomla! views from HTML, CSS, JavaScript and XML </h6>
					<p class="lead">Bold</p><b>Quickly gaze at Joomla! views from HTML, CSS, JavaScript and XML </b>
					<p class="lead">Italics</p><i>Quickly gaze at Joomla! views from HTML, CSS, JavaScript and XML </i>
					<p class="lead">Unordered List</p>
					<ul>
						<li>Item</li>
						<li>Item</li>
						<li>Item<br />
							<ul>
								<li>Item</li>
								<li>Item</li>
								<li>Item<br />
									<ul>
										<li>Item</li>
										<li>Item</li>
										<li>Item</li>
									</ul>
								</li>
							</ul>
						</li>
					</ul>
					<p class="lead">Ordered List</p>
					<ol>
						<li>Item</li>
						<li>Item</li>
						<li>Item<br />
							<ul>
								<li>Item</li>
								<li>Item</li>
								<li>Item<br />
									<ul>
										<li>Item</li>
										<li>Item</li>
										<li>Item</li>
									</ul>
								</li>
							</ul>
						</li>
					</ol>
					<input type="hidden" name="task" value="" />
					<?php echo JHtml::_('form.token'); ?>
				</fieldset>
			</form>
		</div>
	<?php endif; ?>

	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_TEMPLATES_TEMPLATE_DESCRIPTION');?></legend>

		<?php echo $this->loadTemplate('description');?>
	</fieldset>

	<div class="clr"></div>
</div>

<div class="width-40 fltrt">

	<?php if ($this->type != 'home'): ?>
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_TEMPLATES_FILE_INFO');?></legend>
			<?php if ($this->type == 'file'): ?>
				<p><?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_FILENAME', $this->source->filename, $this->template->element); ?></p>
			<?php endif; ?>
			<?php if ($this->type == 'image'): ?>
				<p><?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_FILENAME', $this->image['path'], $this->template->element); ?></p>
			<?php endif; ?>
			<?php if ($this->type == 'font'): ?>
				<p><?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_FILENAME', $this->font['rel_path'], $this->template->element); ?></p>
			<?php endif; ?>
		</fieldset>
	<?php endif; ?>

	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_TEMPLATES_TEMPLATE_FILES');?></legend>

		<?php echo $this->loadTemplate('tree');?>
	</fieldset>

	<?php echo JHtml::_('sliders.start', 'content-sliders', array('useCookie' => 1)); ?>
	<?php echo JHtml::_('sliders.panel', JText::_('COM_TEMPLATES_TEMPLATE_COPY'), 'template-copy'); ?>
	<form action="<?php echo JRoute::_('index.php?option=com_templates&task=template.copy&id=' . $input->getInt('id') . '&file=' . $this->file); ?>"
		  method="post" name="adminForm" id="adminForm">
		<fieldset class="panelform">
			<label id="new_name" class="hasTooltip" title="<?php echo JHtml::_('tooltipText', 'COM_TEMPLATES_TEMPLATE_NEW_NAME_DESC'); ?>"><?php echo JText::_('COM_TEMPLATES_TEMPLATE_NEW_NAME_LABEL')?></label>
			<input type="text" id="new_name" name="new_name"  />
			<button type="submit"><?php echo JText::_('COM_TEMPLATES_TEMPLATE_COPY'); ?></button>
		</fieldset>
		<?php echo JHtml::_('form.token'); ?>
	</form>
	<?php if ($this->type != 'home'): ?>
		<?php  echo JHtml::_('sliders.panel', JText::_('COM_TEMPLATES_BUTTON_RENAME'), 'file-rename'); ?>
		<form action="<?php echo JRoute::_('index.php?option=com_templates&task=template.renameFile&id=' . $input->getInt('id') . '&file=' . $this->file); ?>"
			  method="post" name="adminForm" id="adminForm">
			<fieldset class="panelform">
				<label id="new_name" class="hasTooltip" title="<?php echo JHtml::_('tooltipText', JText::_('COM_TEMPLATES_NEW_FILE_NAME')); ?>"><?php echo JText::_('COM_TEMPLATES_NEW_FILE_NAME')?></label>
				<input type="text" name="new_name"  />
				<button type="submit"><?php echo JText::_('COM_TEMPLATES_BUTTON_RENAME'); ?></button>
			</fieldset>
			<?php echo JHtml::_('form.token'); ?>
		</form>
	<?php endif; ?>
	<?php  echo JHtml::_('sliders.panel', JText::_('COM_TEMPLATES_OVERRIDES_MODULES'), 'override-module'); ?>
	<fieldset class="panelform">
		<ul class="adminformlist">
			<?php foreach ($this->overridesList['modules'] as $module): ?>
				<li>
					<a href="<?php echo JRoute::_('index.php?option=com_templates&view=template&task=template.overrides&folder=' . $module->path . '&id=' . $input->getInt('id') . '&file=' . $this->file); ?>">
						<span class="icon-copy"></span>&nbsp;<?php echo $module->name; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</fieldset>
	<?php  echo JHtml::_('sliders.panel', JText::_('COM_TEMPLATES_OVERRIDES_COMPONENTS'), 'override-component'); ?>
	<fieldset class="panelform">
		<ul class="adminformlist">
			<?php foreach ($this->overridesList['components'] as $key => $value): ?>
				<li class="component-folder">
					<a href="#" class="component-folder-url">
						<span class="icon-folder"></span>&nbsp;<?php echo $key; ?>
					</a>
					<ul class="adminformList">
						<?php foreach ($value as $view): ?>
							<li>
								<a class="component-file-url" href="<?php echo JRoute::_('index.php?option=com_templates&view=template&task=template.overrides&folder=' . $view->path . '&id=' . $input->getInt('id') . '&file=' . $this->file); ?>">
									<span class="icon-copy"></span>&nbsp;<?php echo $view->name; ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</li>
			<?php endforeach; ?>
		</ul>
	</fieldset>
	<?php  echo JHtml::_('sliders.panel', JText::_('COM_TEMPLATES_OVERRIDES_PLUGINS'), 'override-plugins'); ?>
	<fieldset class="panelform">
		<ul class="adminformlist">
			<?php foreach ($this->overridesList['plugins'] as $plugins): ?>
				<li>
					<a href="<?php echo JRoute::_('index.php?option=com_templates&view=template&task=template.overrides&folder=' . $plugins->path . '&id=' . $input->getInt('id') . '&file=' . $this->file); ?>">
						<span class="icon-copy"></span>&nbsp;<?php echo $plugins->name; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</fieldset>
	<?php  echo JHtml::_('sliders.panel', JText::_('COM_TEMPLATES_OVERRIDES_LAYOUTS'), 'override-layout'); ?>
	<fieldset class="panelform">
		<ul class="adminformlist">
			<?php foreach ($this->overridesList['layouts'] as $layout): ?>
				<li>
					<a href="<?php echo JRoute::_('index.php?option=com_templates&view=template&task=template.overrides&folder=' . $layout->path . '&id=' . $input->getInt('id') . '&file=' . $this->file); ?>">
						<span class="icon-copy"></span>&nbsp;<?php echo $layout->name; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</fieldset>
	<?php echo JHtml::_('sliders.end'); ?>
</div>
