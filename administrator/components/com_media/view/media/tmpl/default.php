<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$user  = JFactory::getUser();
$input = JFactory::getApplication()->input;
?>
<div class="row-fluid">
	<!-- Begin Sidebar -->
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
		<hr>
		<b>
		<?php echo JText::_('COM_MEDIA_FOLDER_TREE');?>
		</b>
		<hr>
		<div id="treeview">
			<div id="media-tree_tree" class="sidebar-nav">
				<?php echo $this->loadTemplate('folders'); ?>
			</div>
		</div>
	</div>

	<!-- End Sidebar -->
	<!-- Begin Content -->
	<div id="j-main-container" class="span10">
		<?php echo $this->loadTemplate('navigation'); ?>
		<?php if (($user->authorise('core.create', 'com_media')) and $this->require_ftp) : ?>
			<form action="<?php echo JRoute::_('index.php?option=com_media&controller=media.ftpValidate'); ?>" name="ftpForm" id="ftpForm" method="post">
				<fieldset title="<?php echo JText::_('COM_MEDIA_DESCFTPTITLE'); ?>">
					<legend><?php echo JText::_('COM_MEDIA_DESCFTPTITLE'); ?></legend>
					<?php echo JText::_('COM_MEDIA_DESCFTP'); ?>
					<label for="username"><?php echo JText::_('JGLOBAL_USERNAME'); ?></label>
					<input type="text" id="username" name="username" class="inputbox" size="70" value="" />

					<label for="password"><?php echo JText::_('JGLOBAL_PASSWORD'); ?></label>
					<input type="password" id="password" name="password" class="inputbox" size="70" value="" />
				</fieldset>
			</form>
		<?php endif; ?>

		<form action="index.php?option=com_media" name="adminForm" id="mediamanager-form" method="post" enctype="multipart/form-data" >
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="cb1" id="cb1" value="0" />
			<input class="update-folder" type="hidden" name="folder" id="folder" value="<?php echo $this->state->get('folder'); ?>" />
		</form>

		<?php if ($user->authorise('core.create', 'com_media')):?>
		
	<!-- File Upload Form Modal -->
	<form action="<?php echo JRoute::_('index.php?option=com_media&controller=media.upload.media&format=html'); ?>" id="uploadForm" name="uploadForm" method="post" enctype="multipart/form-data" >
	<div id="uploadModal" class="modal hide fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"
				aria-hidden="true">&times;</button>
			<h3>
				<?php echo JText::_('COM_MEDIA_UPLOAD_FILE'); ?>
			</h3>
		</div>
		<div class="modal-body">

			<?php 
			echo JHtml::_('bootstrap.startAccordion', 'collapseTypes', array('active' => 'collapse_dragndrop'));

			echo JHtml::_('bootstrap.addSlide', 'collapseTypes', JText::_('COM_MEDIA_DRAGNDROP_UPLOAD'), 'collapse_dragndrop');
			?>

				<!-- Ajax based Drag&Drop Uploader -->
				<div id="dragandrophandler" class="hero-unit"><h2>Drag & Drop Files Here</h2></div>

						<table class="table table-striped">
							<tbody id="upload-container">	 						
							</tbody>					
						</table>

				<input type="hidden" id="form-token" value="<?php echo JSession::getFormToken();?>" />
			<?php echo JHtml::_('bootstrap.endSlide'); ?>

			<?php echo JHtml::_('bootstrap.addSlide', 'collapseTypes', JText::_('COM_MEDIA_REGULAR_UPLOAD'), 'collapse_regular'); ?>

				<!-- Regular Uploader -->
				<div id="" class="form-horizontal">
					<fieldset id="upload-noflash" class="actions">
						<label for="upload-file" class="control-label"><?php echo JText::_('COM_MEDIA_UPLOAD_FILE'); ?></label>
						<input type="file" id="upload-file" name="Filedata[]" multiple /> 
						<p class="help-block"><?php echo $this->config->get('upload_maxsize') == '0' ? JText::_('COM_MEDIA_UPLOAD_FILES_NOLIMIT') : JText::sprintf('COM_MEDIA_UPLOAD_FILES', $this->config->get('upload_maxsize')); ?></p>
					</fieldset>
						<input class="update-folder" type="hidden" name="folder" id="folder" value="<?php echo $this->state->get('folder'); ?>" />
						<input type="hidden" name="return-url" value="<?php echo base64_encode('index.php?option=com_media'); ?>" />

						<button class="btn btn-primary" id="upload-submit">
							<i class="icon-upload icon-white"></i> <?php echo JText::_('COM_MEDIA_START_UPLOAD'); ?>
						</button>
				</div>

			<?php echo JHtml::_('bootstrap.endSlide'); ?>
			<?php echo JHtml::_('bootstrap.endAccordion'); ?>

		</div>
		<div class="modal-footer">
			<a href="#" class="btn" data-dismiss="modal"><?php echo JText::_('COM_MEDIA_UPLOAD_CLOSE'); ?>
			</a>
			
		</div>
	</div>
		<?php echo JHtml::_('form.token'); ?>
	</form>
		
		
	<!-- New Folder Form Modal -->
	<form action="<?php echo JRoute::_('index.php?option=com_media&controller=media.create.medialist'); ?>" method="post">
	<div id="newfolderModal" class="modal hide fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"
				aria-hidden="true">&times;</button>
			<h3>
				<?php echo JText::_('COM_MEDIA_CREATE_FOLDER'); ?>
			</h3>
		</div>
		<div class="modal-body">
			<div id="" class="form-horizontal">
						<input class="input-xlarge" type="text" id="folderpath" readonly="readonly" />
						<input class="input-medium" type="text" id="foldername" name="foldername" required />
						<input class="update-folder" type="hidden" name="folderbase" id="folderbase" value="<?php echo $this->state->get('folder'); ?>" />
			</div>
		</div>
		<div class="modal-footer">
			<a href="#" class="btn" data-dismiss="modal"><?php echo JText::_('COM_MEDIA_CREATE_FOLDER_CLOSE'); ?>
			</a>
			<button class="btn btn-primary" type="submit">
				<i class="icon-folder-open"></i> <?php echo JText::_('COM_MEDIA_CREATE_FOLDER'); ?>
			</button>
		</div>
	</div>
		<?php echo JHtml::_('form.token'); ?>
	</form>
		
		<?php endif;?>

		<form action="index.php?option=com_media&amp;task=folder.create&amp;tmpl=<?php echo $input->getCmd('tmpl', 'index');?>" name="folderForm" id="folderForm" method="post">
			<div id="folderview">
				<div class="view">
					<iframe class="thumbnail" src="index.php?option=com_media&amp;controller=media.display.medialist&amp;view=medialist&amp;tmpl=component&amp;folder=<?php echo $this->state->get('folder');?>" id="folderframe" name="folderframe" width="100%" height="500px" marginwidth="0" marginheight="0" scrolling="auto"></iframe>
				</div>
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</form>
	</div>
	<!-- End Content -->
</div>
