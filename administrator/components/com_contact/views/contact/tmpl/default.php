<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

	JHtml::_('behavior.tooltip');

	// Set toolbar items for the page
	$edit		= JRequest::getVar('edit',true);
	$text = !$edit ? JText::_('NEW') : JText::_('EDIT');
	JToolBarHelper::title(  JText::_('CONTACT').': <small><small>[ ' . $text.' ]</small></small>');
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

		// do contact validation
		if (form.name.value == ""){
			alert("<?php echo JText::_('CONTACT_ITEM_MUST_HAVE_A_NAME', true); ?>");
		}
		if (form.categories.value ==""){
			alert("<?php echo JText::_('CATEGORY_SELECTED_VALIDATION', true); ?>");
		}
		<?php
			foreach ($this->fields as $this->field) {
				if ($this->field->params->get('required')){;
					$alias = $this->field->alias;
					$title = $this->field->title;
					echo "else if (form.$alias.value == \"\") {"
							."alert(\"The $title is required.\"); "
							."}";
				}
			}
		?>

		else {
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
				<td width="100" align="right" class="key">
					<label for="name"><?php echo JText::_('NAME'); ?>:</label>
				</td>
				<td>
					<input class="text_area" type="text" name="name" id="name" size="32" maxlength="250" value="<?php echo $this->contact->name;?>" />
				</td>
			</tr>
			<tr>
				<td width="100" align="right" class="key">
					<label for="alias"><?php echo JText::_('ALIAS'); ?>:</label>
				</td>
				<td>
					<input class="text_area" type="text" name="alias" id="alias" size="32" maxlength="250" value="<?php echo $this->contact->alias;?>" />
				</td>
			</tr>
			<tr>
				<td valign="top" align="right" class="key">
					<label for="published"><?php echo JText::_('PUBLISHED'); ?>:</label>
				</td>
				<td>
					<?php echo $this->lists['published']; ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="user_id">
						<?php echo JText::_('LINKED_USER'); ?>:
					</label>
				</td>
				<td >
					<?php echo $this->lists['user_id'];?>
				</td>
			</tr>
			<tr>
				<td valign="top" align="right" class="key">
					<label for="access"><?php echo JText::_('ACCESS_LEVEL'); ?>:</label>
				</td>
				<td>
					<?php echo $this->lists['access']; ?>
				</td>
			</tr>
			<?php
				if ($this->contact->id) {
					?>
					<tr>
						<td class="key">
							<label>
								<?php echo JText::_('ID'); ?>:
							</label>
						</td>
						<td>
							<strong><?php echo $this->contact->id;?></strong>
						</td>
					</tr>
					<?php
				}
			?>
		</table>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo JText::_('CATEGORIES'); ?></legend>

		<table class="admintable">
			<tr>
				<td valign="top" align="right" class="key">
					<label for="categories"><?php echo JText::_('CATEGORIES'); ?>:</label>
				</td>
				<td>
					<?php echo $this->lists['category']; ?>
				</td>
			</tr>
			<?php for($i=0; $i<count($this->categories); $i++) { ?>
			<tr>
				<td valign="top" align="right" class="key"><?php //echo JText::sprintf('CONTACT_PARAMETERS_DESCRIPTION', strtolower(JText::_($this->field->title))); ?>
					<label for="ordering"><?php echo JText::sprintf('ORDERING_CATEGORY', $this->categories[$i]->title); ?>:</label>
				</td>
				<td>
					<?php echo $this->lists['ordering'.$i]; ?>
				</td>
			</tr>
			<?php } ?>
		</table>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo JText::_('INFORMATION'); ?></legend>

		<table class="admintable">
			<?php for($i=0; $i<count($this->fields); $i++) {
					$field = &$this->fields[$i];
					if ($field->params->get('required')){
							$star = '* ';
					} else{
						$star ='';
					}
			?>
			<tr>
				<td valign="top" align="right" class="key">
					<label for="<?php echo $field->title; ?>">
						<?php echo JText::_($star.$field->title); ?>:
					</label>
				</td>
				<td>
					<?php
						if ($field->type == 'text' || $field->type == 'email' || $field->type == 'url'){
							echo  '<input class="text_area" type="text" name="fields['.$field->alias.']"
										id="'.$field->alias.'" size="32" maxlength="250"
										value="'.$field->data.'" />';
						}else if ($field->type == 'textarea'){
							echo '<textarea class="inputbox" name="fields['.$field->alias.']"
									rows="5" cols="50" id="'.$field->alias.'">'.
									$field->data.'</textarea>';
						}else if ($field->type == 'editor'){
							$editor =& JFactory::getEditor();
							echo $editor->display('fields['.$field->alias.']', $field->data, '100%', '100%', '50', '5');
						}else if ($field->type == 'image'){
							echo JHtml::_('list.images',  'fields['.$field->alias.']', $field->data);
						}

					?>
				</td>
			</tr>
			<?php } ?>
		</table>
	</fieldset>
</div>
<div class="col width-40">
	<fieldset class="adminform">
		<legend><?php echo JText::_('PARAMETERS'); ?></legend>
		<?php
			jimport('joomla.html.pane');
			$pane =& JPane::getInstance('sliders');

			echo $pane->startPane("menu-pane");
			echo $pane->startPanel(JText :: _('CONTACT_PARAMETERS'), "param-page");
			echo $this->params->render();
			$i = 0;
		?>
		<table class="paramlist admintable" cellspacing="1" width="100%">
			<tbody>
			<?php foreach($this->fields as $this->field){ ?>
				<tr>
					<td class="paramlist_key" width="40%">
						<span class="editlinktip">
							<label id="paramsshow_contact-lbl" for="paramsshow_contact" class="hasTip" title="<?php echo JText::_($this->field->title).'::'.JText::sprintf('CONTACT_PARAMETERS_DESCRIPTION', strtolower(JText::_($this->field->title)));?>">
								<?php echo JText::_($this->field->title); ?>
							</label>
						</span>
					</td>
					<td class="paramlist_value">
						<?php echo $this->lists['showContact'.$i]; ?>
					</td>
				</tr>
				<tr>
			<?php $i++; } ?>
			</tbody>
		</table>
		<?php
			echo $pane->endPanel();
			echo $pane->startPanel(JText :: _('DIRECTORY_PARAMETERS'), "param-page");
			echo $this->params->render('params', 'directory');
			$i = 0;
		?>
		<table class="paramlist admintable" cellspacing="1" width="100%">
			<tbody>
			<?php foreach($this->fields as $this->field){ ?>
				<tr>
					<td class="paramlist_key" width="40%">
						<span class="editlinktip">
							<label id="paramsshow_directory-lbl" for="paramsshow_directory" class="hasTip" title="<?php echo JText::_($this->field->title).'::'.JText::sprintf('DIRECTORY_PARAMETERS_DESCRIPTION', strtolower(JText::_($this->field->title)));?>">
								<?php echo JText::_($this->field->title); ?>
							</label>
						</span>
					</td>
					<td class="paramlist_value">
						<?php echo $this->lists['showDirectory'.$i]; ?>
					</td>
				</tr>
				<tr>
			<?php $i++; } ?>
			</tbody>
		</table>
		<?php
			echo $pane->endPanel();
			echo $pane->startPanel(JText :: _('EMAIL_PARAMETERS'), "param-page");
			echo $this->params->render('params', 'email');
			echo $pane->endPanel();
			echo $pane->endPane();
		?>
	</fieldset>
</div>

<div class="clr"></div>

	<input type="hidden" name="controller" value="contact" />
	<input type="hidden" name="option" value="com_contact" />
	<input type="hidden" name="cid[]" value="<?php echo $this->contact->id; ?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>