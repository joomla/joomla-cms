<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$input = Factory::getApplication()->input;

// No access if not global SuperUser
if (!Factory::getUser()->authorise('core.admin'))
{
	Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'danger');
}

if ($this->type == 'image')
{
	HTMLHelper::_('script', 'vendor/cropperjs/cropper.min.js', array('version' => 'auto', 'relative' => true));
	HTMLHelper::_('stylesheet', 'vendor/cropperjs/cropper.min.css', array('version' => 'auto', 'relative' => true));
}

HTMLHelper::_('script', 'com_templates/admin-templates-default.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('stylesheet', 'com_templates/admin-templates-default.css', array('version' => 'auto', 'relative' => true));

if ($this->type == 'font')
{
	Factory::getDocument()->addStyleDeclaration("
		@font-face {
			font-family: previewFont;
			src: url('" . $this->font['address'] . "')
		}
		.font-preview {
			font-family: previewFont !important;
		}
	");
}
?>
<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'editor')); ?>
<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'editor', Text::_('COM_TEMPLATES_TAB_EDITOR')); ?>
<div class="row">
	<div class="col-md-12">
		<?php if($this->type == 'file') : ?>
			<p class="lead"><?php echo Text::sprintf('COM_TEMPLATES_TEMPLATE_FILENAME', $this->source->filename, $this->template->element); ?></p>
			<p class="lead path hidden"><?php echo $this->source->filename; ?></p>
		<?php endif; ?>
		<?php if($this->type == 'image') : ?>
			<p class="lead"><?php echo Text::sprintf('COM_TEMPLATES_TEMPLATE_FILENAME', $this->image['path'], $this->template->element); ?></p>
			<p class="lead path hidden"><?php echo $this->image['path']; ?></p>
		<?php endif; ?>
		<?php if($this->type == 'font') : ?>
			<p class="lead"><?php echo Text::sprintf('COM_TEMPLATES_TEMPLATE_FILENAME', $this->font['rel_path'], $this->template->element); ?></p>
			<p class="lead path hidden"><?php echo $this->font['rel_path']; ?></p>
		<?php endif; ?>
	</div>
</div>
<div class="row">
	<div id="treeholder" class="col-md-3 tree-holder">
		<?php echo $this->loadTemplate('tree'); ?>
	</div>
	<div class="col-md-9">
		<?php if ($this->type == 'home') : ?>
			<form action="<?php echo Route::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm">
				<input type="hidden" name="task" value="">
				<?php echo HTMLHelper::_('form.token'); ?>
				<h2><?php echo Text::_('COM_TEMPLATES_HOME_HEADING'); ?></h2>
				<p><?php echo Text::_('COM_TEMPLATES_HOME_TEXT'); ?></p>
				<p>
					<a href="https://docs.joomla.org/Special:MyLanguage/J3.x:How_to_use_the_Template_Manager" target="_blank" class="btn btn-primary btn-lg">
						<?php echo Text::_('COM_TEMPLATES_HOME_BUTTON'); ?>
					</a>
				</p>
			</form>
		<?php endif; ?>
		<?php if ($this->type == 'file') : ?>
			<form action="<?php echo Route::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm">
				<div class="editor-border">
					<?php echo $this->form->getInput('source'); ?>
				</div>
				<input type="hidden" name="task" value="" />
				<?php echo HTMLHelper::_('form.token'); ?>
				<?php echo $this->form->getInput('extension_id'); ?>
				<?php echo $this->form->getInput('filename'); ?>
			</form>
		<?php endif; ?>
		<?php if ($this->type == 'archive') : ?>
			<legend><?php echo Text::_('COM_TEMPLATES_FILE_CONTENT_PREVIEW'); ?></legend>
			<form action="<?php echo Route::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm">
				<ul class="nav flex-column well">
					<?php foreach ($this->archive as $file) : ?>
						<li>
							<?php if (substr($file, -1) === DIRECTORY_SEPARATOR) : ?>
								<span class="fa-fw fa fa-folder" aria-hidden="true"></span>&nbsp;<?php echo $file; ?>
							<?php endif; ?>
							<?php if (substr($file, -1) != DIRECTORY_SEPARATOR) : ?>
								<span class="fa-fw fa fa-file-o" aria-hidden="true"></span>&nbsp;<?php echo $file; ?>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
				<input type="hidden" name="task" value="">
				<?php echo HTMLHelper::_('form.token'); ?>
			</form>
		<?php endif; ?>
		<?php if ($this->type == 'image') : ?>
			<img id="image-crop" src="<?php echo $this->image['address'] . '?' . time(); ?>">
			<form action="<?php echo Route::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm">
				<fieldset class="adminform">
					<input type="hidden" id="x" name="x">
					<input type="hidden" id="y" name="y">
					<input type="hidden" id="h" name="h">
					<input type="hidden" id="w" name="w">
					<input type="hidden" id="imageWidth" value="<?php echo $this->image['width']; ?>">
					<input type="hidden" id="imageHeight" value="<?php echo $this->image['height']; ?>">
					<input type="hidden" name="task" value="">
					<?php echo HTMLHelper::_('form.token'); ?>
				</fieldset>
			</form>
		<?php endif; ?>
		<?php if ($this->type == 'font') : ?>
			<div class="font-preview">
				<form action="<?php echo Route::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm">
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
							<li>Item<br>
								<ul>
									<li>Item</li>
									<li>Item</li>
									<li>Item<br>
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
							<li>Item<br>
								<ul>
									<li>Item</li>
									<li>Item</li>
									<li>Item<br>
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
						<?php echo HTMLHelper::_('form.token'); ?>
					</fieldset>
				</form>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php echo HTMLHelper::_('uitab.endTab'); ?>

<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'overrides', Text::_('COM_TEMPLATES_TAB_OVERRIDES')); ?>
<div class="row">
	<div class="col-md-4">
		<legend><?php echo Text::_('COM_TEMPLATES_OVERRIDES_MODULES'); ?></legend>
		<ul class="list-unstyled">
			<?php $token = Session::getFormToken() . '=' . 1; ?>
			<?php foreach ($this->overridesList['modules'] as $module) : ?>
				<li>
					<?php
					$overrideLinkUrl = 'index.php?option=com_templates&view=template&task=template.overrides&folder=' . $module->path
							. '&id=' . $input->getInt('id') . '&file=' . $this->file . '&' . $token;
					?>
					<a href="<?php echo Route::_($overrideLinkUrl); ?>">
						<span class="fa fa-files-o" aria-hidden="true"></span>&nbsp;<?php echo $module->name; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<div class="col-md-4">
		<legend><?php echo Text::_('COM_TEMPLATES_OVERRIDES_COMPONENTS'); ?></legend>
		<ul class="list-unstyled">
			<?php $token = Session::getFormToken() . '=' . 1; ?>
			<?php foreach ($this->overridesList['components'] as $key => $value) : ?>
				<li class="component-folder">
					<a href="#" class="component-folder-url">
						<span class="fa fa-folder" aria-hidden="true"></span>&nbsp;<?php echo $key; ?>
					</a>
					<ul class="list-unstyled">
						<?php foreach ($value as $view) : ?>
							<li>
								<?php
								$overrideLinkUrl = 'index.php?option=com_templates&view=template&task=template.overrides&folder=' . $view->path
										. '&id=' . $input->getInt('id') . '&file=' . $this->file . '&' . $token;
								?>
								<a class="component-file-url" href="<?php echo Route::_($overrideLinkUrl); ?>">
									<span class="fa fa-files-o" aria-hidden="true"></span>&nbsp;<?php echo $view->name; ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<div class="col-md-4">
		<legend><?php echo Text::_('COM_TEMPLATES_OVERRIDES_LAYOUTS'); ?></legend>
		<ul class="list-unstyled">
			<?php $token = Session::getFormToken() . '=' . 1; ?>
			<?php foreach ($this->overridesList['layouts'] as $key => $value) : ?>
			<li class="layout-folder">
				<a href="#" class="layout-folder-url">
					<span class="fa fa-folder" aria-hidden="true"></span>&nbsp;<?php echo $key; ?>
				</a>
				<ul class="list-unstyled">
					<?php foreach ($value as $layout) : ?>
						<li>
							<?php
							$overrideLinkUrl = 'index.php?option=com_templates&view=template&task=template.overrides&folder=' . $layout->path
									. '&id=' . $input->getInt('id') . '&file=' . $this->file . '&' . $token;
							?>
							<a href="<?php echo Route::_($overrideLinkUrl); ?>">
								<span class="fa fa-files-o" aria-hidden="true"></span>&nbsp;<?php echo $layout->name; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
<?php echo HTMLHelper::_('uitab.endTab'); ?>

<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'description', Text::_('COM_TEMPLATES_TAB_DESCRIPTION')); ?>
<?php echo $this->loadTemplate('description'); ?>
<?php echo HTMLHelper::_('uitab.endTab'); ?>
<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

<?php // Collapse Modal
$copyModalData = array(
	'selector' => 'copyModal',
	'params'   => array(
		'title'  => Text::_('COM_TEMPLATES_TEMPLATE_COPY'),
		'footer' => $this->loadTemplate('modal_copy_footer')
	),
	'body' => $this->loadTemplate('modal_copy_body')
);
?>
<form action="<?php echo Route::_('index.php?option=com_templates&task=template.copy&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm">
	<?php echo LayoutHelper::render('joomla.modal.main', $copyModalData); ?>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php if ($this->type != 'home') : ?>
	<?php // Rename Modal
	$renameModalData = array(
		'selector' => 'renameModal',
		'params'   => array(
			'title'  => Text::sprintf('COM_TEMPLATES_RENAME_FILE', $this->fileName),
			'footer' => $this->loadTemplate('modal_rename_footer')
		),
		'body' => $this->loadTemplate('modal_rename_body')
	);
	?>
	<form action="<?php echo Route::_('index.php?option=com_templates&task=template.renameFile&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post">
		<?php echo LayoutHelper::render('joomla.modal.main', $renameModalData); ?>
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
<?php endif; ?>
<?php if ($this->type != 'home') : ?>
	<?php // Delete Modal
	$deleteModalData = array(
		'selector' => 'deleteModal',
		'params'   => array(
			'title'  => Text::_('COM_TEMPLATES_ARE_YOU_SURE'),
			'footer' => $this->loadTemplate('modal_delete_footer')
		),
		'body' => $this->loadTemplate('modal_delete_body')
	);
	?>
	<?php echo LayoutHelper::render('joomla.modal.main', $deleteModalData); ?>
<?php endif; ?>
<?php // File Modal
$fileModalData = array(
	'selector' => 'fileModal',
	'params'   => array(
		'title'      => Text::_('COM_TEMPLATES_NEW_FILE_HEADER'),
		'footer'     => $this->loadTemplate('modal_file_footer'),
		'height'     => '400px',
		'width'      => '800px',
		'bodyHeight' => 50,
		'modalWidth' => 60,
	),
	'body' => $this->loadTemplate('modal_file_body')
);
?>
<?php echo LayoutHelper::render('joomla.modal.main', $fileModalData); ?>
<?php // Folder Modal
$folderModalData = array(
	'selector' => 'folderModal',
	'params'   => array(
		'title'      => Text::_('COM_TEMPLATES_MANAGE_FOLDERS'),
		'footer'     => $this->loadTemplate('modal_folder_footer'),
		'height'     => '400px',
		'width'      => '800px',
		'bodyHeight' => 50,
		'modalWidth' => 60,
	),
	'body' => $this->loadTemplate('modal_folder_body')
);
?>
<?php echo LayoutHelper::render('joomla.modal.main', $folderModalData); ?>
<?php if ($this->type != 'home') : ?>
	<?php // Resize Modal
	$resizeModalData = array(
		'selector' => 'resizeModal',
		'params'   => array(
			'title'	 => Text::_('COM_TEMPLATES_RESIZE_IMAGE'),
			'footer' => $this->loadTemplate('modal_resize_footer')
		),
		'body' => $this->loadTemplate('modal_resize_body')
	);
	?>
	<form action="<?php echo Route::_('index.php?option=com_templates&task=template.resizeImage&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post">
		<?php echo LayoutHelper::render('joomla.modal.main', $resizeModalData); ?>
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
<?php endif; ?>
