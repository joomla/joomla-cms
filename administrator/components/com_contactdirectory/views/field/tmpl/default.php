<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	ContactDirectory
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.tooltip');

// Set toolbar items for the page
$edit		= JRequest::getVar('edit',true);
$text = !$edit ? JText::_('NEW') : JText::_('EDIT');
JToolBarHelper::title(  JText::_('FIELD').': <small><small>[ ' . $text.' ]</small></small>');
JToolBarHelper::save();
JToolBarHelper::apply();
if (!$edit)  {
	JToolBarHelper::cancel();
} else {
	// for existing items the button is renamed `close`
	JToolBarHelper::cancel('cancel', 'Close');
}
?>

<script language="javascript" type="text/javascript">
	function submitbutton(pressbutton) {
		var form = document.adminForm;
		if (pressbutton == 'cancel') {
			submitform(pressbutton);
			return;
		}

		// do field validation
		var id = <?php if ($this->field->id)echo $this->field->id;else echo 0;?>;
		if (form.title.value == ""){
			alert("<?php echo JText::_('FIELD_ITEM_MUST_HAVE_A_TITLE'); ?>");
		}else if (id == 1 && form.type.value != "email"){
			alert("<?php echo JText::_('NOT_AUTHORIZED_CHANGE_TYPE_EMAIL'); ?>");
		}else {
			submitform(pressbutton);
		}
	}
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<div class="col width-60">
		<fieldset class="adminform">
			<legend><?php echo JText::_('DETAILS'); ?></legend>
			<table class="admintable">
				<tr>
					<td width="100" align="right" class="key"><?php echo JText::_('TITLE'); ?>:</td>
					<td>
						<input class="text_area" type="text" name="title" id="title"
						size="32" maxlength="250" value="<?php echo $this->field->title;?>"/>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right" class="key"><?php echo JText::_('ALIAS'); ?>:</td>
					<td>
						<input class="text_area" type="text" name="alias" id="alias"
						size="32" maxlength="250" value="<?php echo $this->field->alias;?>"/>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right" class="key"><?php echo JText::_('PUBLISHED'); ?>:</td>
					<td><?php echo $this->lists['published']; ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('POSITION'); ?>:</td>
					<td><?php echo $this->lists['pos']; ?></td>
				</tr>
				<tr>
					<td valign="top" align="right" class="key"><?php echo JText::_('ORDERING'); ?>:</td>
					<td><?php echo $this->lists['ordering']; ?></td>
				</tr>
				<tr>
					<td valign="top" align="right" class="key"><?php echo JText::_('ACCESS_LEVEL'); ?>:</td>
					<td><?php echo $this->lists['access']; ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('TYPE'); ?>:</td>
					<td><?php echo $this->lists['type']; ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('DESCRIPTION'); ?>:</td>
					<td>
						<textarea class="inputbox" name="description" rows="5" cols="50"
						id="description"><?php echo $this->field->description; ?></textarea>
					</td>
				</tr>
				<?php
				if ($this->field->id) {
					?>
				<tr>
					<td class="key"><?php echo JText::_('ID'); ?>:</td>
					<td><strong><?php echo $this->field->id;?></strong></td>
				</tr>
				<?php
				}
				?>
			</table>
		</fieldset>
	</div>

	<div class="col width-40">
		<fieldset class="adminform">
			<legend> <?php echo JText::_('PARAMETERS'); ?></legend>
			<?php
				jimport('joomla.html.pane');
				$pane =& JPane::getInstance('sliders');

				echo $pane->startPane("menu-pane");
				echo $pane->startPanel(JText :: _('FIELD_PARAMETERS'), "param-page");
				echo $this->params->render();
				echo $pane->endPanel();
				echo $pane->endPane();
			?>
		</fieldset>
	</div>

	<div class="clr"></div>

	<input type="hidden" name="controller" value="field" />
	<input type="hidden" name="option" value="com_contactdirectory" />
	<input type="hidden" name="cid[]" value="<?php echo $this->field->id; ?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
