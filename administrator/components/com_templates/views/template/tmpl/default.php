<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tabstate');

$input = JFactory::getApplication()->input;

// No access if not global SuperUser
if (!JFactory::getUser()->authorise('core.admin'))
{
	JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
}

if ($this->type == 'image')
{
	JHtml::_('script', 'system/jquery.Jcrop.min.js', array('version' => 'auto', 'relative' => true));
	JHtml::_('stylesheet', 'system/jquery.Jcrop.min.css', array('version' => 'auto', 'relative' => true));
}

JFactory::getDocument()->addScriptDeclaration("
jQuery(document).ready(function($){

	// Hide all the folder when the page loads
	$('.folder ul, .component-folder ul, .plugin-folder ul, .layout-folder ul').hide();

	// Display the tree after loading
	$('.directory-tree').removeClass('directory-tree');

	// Show all the lists in the path of an open file
	$('.show > ul').show();

	// Stop the default action of anchor tag on a click event
	$('.folder-url, .component-folder-url, .plugin-folder-url, .layout-folder-url').click(function(event){
		event.preventDefault();
	});

	// Prevent the click event from proliferating
	$('.file, .component-file-url, .plugin-file-url').bind('click',function(e){
		e.stopPropagation();
	});

	// Toggle the child indented list on a click event
	$('.folder, .component-folder, .plugin-folder, .layout-folder').bind('click',function(e){
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

	var containerDiv = document.querySelector('.span3.tree-holder'),
		treeContainer = containerDiv.querySelector('.nav.nav-list'),
		liEls = treeContainer.querySelectorAll('.folder.show'),
		filePathEl = document.querySelector('p.lead.hidden.path');

	if(filePathEl)
		var filePathTmp = document.querySelector('p.lead.hidden.path').innerText;

	 if(filePathTmp && filePathTmp.charAt( 0 ) === '/' ) {
			filePathTmp = filePathTmp.slice( 1 );
			filePathTmp = filePathTmp.split('/');
			filePathTmp = filePathTmp[filePathTmp.length - 1];

		for (var i = 0, l = liEls.length; i < l; i++) {
			liEls[i].querySelector('a').classList.add('active');
			if (i === liEls.length - 1) {
				var parentUl = liEls[i].querySelector('ul'),
					allLi = parentUl.querySelectorAll('li'); 
	
				for (var i = 0, l = allLi.length; i < l; i++) {
					aEl = allLi[i].querySelector('a'),
					spanEl = aEl.querySelector('span');
	
					if (spanEl && filePathTmp === $.trim(spanEl.innerText)) {
						aEl.classList.add('active');
					}
				}
			}
		}
	}
});");

if ($this->type == 'image')
{
	JFactory::getDocument()->addScriptDeclaration("
		jQuery(document).ready(function($) {
			var jcrop_api;

			// Configuration for image cropping
			$('#image-crop').Jcrop({
				onChange:   showCoords,
				onSelect:   showCoords,
				onRelease:  clearCoords,
				trueSize:   [" . $this->image['width'] . ',' . $this->image['height'] . "]
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
<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'editor')); ?>
<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'editor', JText::_('COM_TEMPLATES_TAB_EDITOR')); ?>
<div class="row-fluid">
	<div class="span12">
		<?php if ($this->type == 'file') : ?>
			<p class="lead"><?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_FILENAME', $this->source->filename, $this->template->element); ?></p>
			<p class="lead path hidden"><?php echo $this->source->filename; ?></p>
		<?php endif; ?>
		<?php if ($this->type == 'image') : ?>
			<p class="lead"><?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_FILENAME', $this->image['path'], $this->template->element); ?></p>
			<p class="lead path hidden"><?php echo $this->image['path']; ?></p>

		<?php endif; ?>
		<?php if ($this->type == 'font') : ?>
			<p class="lead"><?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_FILENAME', $this->font['rel_path'], $this->template->element); ?></p>
			<p class="lead path hidden"><?php echo $this->font['rel_path']; ?></p>

		<?php endif; ?>
	</div>
</div>
<div class="row-fluid">
	<div class="span3 tree-holder">
		<?php echo $this->loadTemplate('tree'); ?>
	</div>
	<div class="span9">
		<?php if ($this->type == 'home') : ?>
			<form action="<?php echo JRoute::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
				<input type="hidden" name="task" value="" />
				<?php echo JHtml::_('form.token'); ?>
				<div class="hero-unit" style="text-align: justify;">
					<h2><?php echo JText::_('COM_TEMPLATES_HOME_HEADING'); ?></h2>
					<p><?php echo JText::_('COM_TEMPLATES_HOME_TEXT'); ?></p>
					<p>
						<a href="https://docs.joomla.org/Special:MyLanguage/J3.x:How_to_use_the_Template_Manager" target="_blank" class="btn btn-primary btn-large">
							<?php echo JText::_('COM_TEMPLATES_HOME_BUTTON'); ?>
						</a>
					</p>
				</div>
			</form>
		<?php endif; ?>
		<?php if ($this->type == 'file') : ?>
			<form action="<?php echo JRoute::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">

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
			<form action="<?php echo JRoute::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
				<ul class="nav nav-stacked nav-list well">
					<?php foreach ($this->archive as $file) : ?>
						<li>
							<?php if (substr($file, -1) === DIRECTORY_SEPARATOR) : ?>
								<span class="icon-folder" aria-hidden="true"></span>&nbsp;<?php echo $file; ?>
							<?php endif; ?>
							<?php if (substr($file, -1) != DIRECTORY_SEPARATOR) : ?>
								<span class="icon-file" aria-hidden="true"></span>&nbsp;<?php echo $file; ?>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
				<input type="hidden" name="task" value="" />
				<?php echo JHtml::_('form.token'); ?>

			</form>
		<?php endif; ?>
		<?php if ($this->type == 'image') : ?>
			<img id="image-crop" src="<?php echo $this->image['address'] . '?' . time(); ?>" />
			<form action="<?php echo JRoute::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
				<fieldset class="adminform">
					<input type ="hidden" id="x" name="x" />
					<input type ="hidden" id="y" name="y" />
					<input type ="hidden" id="h" name="h" />
					<input type ="hidden" id="w" name="w" />
					<input type="hidden" name="task" value="" />
					<?php echo JHtml::_('form.token'); ?>
				</fieldset>
			</form>
		<?php endif; ?>
		<?php if ($this->type == 'font') : ?>
			<div class="font-preview">
				<form action="<?php echo JRoute::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
					<fieldset class="adminform">
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
	</div>
</div>
<?php echo JHtml::_('bootstrap.endTab'); ?>

<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'overrides', JText::_('COM_TEMPLATES_TAB_OVERRIDES')); ?>
<div class="row-fluid">
	<div class="span3">
		<legend><?php echo JText::_('COM_TEMPLATES_OVERRIDES_MODULES'); ?></legend>
		<ul class="nav nav-list">
			<?php $token = JSession::getFormToken() . '=' . 1; ?>
			<?php foreach ($this->overridesList['modules'] as $module) : ?>
				<li>
					<?php
					$overrideLinkUrl = 'index.php?option=com_templates&view=template&task=template.overrides&folder=' . $module->path
							. '&id=' . $input->getInt('id') . '&file=' . $this->file . '&' . $token;
					?>
					<a href="<?php echo JRoute::_($overrideLinkUrl); ?>">
						<span class="icon-copy" aria-hidden="true"></span>&nbsp;<?php echo $module->name; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<div class="span3">
		<legend><?php echo JText::_('COM_TEMPLATES_OVERRIDES_COMPONENTS'); ?></legend>
		<ul class="nav nav-list">
			<?php $token = JSession::getFormToken() . '=' . 1; ?>
			<?php foreach ($this->overridesList['components'] as $key => $value) : ?>
				<li class="component-folder">
					<a href="#" class="component-folder-url">
						<span class="icon-folder" aria-hidden="true"></span>&nbsp;<?php echo $key; ?>
					</a>
					<ul class="nav nav-list">
						<?php foreach ($value as $view) : ?>
							<li>
								<?php
								$overrideLinkUrl = 'index.php?option=com_templates&view=template&task=template.overrides&folder=' . $view->path
										. '&id=' . $input->getInt('id') . '&file=' . $this->file . '&' . $token;
								?>
								<a class="component-file-url" href="<?php echo JRoute::_($overrideLinkUrl); ?>">
									<span class="icon-copy" aria-hidden="true"></span>&nbsp;<?php echo $view->name; ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<div class="span3">
		<legend><?php echo JText::_('COM_TEMPLATES_OVERRIDES_PLUGINS'); ?></legend>
		<ul class="nav nav-list">
			<?php $token = JSession::getFormToken() . '=' . 1; ?>
			<?php foreach ($this->overridesList['plugins'] as $key => $group) : ?>
				<li class="plugin-folder">
					<a href="#" class="plugin-folder-url">
						<span class="icon-folder" aria-hidden="true"></span>&nbsp;<?php echo $key; ?>
					</a>
					<ul class="nav nav-list">
						<?php foreach ($group as $plugin) : ?>
							<li>
								<?php
								$overrideLinkUrl = 'index.php?option=com_templates&view=template&task=template.overrides&folder=' . $plugin->path
										. '&id=' . $input->getInt('id') . '&file=' . $this->file . '&' . $token;
								?>
								<a class="plugin-file-url" href="<?php echo JRoute::_($overrideLinkUrl); ?>">
									<span class="icon-copy" aria-hidden="true"></span>&nbsp;<?php echo $plugin->name; ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<div class="span3">
		<legend><?php echo JText::_('COM_TEMPLATES_OVERRIDES_LAYOUTS'); ?></legend>
		<ul class="nav nav-list">
			<?php $token = JSession::getFormToken() . '=' . 1; ?>
			<?php foreach ($this->overridesList['layouts'] as $key => $value) : ?>
			<li class="layout-folder">
				<a href="#" class="layout-folder-url">
					<span class="icon-folder" aria-hidden="true"></span>&nbsp;<?php echo $key; ?>
				</a>
				<ul class="nav nav-list">
					<?php foreach ($value as $layout) : ?>
						<li>
							<?php
							$overrideLinkUrl = 'index.php?option=com_templates&view=template&task=template.overrides&folder=' . $layout->path
									. '&id=' . $input->getInt('id') . '&file=' . $this->file . '&' . $token;
							?>
							<a href="<?php echo JRoute::_($overrideLinkUrl); ?>">
								<span class="icon-copy" aria-hidden="true"></span>&nbsp;<?php echo $layout->name; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
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
		'footer' => $this->loadTemplate('modal_copy_footer'),
	),
	'body'     => $this->loadTemplate('modal_copy_body'),
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
			'footer' => $this->loadTemplate('modal_rename_footer'),
		),
		'body'     => $this->loadTemplate('modal_rename_body'),
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
			'footer' => $this->loadTemplate('modal_delete_footer'),
		),
		'body'     => $this->loadTemplate('modal_delete_body'),
	);
	?>
	<?php echo JLayoutHelper::render('joomla.modal.main', $deleteModalData); ?>
<?php endif; ?>
<?php // File Modal
$fileModalData = array(
	'selector' => 'fileModal',
	'params'   => array(
		'title'  => JText::_('COM_TEMPLATES_NEW_FILE_HEADER'),
		'footer' => $this->loadTemplate('modal_file_footer'),
	),
	'body'     => $this->loadTemplate('modal_file_body'),
);
?>
<?php echo JLayoutHelper::render('joomla.modal.main', $fileModalData); ?>
<?php // Folder Modal
$folderModalData = array(
	'selector' => 'folderModal',
	'params'   => array(
		'title'  => JText::_('COM_TEMPLATES_MANAGE_FOLDERS'),
		'footer' => $this->loadTemplate('modal_folder_footer'),
	),
	'body'     => $this->loadTemplate('modal_folder_body'),
);
?>
<?php echo JLayoutHelper::render('joomla.modal.main', $folderModalData); ?>
<?php if ($this->type == 'image') : ?>
	<?php // Resize Modal
	$resizeModalData = array(
		'selector' => 'resizeModal',
		'params'   => array(
			'title'  => JText::_('COM_TEMPLATES_RESIZE_IMAGE'),
			'footer' => $this->loadTemplate('modal_resize_footer'),
		),
		'body'     => $this->loadTemplate('modal_resize_body'),
	);
	?>
	<form action="<?php echo JRoute::_('index.php?option=com_templates&task=template.resizeImage&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post">
		<?php echo JLayoutHelper::render('joomla.modal.main', $resizeModalData); ?>
		<?php echo JHtml::_('form.token'); ?>
	</form>
<?php endif; ?>