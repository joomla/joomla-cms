<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$user   = JFactory::getUser();
$input  = JFactory::getApplication()->input;

$fieldid = $input->get('fieldid', '');
$linkraw = $input->getString('link', '');
$link = json_decode($linkraw, true);

JHtml::_('formbehavior.chosen', 'select');

// Load tooltip instance without HTML support because we have a HTML tag in the tip
JHtml::_('bootstrap.tooltip', '.noHtmlTip', array('html' => false));

// Include jQuery
JHtml::_('jquery.framework');

// Include script to save modal fields back to parent window
JHtml::_('script', 'com_content/popup-linkmanager.min.js', false, true, false, false, true);
?>
<div class="container-popup">

	<form action="index.php?option=com_content&amp;asset=<?php echo $input->getCmd('asset');?>&amp;author=<?php echo $input->getCmd('author'); ?>" class="form-vertical" id="linkForm" method="post" enctype="multipart/form-data">

		<div id="messages" style="display: none;">
			<span id="message"></span><?php echo JHtml::_('image', 'media/dots.gif', '...', array('width' => 22, 'height' => 12), true) ?>
		</div>

		<div class="well">
			<div class="row">
				<div class="span6 control-group">
					<div class="control-label">
						<label for="f_url"><?php echo JText::_('COM_Content_LINK_URL') ?></label>
					</div>
					<div class="controls">
						<input type="text" id="f_url" value="<?php echo htmlspecialchars($link['url']); ?>" />
					</div>
				</div>
				<div class="span6 control-group">
					<div class="control-label">
						<label for="f_title"><?php echo JText::_('COM_Content_LINK_TITLE') ?></label>
					</div>
					<div class="controls">
						<input type="text" id="f_title" value="<?php echo htmlspecialchars($link['title']); ?>" />
					</div>
				</div>
			</div>
			<div class="row">
				<div class="span6 control-group">
					<div class="control-label">
						<label for="f_rel"><?php echo JText::_('COM_Content_LINK_REL') ?></label>
					</div>
					<div class="controls">
						<input type="text" id="f_rel" value="<?php echo htmlspecialchars($link['rel']); ?>" />
					</div>
				</div>
			</div>
		</div>
		<div class="pull-right">
			<button class="btn btn-success button-save-selected" type="button" onclick="<?php if ($fieldid):?>LinkManager.saveAttr('<?php echo $fieldid . "', 'link=" . ($linkraw ? htmlspecialchars($linkraw) : '') . "'"; endif; ?>);LinkManager.close();" data-dismiss="modal"><?php echo JText::_('JAPPLY') ?></button>
			<button class="btn button-cancel" type="button" onclick="LinkManager.close();" data-dismiss="modal"><?php echo JText::_('JCANCEL') ?></button>
		</div>
	</form>

</div>
