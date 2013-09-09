<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');

$user = JFactory::getUser();
$input = JFactory::getApplication()->input;
$search = $this->state->get('filter.search');
$category = $this->state->get('filter.category.id');
$access = $this->state->get('filter.access');
$listOrder = $this->state->get('ordering');
$direction = $this->state->get('direction');

$filter = "&amp;search=" . $search . "&amp;category=" . $category . "&amp;access=" . $access . "&amp;ordering=" . $listOrder."&amp;direction=".$direction."&amp;access=".$access;
$filterJ = "&search=" . $search . "&category=" . $category . "&access=" . $access . "&ordering=" . $listOrder."&direction=".$direction."&access=".$access;
$sortFields = $this->getSortFields();
?>
<div class="row-fluid">
	<!-- Begin Sidebar -->
	<div class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<style>
		.overall-progress,
		.current-progress {
			width: 150px;
		}
	</style>
	<!-- End Sidebar -->
	<!-- Begin Content -->
	<div class="span8">
		<?php echo $this->loadTemplate('navigation'); ?>

		<div class="clearfix"></div>
		<?php if (($user->authorise('core.create', 'com_media')) and $this->require_ftp) : ?>
			<form action="index.php?option=com_media&amp;task=ftpValidate" name="ftpForm" id="ftpForm" method="post">
				<fieldset title="<?php echo JText::_('COM_MEDIA_DESCFTPTITLE'); ?>">
					<legend><?php echo JText::_('COM_MEDIA_DESCFTPTITLE'); ?></legend>
					<?php echo JText::_('COM_MEDIA_DESCFTP'); ?>
					<label for="username"><?php echo JText::_('JGLOBAL_USERNAME'); ?></label>
					<input type="text" id="username" name="username" class="inputbox" size="70" value=""/>

					<label for="password"><?php echo JText::_('JGLOBAL_PASSWORD'); ?></label>
					<input type="password" id="password" name="password" class="inputbox" size="70" value=""/>
				</fieldset>
			</form>
		<?php endif; ?>

		<form action="index.php?option=com_media" name="adminForm" id="mediamanager-form" method="post"
		      enctype="multipart/form-data">
			<div id="filter-bar" class="btn-toolbar">
				<div class="filter-search btn-group pull-left">
					<label for="filter_search"
					       class="element-invisible"><?php echo JText::_('COM_MEDIA_FILTER_SEARCH_DESC'); ?></label>
					<input type="text" name="filter_search" id="filter_search"
					       placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>"
					       value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="hasTooltip"
					       title="<?php echo JHtml::tooltipText('COM_MEDIA_FILTER_SEARCH_DESC'); ?>"/>
				</div>
				<div class="btn-group pull-left hidden-phone">
					<button type="submit" class="btn hasTooltip"
					        title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><i
							class="icon-search"></i></button>
					<button type="button" class="btn hasTooltip"
					        title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>"
					        onclick="document.id('filter_search').value='';this.form.submit();">
						<i class="icon-remove"></i></button>
				</div>
				<div class="btn-group pull-right hidden-phone">
					<label for="directionTable"
					       class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC');  ?></label>
					<select name="directionTable" id="directionTable" class="input-medium">
						<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC');  ?></option>
						<option
							value="asc" <?php if ($direction == 'asc') echo 'selected="selected"';  ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING');  ?></option>
						<option
							value="desc" <?php if ($direction == 'desc') echo 'selected="selected"';  ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');   ?></option>
					</select>
				</div>
				<div class="btn-group pull-right">
					<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY'); ?></label>
					<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
						<option value=""><?php echo JText::_('JGLOBAL_SORT_BY'); ?></option>
						<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
					</select>
				</div>
			</div>
			<input type="hidden" name="order" value=""/>
			<input type="hidden" name="asc" value=""/>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="category" id="category"/>
			<input type="hidden" name="access" id="access"/>
			<input type="hidden" name="cb1" id="cb1" value="0"/>
			<input class="update-folder" type="hidden" name="folder" id="folder"
			       value="<?php echo $this->state->get('folder'); ?>"/>
		</form>

		<?php if ($user->authorise('core.create', 'com_media')): ?>
			<!-- File Upload Form -->
			<div id="collapseUpload" class="collapse">
				<form
					action="<?php echo JURI::base(); ?>index.php?option=com_media&amp;controller=upload&amp;tmpl=component&amp;<?php echo $this->session->getName() . '=' . $this->session->getId(); ?>&amp;<?php echo JSession::getFormToken(); ?>=1&amp;format=html"
					id="uploadForm" class="form-inline" name="uploadForm" method="post" enctype="multipart/form-data">
					<div id="uploadform">
						<fieldset id="upload-noflash" class="actions">
							<label for="upload-file"
							       class="control-label"><?php echo JText::_('COM_MEDIA_UPLOAD_FILE'); ?></label>
							<input type="file" id="upload-file" name="Filedata[]" multiple/>
							<button class="btn btn-primary" id="upload-submit"><i
									class="icon-upload icon-white"></i> <?php echo JText::_('COM_MEDIA_START_UPLOAD'); ?>
							</button>
							<p class="help-block"><?php echo $this->config->get('upload_maxsize') == '0' ? JText::_('COM_MEDIA_UPLOAD_FILES_NOLIMIT') : JText::sprintf('COM_MEDIA_UPLOAD_FILES', $this->config->get('upload_maxsize')); ?></p>
						</fieldset>
						<input class="update-folder" type="hidden" name="folder" id="folder"
						       value="<?php echo $this->state->get('folder'); ?>"/>
						<input type="hidden" name="return-url"
						       value="<?php echo base64_encode('index.php?option=com_media'); ?>"/>
					</div>
				</form>
			</div>
			<div id="collapseFolder" class="collapse">
				<form
					action="index.php?option=com_media&amp;controller=create&amp;tmpl=<?php echo $input->getCmd('tmpl', 'index'); ?>"
					name="folderForm" id="folderForm" class="form-inline" method="post">
					<div class="path">
						<input class="inputbox" type="text" id="folderpath" readonly="readonly"/>
						<input class="inputbox" type="text" id="foldername" name="foldername"/>
						<input class="update-folder" type="hidden" name="folderbase" id="folderbase"
						       value="<?php echo $this->state->get('folder'); ?>"/>
						<button type="submit" class="btn"><i
								class="icon-folder-open"></i> <?php echo JText::_('COM_MEDIA_CREATE_FOLDER'); ?>
						</button>
					</div>
					<?php echo JHtml::_('form.token'); ?>
				</form>
			</div>
		<?php endif; ?>
		<div id="collapseRename" class="collapse">
			<div class="path">
				<input class="inputbox" type="text" id="renamepath" readonly="readonly"/>
				<input class="inputbox" type="text" id="renameInput" name="renameInput"/>
				<input class="update-folder" type="hidden" name="folderbase" id="folderbase"
				       value="<?php echo $this->state->get('folder'); ?>"/>
				<button href="#" id="renameSubmit" onclick="MediaManager.submit('rename')" class="btn"><i
						class="icon-folder-open"></i><?php echo JText::_('COM_MEDIA_RENAME'); ?></button>
			</div>
		</div>
		<form
			action="index.php?option=com_media&amp;task=folder.create&amp;tmpl=<?php echo $input->getCmd('tmpl', 'index'); ?>"
			name="folderForm" id="folderForm" method="post">
			<div id="folderview">
				<div class="view">
					<iframe class="thumbnail"
					        src="index.php?option=com_media&amp;controller=editor&amp;view=medialist&amp;tmpl=component&amp;
					        folder=<?php echo $this->state->get('folder') . $filter; ?>"
					        id="folderframe" name="folderframe" width="100%" height="500px" marginwidth="0"
					        marginheight="0" scrolling="auto"></iframe>
				</div>
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</form>
	</div>
	<div class="span2" style="margin-top:50px">
		<h4 class="page-header">Folder tree:</h4>

		<div id="treeview">
			<div id="media-tree_tree" class="sidebar-nav">
				<?php echo $this->loadTemplate('folders'); ?>
			</div>
		</div>
	</div>
	<!-- End Content -->
</div>

<script>
	jQuery.noConflict();
	jQuery(function ($) {
		$(document).ready(function () {
			$("#mediamanager-form").submit(function () {
				$("#category").val($("#filter_category_id").val());
				$("#access").val($("#filter_access").val());
			});

			$("select").unbind();
			$("select").change(function () {
				$("#mediamanager-form").submit();
			});

			$("#details").click(function () {
				if ($("#thumbs").hasClass("active")) {
					$("#thumbs").removeClass("active");
					$("#details").addClass("active");
					$("#folderframe").attr('src', "index.php?option=com_media&controller=editor&view=medialist&tmpl=component&folder=<?php echo $this->state->get('folder').$filterJ; ?>&layout=details");
				}
			});

			$("#thumbs").click(function () {
				if ($("#details").hasClass("active")) {
					$("#details").removeClass("active");
					$("#thumbs").addClass("active");
					$("#folderframe").attr('src', "index.php?option=com_media&controller=editor&view=medialist&tmpl=component&folder=<?php echo $this->state->get('folder').$filterJ; ?>&layout=thumbs");
				}
			})
		});
	});
</script>