<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$result = $this->model->getImageInfo();
$input = JFactory::getApplication()->input;
$this->form = $this->model->getForm();

	$row = $result;
	$path = pathinfo($row->urls);
	$image = new JImage(COM_MEDIA_BASE . '/' . $row->urls);
	if ($row->created_user_id == 0) $user->username = 'FTP';
		else $user = JFactory::getUser($row->created_user_id);

$editing = $input->get('editing', '', 'string');
$fullpath = COM_MEDIA_BASEURL . '/' . $editing;

?>
<div class="row-fluid">
	<div class="span3">
		<div id="info">
			<table class="table table-striped">
				<tr>
					<td><?php echo JText::_("COM_MEDIA_UPLOAD_ON") . ":  " . $row->created_time ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_("COM_MEDIA_FIELD_CREATED_BY_LABEL") . ":  " . $user->username ?></td>
				</tr>
				<tr>
					<td><label><?php echo JText::_("COM_MEDIA_FILE_URL") ?></label><input id="fileUrl" type="text"
					                                                                      disabled='disabled'
					                                                                      value=<?php echo "'" . COM_MEDIA_BASEURL . '/' . $row->urls . "'" ?>>
					</td>
				</tr>
				<tr>
					<td><?php echo JText::_("COM_MEDIA_FILE_NAME") . ': ' . $path['filename'] ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_("COM_MEDIA_FILE_TYPE") . ': ' . strtoupper($path['extension']) ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_("COM_MEDIA_DIMENSION") . ': ' . $image->getWidth() . ' x ' . $image->getHeight() ?></td>
				</tr>
			</table>
		</div>
	</div>
	<div class="span9">
		<div class="editor-buttons">
			<div class="btn-group">
				<button class="btn" id="rotateleft"><?php echo JText::_("COM_MEDIA_ROTATE_LEFT") ?></button>
				<button class="btn" id="rotateright"><?php echo JText::_("COM_MEDIA_ROTATE_RIGHT") ?></button>
				<button class="btn" id="flipvertical"><?php echo JText::_("COM_MEDIA_FLIP_VERTICAL") ?></button>
				<button class="btn" id="fliphorizontal"><?php echo JText::_("COM_MEDIA_FLIP_HORIZONTAL") ?></button>
				<button class="btn" id="flipboth"><?php echo JText::_("COM_MEDIA_FLIP_BOTH") ?></button>
				<button class="btn" id="undo"><?php echo JText::_("COM_MEDIA_UNDO") ?></button>
				<button class="btn" id="redo"><?php echo JText::_("COM_MEDIA_REDO") ?></button>
				<button class="btn" id="save"><?php echo JText::_("COM_MEDIA_SAVE") ?></button>
			</div>
		</div>
		<div class="span8">
			<div class="span9" id="image-container">
				<img id="editing" alt="">
			</div>
			<form id="hidden_form">
				<?php echo JHtml::_('form.token'); ?>
				<input type="hidden" name="editing_path" id="editing_path" value="<?php echo $editing ?>"/>
				<input type="hidden" name="full_path" id="fullPath" value="<?php echo $fullpath ?>"/>
				<input type="hidden" name="isOriginal" id="isOriginal" value="0"/>
				<input type="hidden" name="history" id="history" value=""/>
				<input type="hidden" name="current" id="current" value=""/>
			</form>

			<!-- Bootstrap modal -->
			<div class="modal hide fade" id="duplicateModal">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3><?php echo JText::_("COM_MEDIA_FILE_NAME") ?></h3>
				</div>
				<div class="modal-body">
					<div class='alert' id='duplicateAlert'></div>
					<input id="duplicateName" type="text">
				</div>
				<div class="modal-footer">
					<a href="#" class="btn btn-primary" id="saveDuplicate"><?php echo JText::_("COM_MEDIA_SAVE") ?></a>
				</div>
			</div>

		</div>
		<div class="span3">
			<div id="image-options">
				<table>
					<tr>
						<td><?php echo JText::_("COM_MEDIA_WIDTH") ?></td>
						<td><input id="imageWidth" type="text"></td>
						<td><?php echo JText::_("COM_MEDIA_HEIGHT") ?></td>
						<td><input id="imageHeight" type="text"></td>
					</tr>
				</table>
				<button class="btn" id="resize"><?php echo JText::_("COM_MEDIA_RESIZE") ?></button>
				<button type="button" class="btn" data-toggle="modal"
				        data-target="#duplicateModal"><?php echo JText::_("COM_MEDIA_DUPLICATE") ?></button>
				<table id="aspect-ratio">
					<tr>
						<td><?php echo JText::_("COM_MEDIA_RATIO") ?></td>
						<td><input id="ratio-x" type="text"></td>
						<td>:</td>
						<td><input id="ratio-y" type="text"></td>
					</tr>
				</table>
				<button class="btn" id="resetRatio"><?php echo JText::_("COM_MEDIA_RESET_RATIO") ?></button>
				<table id="coordinates">
					<tr>
						<td>X1</td>
						<td><input id="x1" type="text"></td>
						<td>Y1</td>
						<td><input id="y1" type="text"></td>
					</tr>
					<tr>
						<td>X2</td>
						<td><input id="x2" type="text"></td>
						<td>Y2</td>
						<td><input id="y2" type="text"></td>
					</tr>
					<tr>
						<td>W</td>
						<td><input id="w" type="text" disabled="disabled"></td>
						<td>H</td>
						<td><input id="h" type="text" disabled="disabled"></td>
					</tr>
				</table>
				<button class="btn" id="crop"><?php echo JText::_("COM_MEDIA_CROP") ?></button>
			</div>
		</div>

		<form action="index.php?option=com_media" name="adminForm" id="mediamanager-form" method="post"
		      enctype="multipart/form-data">
			<input type="hidden" name="steps" id="steps" value=""/>
			<input type="hidden" name="operation" id="operation" value=""/>
		</form>
	</div>
</div>
