<?php 
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2015 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$item = $displayData['item'];
$params = $displayData['params'];
// first display all image downloads as thumbnails

if($params->get('access-download'))
{
	CJFunctions::load_jquery(array('libs'=>array('colorbox')));
}
?>
<div class="row">
	<?php
	foreach ($item->attachments as $attachment)
	{
		$extension = JString::strtolower(JFile::getExt($attachment->filename));
		if($params->get('access-download') && in_array($extension, array('jpg', 'jpeg', 'png', 'gif')))
		{
			?>
			<div class="col-xs-4 col-md-2">
				<a href="<?php echo CF_ATTACHMENTS_URI.'/'.$attachment->filename?>" class="thumbnail gallery" rel="nofollow">
					<img src="<?php echo CF_ATTACHMENTS_URI.'/'.$attachment->filename?>" alt="<?php echo $attachment->filename;?>">
				</a>
			</div>
			<?php 
		}
	}
	?>
</div>
<div class="row">
	<?php
	// then display all other attachments
	foreach ($item->attachments as $attachment)
	{
		$extension = JString::strtolower(JFile::getExt($attachment->filename));
		if($params->get('access-download') && !in_array($extension, array('jpg', 'jpeg', 'png', 'gif')))
		{
			?>
			<div class="list-group">
				<a class="list-group-item" href="#" onclick="document.adminForm.d_id.value=<?php echo $attachment->id;?>;Joomla.submitbutton('topic.download'); return false;" target="_blank">
					<i class="fa fa-download"></i> <?php echo $this->escape($attachment->filename). ' ('.JText::_('COM_FORUM_DOWNLOAD_ATTACHMENT').')';?>
				</a>
			</div>
			<?php
		}
		else if(! $params->get('access-download')) // no need to display downloadable images as they displayed as thumbnails above
		{
			?>
			<div class="list-group">
				<div class="list-group-item">
					<i class="fa fa-download"></i> <?php echo $this->escape($attachment->filename);?> <span class="muted">(<?php echo JText::_('COM_CJFORUM_MESSAGE_NO_DOWNLOAD_AUTH');?>)</span>
				</div>
			</div>
			<?php
		}
	}
	?>
</div>