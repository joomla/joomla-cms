<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.modal');

$canDo = TemplatesHelper::getActions();
$input = JFactory::getApplication()->input;
if($this->type == 'image')
{
	$doc = JFactory::getDocument();
	$doc->addScript(JUri::root() . 'media/system/js/jquery.Jcrop.min.js');
	$doc->addStyleSheet(JUri::root() . 'media/system/css/jquery.Jcrop.min.css');

}
?>
<script type="text/javascript">
	jQuery(document).ready(function($){

		// Hide all the folder when the page loads
		$('.folder ul').hide();

		// Show all the lists in the path of an open file
		$('.show > ul').show();

		// Stop the default action of anchor tag on a click event
		$('.folder-url').click(function(event){
			event.preventDefault();
		});

		// Prevent the click event from proliferating
		$('.file').bind('click',function(e){
			e.stopPropagation();
		});

		// Toggle the child indented list on a click event
		$('.folder').bind('click',function(e){
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

		<?php if($this->type == 'image'): ?>
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

		<?php endif; ?>

	});
</script>
<style>

		/* Styles for modals */
	.selected{
		display: block;
		background: #08c ;
		color: #fff !important;
	}
	.selected:hover{
		display: block;
		background: #08c;
		color: #fff;
	}

	#image-crop{
		max-width: 100%;
		width: auto;
		height: auto;
	}

	@-moz-document url-prefix() {
	  #image-crop {
		width: 100%;
	  }
	}

	<?php if($this->type == 'font'): ?>

		/* Styles for font preview */
	@font-face
	{
		font-family: previewFont;
		src: url('<?php echo $this->font['address'] ?>')
	}

	.font-preview{
		font-family: previewFont !important;
	}

	<?php endif; ?>

</style>
<div class="width-60 fltlft">

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
				<a href="#" data-dismiss="modal">Close</a>
				<a href="<?php echo JRoute::_('index.php?option=com_templates&task=template.delete&id=' . $input->getInt('id') . '&file=' . $this->file); ?>"><?php echo JText::_('COM_TEMPLATES_BUTTON_DELETE');?></a>
			</div>
		</fieldset>
	</div>

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
						<a href="#" class="btn" data-dismiss="modal">Close</a>
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
								<option>- <?php echo JText::_('COM_TEMPLATES_NEW_FILE_SELECT');?> -</option>
								<option value="css">css</option>
								<option value="php">php</option>
								<option value="js">js</option>
								<option value="xml">xml</option>
								<option value="ini">ini</option>
								<option value="less">less</option>
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
							<input type="submit" value="<?php echo JText::_('COM_TEMPLATES_BUTTON_UPLOAD');?>" class="btn btn-primary" />
						</fieldset>
					</form>
				</div>
				<div class="width-50 fltlft">
					<?php echo $this->loadTemplate('folders');?>
				</div>
			</div>
			<div class="modal-footer">
				<a href="#" class="btn" data-dismiss="modal">Close</a>
			</div>
		</fieldset>
	</div>

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
						<label for="height" class="control-label hasTooltip" title="<?php echo JHtml::tooltipText('COM_TEMPLATES_IMAGE_HEIGHT'); ?>"><?php echo JText::_('COM_TEMPLATES_IMAGE_HEIGHT')?></label>
						<div class="controls">
							<input class="input-xlarge" type="number" name="height" placeholder="<?php echo $this->image['height']; ?> px" required />
						</div>
						<br />
						<label for="width" class="control-label hasTooltip" title="<?php echo JHtml::tooltipText('COM_TEMPLATES_IMAGE_WIDTH'); ?>"><?php echo JText::_('COM_TEMPLATES_IMAGE_WIDTH')?></label>
						<div class="controls">
							<input class="input-xlarge" type="number" name="width" placeholder="<?php echo $this->image['width']; ?> px" required />
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<a href="#" class="btn" data-dismiss="modal">Close</a>
				<button class="btn btn-primary" type="submit"><?php echo JText::_('COM_TEMPLATES_BUTTON_RESIZE'); ?></button>
			</div>
		</div>
		<?php echo JHtml::_('form.token'); ?>
	</form>

	<?php if($this->type == 'file'): ?>
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
	<?php if($this->type == 'image'): ?>
		<form action="<?php echo JRoute::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
			<fieldset class="adminform">
				<legend><?php echo JText::_('COM_TEMPLATES_SOURCE_CODE');?></legend>
				<img id="image-crop" src="<?php echo $this->image['address'] . '?' . time(); ?>" />
				<input type ="hidden" id="x" name="x" />
				<input type ="hidden" id="y" name="y" />
				<input type ="hidden" id="h" name="h" />
				<input type ="hidden" id="w" name="w" />
				<input type="hidden" name="task" value="" />
				<?php echo JHtml::_('form.token'); ?>
			</fieldset>
		</form>
	<?php endif; ?>
	<?php if($this->type == 'font'): ?>
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

		<?php echo JHtml::_('templates.thumb', $this->template->element, $this->template->client_id); ?>


		<h2><?php echo ucfirst($this->template->element); ?></h2>
		<?php $client = JApplicationHelper::getClientInfo($this->template->client_id); ?>
		<p><?php $this->template->xmldata = TemplatesHelper::parseXMLTemplateFile($client->path, $this->template->element);?></p>
		<p><?php  echo JText::_($this->template->xmldata->description); ?></p>
	</fieldset>

	<div class="clr"></div>
</div>

<div class="width-40 fltrt">

	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_TEMPLATES_FILE_INFO');?></legend>
		<?php if($this->type == 'file'): ?>
			<p><?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_FILENAME', $this->source->filename, $this->template->element); ?></p>
		<?php endif; ?>
		<?php if($this->type == 'image'): ?>
			<p><?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_FILENAME', $this->image['address'], $this->template->element); ?></p>
		<?php endif; ?>
		<?php if($this->type == 'font'): ?>
			<p><?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_FILENAME', $this->font['address'], $this->template->element); ?></p>
		<?php endif; ?>
	</fieldset>

	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_TEMPLATES_TEMPLATE_FILES');?></legend>

		<?php echo $this->loadTemplate('tree');?>
	</fieldset>

	<?php echo JHtml::_('sliders.start', 'content-sliders', array('useCookie' => 1)); ?>
		<?php echo JHtml::_('sliders.panel', JText::_('COM_TEMPLATES_TEMPLATE_COPY'), 'template-copy'); ?>
			<form action="<?php echo JRoute::_('index.php?option=com_templates&task=template.copy&id=' . $input->getInt('id') . '&file=' . $this->file); ?>"
				  method="post" name="adminForm" id="adminForm">
				<fieldset class="panelform">
					<label id="new_name" class="hasTooltip" title="<?php echo JHtml::tooltipText('COM_TEMPLATES_TEMPLATE_NEW_NAME_DESC'); ?>"><?php echo JText::_('COM_TEMPLATES_TEMPLATE_NEW_NAME_LABEL')?></label>
					<input class="inputbox" type="text" id="new_name" name="new_name"  />
					<button type="submit"><?php echo JText::_('COM_TEMPLATES_TEMPLATE_COPY'); ?></button>
				</fieldset>
				<?php echo JHtml::_('form.token'); ?>
			</form>
		<?php  echo JHtml::_('sliders.panel', JText::_('COM_TEMPLATES_BUTTON_RENAME'), 'file-rename'); ?>
			<form action="<?php echo JRoute::_('index.php?option=com_templates&task=template.renameFile&id=' . $input->getInt('id') . '&file=' . $this->file); ?>"
				  method="post" name="adminForm" id="adminForm">
				<fieldset class="panelform">
					<label id="new_name" class="hasTooltip" title="<?php echo JHtml::tooltipText(JText::_('COM_TEMPLATES_NEW_FILE_NAME')); ?>"><?php echo JText::_('COM_TEMPLATES_NEW_FILE_NAME')?></label>
					<input class="inputbox" type="text" name="new_name"  />
					<button type="submit"><?php echo JText::_('COM_TEMPLATES_BUTTON_RENAME'); ?></button>
				</fieldset>
				<?php echo JHtml::_('form.token'); ?>
			</form>
	<?php  echo JHtml::_('sliders.panel', JText::_('COM_TEMPLATES_OVERRIDES_MODULES'), 'override-module'); ?>
		<fieldset class="panelform">
			<ul class="adminformlist">
				<?php foreach($this->overridesList['modules'] as $module): ?>
					<li>
						<a href="<?php echo JRoute::_('index.php?option=com_templates&view=template&task=template.overrides&folder=' . $module->path . '&id=' . $input->getInt('id') . '&file=' . $this->file); ?>">
							<i class="icon-copy"></i>&nbsp;<?php echo $module->name; ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</fieldset>
	<?php  echo JHtml::_('sliders.panel', JText::_('COM_TEMPLATES_OVERRIDES_COMPONENTS'), 'override-component'); ?>
		<fieldset class="panelform">
			<ul class="adminformlist">
				<?php foreach($this->overridesList['components'] as $component): ?>
					<li>
						<a href="<?php echo JRoute::_('index.php?option=com_templates&view=template&task=template.overrides&folder=' . $component->path . '&id=' . $input->getInt('id') . '&file=' . $this->file); ?>">
							<i class="icon-copy"></i>&nbsp;<?php echo $component->name; ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</fieldset>
	<?php  echo JHtml::_('sliders.panel', JText::_('COM_TEMPLATES_OVERRIDES_LAYOUTS'), 'override-layout'); ?>
		<fieldset class="panelform">
			<ul class="adminformlist">
				<?php foreach($this->overridesList['layouts'] as $layout): ?>
					<li>
						<a href="<?php echo JRoute::_('index.php?option=com_templates&view=template&task=template.overrides&folder=' . $layout->path . '&id=' . $input->getInt('id') . '&file=' . $this->file); ?>">
							<i class="icon-copy"></i>&nbsp;<?php echo $layout->name; ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</fieldset>
	<?php echo JHtml::_('sliders.end'); ?>

</div>