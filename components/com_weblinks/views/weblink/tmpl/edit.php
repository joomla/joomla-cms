<?php
/**
 * @version		$Id: form.php 11952 2009-06-01 03:21:19Z robs $
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<script language="javascript" type="text/javascript">
function submitbutton(pressbutton)
{
	var form = document.adminForm;
	if (pressbutton == 'cancel') {
		submitform(pressbutton);
		return;
	}

	// do field validation
	if (document.getElementById('jformtitle').value == ""){
		alert("<?php echo JText::_('COM_WEBLINKS_FIELD_TITLE_DESC', true); ?>");
	} else if (document.getElementById('jformcatid').value < 1) {
		alert("<?php echo JText::_('COM_WEBLINKS_FIELD_CATEGORY_DESC', true); ?>");
	} else if (document.getElementById('jformurl').value == ""){
		alert("<?php echo JText::_('COM_WEBLINKS_FIELD_URL_DESC', true); ?>");
	} else {
		submitform(pressbutton);
	}
}
</script>

<div class="<?php echo $this->params->get('pageclass_sfx'); ?>">
<form action="<?php echo $this->action ?>" method="post" name="adminForm" id="adminForm">
<?php if ($this->params->def('show_page_title', 1)) : ?>
	<h1>
		<?php if ($this->escape($this->params->get('page_heading'))) :?>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		<?php else : ?>
			<?php echo $this->escape($this->params->get('page_title')); ?>
		<?php endif; ?>
	</h1>
<?php endif; ?>
<table cellpadding="4" cellspacing="1" border="0" width="100%">
<tr>
	<td width="10%">
		<label for="jformtitle">
			<?php echo JText::_('COM_WEBLINKS_NAME'); ?>:
		</label>
	</td>
	<td width="80%">
		<input class="inputbox" type="text" id="jformtitle" name="jform[title]" size="50" maxlength="250" value="<?php echo $this->escape($this->weblink->title);?>" />
	</td>
</tr>
<tr>
	<td valign="top">
		<label for="jformcatid">
			<?php echo JText::_('COM_WEBLINKS_FIELD_CATEGORY_LABEL'); ?>:
		</label>
	</td>
	<td>
		<?php echo $this->lists['catid']; ?>
	</td>
</tr>
<tr>
	<td valign="top">
		<label for="jformurl">
			<?php echo JText::_('COM_WEBLINKS_FIELD_URL_LABEL'); ?>:
		</label>
	</td>
	<td>
		<input class="inputbox" type="text" id="jformurl" name="jform[url]" value="<?php echo $this->weblink->url; ?>" size="50" maxlength="250" />
	</td>
</tr>
<tr>
	<td valign="top">
		<label for="jformpublished">
			<?php echo JText::_('JOPTION_PUBLISHED'); ?>:
		</label>
	</td>
	<td>
			<?php echo $this->lists['published']; ?>
	</td>
</tr>
<tr>
	<td valign="top">
		<label for="jformdescription">
			<?php echo JText::_('COM_WEBLINKS_FIELD_DESCRIPTION_LABEL'); ?>:
		</label>
	</td>
	<td>
		<textarea class="inputbox" cols="30" rows="6" id="jformdescription" name="jform[description]" style="width:300px"><?php echo $this->escape($this->weblink->description);?></textarea>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="jformordering">
			<?php echo JText::_('JFIELD_ORDERING_LABEL'); ?>:
		</label>
	</td>
	<td>
		<?php echo $this->lists['ordering']; ?>
	</td>
</tr>
</table>

<div>
	<button type="button" onclick="submitbutton('save')">
		<?php echo JText::_('JSAVE') ?>
	</button>
	<button type="button" onclick="submitbutton('cancel')">
		<?php echo JText::_('JCANCEL') ?>
	</button>
</div>

	<input type="hidden" name="jform[id]" value="<?php echo $this->weblink->id; ?>" />
	<input type="hidden" name="jform[ordering]" value="<?php echo $this->weblink->ordering; ?>" />
	<input type="hidden" name="jform[approved]" value="<?php echo $this->weblink->approved; ?>" />
	<input type="hidden" name="option" value="com_weblinks" />
	<input type="hidden" name="controller" value="weblink" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
</div>
