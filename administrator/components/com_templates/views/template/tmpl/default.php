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
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tabstate');

$input = JFactory::getApplication()->input;

// No access if not global SuperUser
if (!JFactory::getUser()->authorise('core.admin'))
{
	JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'danger');
}

if ($this->type == 'image')
{
	JHtml::_('script', 'vendor/cropperjs/cropper.min.js', array('version' => 'auto', 'relative' => true));
	JHtml::_('stylesheet', 'vendor/cropperjs/cropper.min.css', array('version' => 'auto', 'relative' => true));
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
		document.addEventListener('DOMContentLoaded', function() {
			// Configuration for image cropping
			var image = document.getElementById('image-crop');
				var cropper = new Cropper(image, {
				viewMode: 0,
				scalable: true,
				zoomable: true,
				minCanvasWidth: " . $this->image['width'] . ",
				minCanvasHeight: " . $this->image['height'] . ",
			});

			image.addEventListener('crop', function (e) {
				document.getElementById('x').value = e.detail.x;
				document.getElementById('y').value = e.detail.y;
				document.getElementById('w').value = e.detail.width;
				document.getElementById('h').value = e.detail.height;
			});

			// Function for clearing the coordinates
			function clearCoords()
			{
				var inputs = querySelectorAll('#adminForm input');
				
				for(i=0, l=inputs.length; l>i; i++) {
					inputs[i].value = '';
				};
			}
		});");
}

JFactory::getDocument()->addStyleDeclaration('
	/* Styles for modals */
	.selected {
		background: #08c;
		color: #fff;
	}
	.selected:hover {
		background: #08c !important;
		color: #fff;
	}
	.modal-body .column-left {
		float: left; max-height: 70vh; overflow-y: auto;
	}
	.modal-body .column-right {
		float: right;
	}
	@media (max-width: 767px) {
		.modal-body .column-right {
			float: left;
		}
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
<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'editor')); ?>
<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'editor', JText::_('COM_TEMPLATES_TAB_EDITOR')); ?>
<div class="row">
	<div class="col-md-12">
		<?php if($this->type == 'file') : ?>
			<p class="lead"><?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_FILENAME', $this->source->filename, $this->template->element); ?></p>
		<?php endif; ?>
		<?php if($this->type == 'image') : ?>
			<p class="lead"><?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_FILENAME', $this->image['path'], $this->template->element); ?></p>
		<?php endif; ?>
		<?php if($this->type == 'font') : ?>
			<p class="lead"><?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_FILENAME', $this->font['rel_path'], $this->template->element); ?></p>
		<?php endif; ?>
	</div>
</div>
<div class="row">
	<div class="col-md-3 tree-holder">
		<?php echo $this->loadTemplate('tree'); ?>
	</div>
	<div class="col-md-9">
		<?php if ($this->type == 'home') : ?>
			<form action="<?php echo JRoute::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm">
				<input type="hidden" name="task" value="">
				<?php echo JHtml::_('form.token'); ?>
				<h2><?php echo JText::_('COM_TEMPLATES_HOME_HEADING'); ?></h2>
				<p><?php echo JText::_('COM_TEMPLATES_HOME_TEXT'); ?></p>
				<p>
					<a href="https://docs.joomla.org/J3.x:How_to_use_the_Template_Manager" target="_blank" class="btn btn-primary btn-lg">
						<?php echo JText::_('COM_TEMPLATES_HOME_BUTTON'); ?>
					</a>
				</p>
			</form>
		<?php endif; ?>
		<?php if ($this->type == 'file') : ?>
			<form action="<?php echo JRoute::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm">
				<div class="editor-border">
					<?php echo $this->form->getInput('source'); ?>
				</div>
				<input type="hidden" name="task" value="" />
				<?php echo JHtml::_('form.token'); ?>
				<?php echo $this->form->getInput('extension_id'); ?>
				<?php echo $this->form->getInput('filename'); ?>
			</form>
		<?php endif; ?>
		<?php if ($this->type == 'archive') : ?>
			<legend><?php echo JText::_('COM_TEMPLATES_FILE_CONTENT_PREVIEW'); ?></legend>
			<form action="<?php echo JRoute::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm">
				<ul class="nav flex-column well">
					<?php foreach ($this->archive as $file) : ?>
						<li>
							<?php if (substr($file, -1) === DIRECTORY_SEPARATOR) : ?>
								<i class="fa-fw fa fa-folder"></i>&nbsp;<?php echo $file; ?>
							<?php endif; ?>
							<?php if (substr($file, -1) != DIRECTORY_SEPARATOR) : ?>
								<i class="fa-fw fa fa-file-o"></i>&nbsp;<?php echo $file; ?>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
				<input type="hidden" name="task" value="">
				<?php echo JHtml::_('form.token'); ?>
			</form>
		<?php endif; ?>
		<?php if ($this->type == 'image') : ?>
			<img id="image-crop" src="<?php echo $this->image['address'] . '?' . time(); ?>" />
			<form action="<?php echo JRoute::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm">
				<fieldset class="adminform">
					<input type ="hidden" id="x" name="x">
					<input type ="hidden" id="y" name="y">
					<input type ="hidden" id="h" name="h">
					<input type ="hidden" id="w" name="w">
					<input type="hidden" name="task" value="">
					<?php echo JHtml::_('form.token'); ?>
				</fieldset>
			</form>
		<?php endif; ?>
		<?php if ($this->type == 'font') : ?>
			<div class="font-preview">
				<form action="<?php echo JRoute::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm">
					<fieldset class="adminform">
						<h1>H1. Quickly gaze at Joomla! views from HTML, CSS, JavaScript and XML</h1>
						<h2>H2. Quickly gaze at Joomla! views from HTML, CSS, JavaScript and XML</h2>
						<h3>H3. Quickly gaze at Joomla! views from HTML, CSS, JavaScript and XML</h3>
						<h4>H4. Quickly gaze at Joomla! views from HTML, CSS, JavaScript and XML</h4>
						<h5>H5. Quickly gaze at Joomla! views from HTML, CSS, JavaScript and XML</h5>
						<h6>H6. Quickly gaze at Joomla! views from HTML, CSS, JavaScript and XML</h6>
						<p><b>Bold. Quickly gaze at Joomla! views from HTML, CSS, JavaScript and XML</b></p>
						<p><i>Italics. Quickly gaze at Joomla! views from HTML, CSS, JavaScript and XML</i></p>
						<p>Unordered List</p>
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
						<input type="hidden" name="task" value="">
						<?php echo JHtml::_('form.token'); ?>
					</fieldset>
				</form>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php echo JHtml::_('bootstrap.endTab'); ?>

<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'overrides', JText::_('COM_TEMPLATES_TAB_OVERRIDES')); ?>
<div class="row">
	<div class="col-md-4">
		<legend><?php echo JText::_('COM_TEMPLATES_OVERRIDES_MODULES'); ?></legend>
		<ul class="list-unstyled">
			<?php $token = JSession::getFormToken() . '=' . 1; ?>
			<?php foreach ($this->overridesList['modules'] as $module) : ?>
				<li>
					<?php
					$overrideLinkUrl = 'index.php?option=com_templates&view=template&task=template.overrides&folder=' . $module->path
							. '&id=' . $input->getInt('id') . '&file=' . $this->file . '&' . $token;
					?>
					<a href="<?php echo JRoute::_($overrideLinkUrl); ?>">
						<i class="fa fa-files-o"></i>&nbsp;<?php echo $module->name; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<div class="col-md-4">
		<legend><?php echo JText::_('COM_TEMPLATES_OVERRIDES_COMPONENTS'); ?></legend>
		<ul class="list-unstyled">
			<?php $token = JSession::getFormToken() . '=' . 1; ?>
			<?php foreach ($this->overridesList['components'] as $key => $value) : ?>
				<li class="component-folder">
					<a href="#" class="component-folder-url">
						<i class="fa fa-folder"></i>&nbsp;<?php echo $key; ?>
					</a>
					<ul class="list-unstyled">
						<?php foreach ($value as $view) : ?>
							<li>
								<?php
								$overrideLinkUrl = 'index.php?option=com_templates&view=template&task=template.overrides&folder=' . $view->path
										. '&id=' . $input->getInt('id') . '&file=' . $this->file . '&' . $token;
								?>
								<a class="component-file-url" href="<?php echo JRoute::_($overrideLinkUrl); ?>">
									<i class="fa fa-files-o"></i>&nbsp;<?php echo $view->name; ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<div class="col-md-4">
		<legend><?php echo JText::_('COM_TEMPLATES_OVERRIDES_LAYOUTS'); ?></legend>
		<ul class="nav flex-column">
			<?php $token = JSession::getFormToken() . '=' . 1; ?>
			<?php foreach ($this->overridesList['layouts'] as $layout) : ?>
				<li>
					<?php
					$overrideLinkUrl = 'index.php?option=com_templates&view=template&task=template.overrides&folder=' . $layout->path
							. '&id=' . $input->getInt('id') . '&file=' . $this->file . '&' . $token;
					?>
					<a href="<?php echo JRoute::_($overrideLinkUrl); ?>">
						<i class="fa fa-files-o"></i>&nbsp;<?php echo $layout->name; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
<?php echo JHtml::_('bootstrap.endTab'); ?>

<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'description', JText::_('COM_TEMPLATES_TAB_DESCRIPTION')); ?>
<?php echo $this->loadTemplate('description'); ?>
<?php echo JHtml::_('bootstrap.endTab'); ?>
<?php echo JHtml::_('bootstrap.endTabSet'); ?>

<?php // Collapse Modal
$copyModalData = array(
	'selector' => 'copyModal',
	'params'   => array(
		'title'  => JText::_('COM_TEMPLATES_TEMPLATE_COPY'),
		'footer' => $this->loadTemplate('modal_copy_footer')
	),
	'body' => $this->loadTemplate('modal_copy_body')
);
?>
<form action="<?php echo JRoute::_('index.php?option=com_templates&task=template.copy&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm">
	<?php echo JLayoutHelper::render('joomla.modal.main', $copyModalData); ?>
	<?php echo JHtml::_('form.token'); ?>
</form>
<?php if ($this->type != 'home') : ?>
	<?php // Rename Modal
	$renameModalData = array(
		'selector' => 'renameModal',
		'params'   => array(
			'title'  => JText::sprintf('COM_TEMPLATES_RENAME_FILE', $this->fileName),
			'footer' => $this->loadTemplate('modal_rename_footer')
		),
		'body' => $this->loadTemplate('modal_rename_body')
	);
	?>
	<form action="<?php echo JRoute::_('index.php?option=com_templates&task=template.renameFile&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post">
		<?php echo JLayoutHelper::render('joomla.modal.main', $renameModalData); ?>
		<?php echo JHtml::_('form.token'); ?>
	</form>
<?php endif; ?>
<?php if ($this->type != 'home') : ?>
	<?php // Delete Modal
	$deleteModalData = array(
		'selector' => 'deleteModal',
		'params'   => array(
			'title'  => JText::_('COM_TEMPLATES_ARE_YOU_SURE'),
			'footer' => $this->loadTemplate('modal_delete_footer')
		),
		'body' => $this->loadTemplate('modal_delete_body')
	);
	?>
	<?php echo JLayoutHelper::render('joomla.modal.main', $deleteModalData); ?>
<?php endif; ?>
<?php // File Modal
$fileModalData = array(
	'selector' => 'fileModal',
	'params'   => array(
		'title' => JText::_('COM_TEMPLATES_NEW_FILE_HEADER'),
		'footer' => $this->loadTemplate('modal_file_footer')
	),
	'body' => $this->loadTemplate('modal_file_body')
);
?>
<?php echo JLayoutHelper::render('joomla.modal.main', $fileModalData); ?>
<?php // Folder Modal
$folderModalData = array(
	'selector' => 'folderModal',
	'params'   => array(
		'title'  => JText::_('COM_TEMPLATES_MANAGE_FOLDERS'),
		'footer' => $this->loadTemplate('modal_folder_footer')
	),
	'body' => $this->loadTemplate('modal_folder_body')
);
?>
<?php echo JLayoutHelper::render('joomla.modal.main', $folderModalData); ?>
<?php if ($this->type != 'home') : ?>
	<?php // Resize Modal
	$resizeModalData = array(
		'selector' => 'resizeModal',
		'params'   => array(
			'title'	 => JText::_('COM_TEMPLATES_RESIZE_IMAGE'),
			'footer' => $this->loadTemplate('modal_resize_footer')
		),
		'body' => $this->loadTemplate('modal_resize_body')
	);
	?>
	<form action="<?php echo JRoute::_('index.php?option=com_templates&task=template.resizeImage&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post">
		<?php echo JLayoutHelper::render('joomla.modal.main', $resizeModalData); ?>
		<?php echo JHtml::_('form.token'); ?>
	</form>
<?php endif; ?>
