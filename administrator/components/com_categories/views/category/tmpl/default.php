<?php defined('_JEXEC') or die; ?>

<?php JHtml::_('behavior.tooltip'); ?>

<?php
	JRequest::setVar('hidemainmenu', 1);
	$cid = JRequest::getVar('cid', array(0), '', 'array');

	$edit	= JRequest::getVar('edit',true);
	$text = ($edit ? JText::_('Edit') : JText::_('New'));

	JToolBarHelper::title(JText::_('Category') .': <small><small>[ '. $text.' ]</small></small>', 'categories.png');
	JToolBarHelper::save();
	JToolBarHelper::apply();
	if ($edit) {
		// for existing articles the button is renamed `close`
		JToolBarHelper::cancel('cancel', 'Close');
	} else {
		JToolBarHelper::cancel();
	}
	JToolBarHelper::help('screen.categories.edit');

	$editor = &JFactory::getEditor();

	if ($this->redirect == 'content') {
		$component = 'Content';
	} else {
		$component = ucfirst(substr($this->redirect, 4));
		if ($this->redirect == 'com_contact_details') {
			$component = 'Contact';
		}
	}

	JFilterOutput::objectHTMLSafe($this->row, ENT_QUOTES, 'description');
	$cparams = JComponentHelper::getParams ('com_media');
?>
<script language="javascript" type="text/javascript">
function submitbutton(pressbutton) {
	var form = document.adminForm;
	if (pressbutton == 'cancel') {
		submitform(pressbutton);
		return;
	}

	if (pressbutton == 'menulink') {
		if (form.menuselect.value == "") {
			alert("<?php echo JText::_('Please select a Menu', true); ?>");
			return;
		} else if (form.link_type.value == "") {
			alert("<?php echo JText::_('Please select a menu type', true); ?>");
			return;
		} else if (form.link_name.value == "") {
			alert("<?php echo JText::_('Please enter a Name for this menu item', true); ?>");
			return;
		}
	}

	if (form.title.value == "") {
		alert("<?php echo JText::_('Category must have a title', true); ?>");
	} else {
		<?php
		echo $editor->save('description') ; ?>
		submitform(pressbutton);
	}
}
</script>


<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">

<div class="col width-60">
	<fieldset class="adminform">
		<legend><?php echo JText::_('Details'); ?></legend>

			<table class="admintable">
			<tr>
				<td class="key">
					<label for="title">
						<?php echo JText::_('Title'); ?>:
					</label>
				</td>
				<td colspan="2">
					<input class="text_area" type="text" name="title" id="title" value="<?php echo $this->row->title; ?>" size="50" maxlength="50" title="<?php echo JText::_('A long name to be displayed in headings'); ?>" />
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="alias">
						<?php echo JText::_('Alias'); ?>:
					</label>
				</td>
				<td colspan="2">
					<input class="text_area" type="text" name="alias" id="alias" value="<?php echo $this->row->alias; ?>" size="50" maxlength="255" title="<?php echo JText::_('A short name to appear in menus'); ?>" />
				</td>
			</tr>
			<tr>
				<td width="120" class="key">
					<?php echo JText::_('Published'); ?>:
				</td>
				<td>
					<?php echo $this->lists['published']; ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo JText::_('Parent'); ?>:
				</td>
				<td>
					<?php echo JHtml::_('list.category', 'parent', $this->row->extension, 'com_content.view', $this->filter, (int) $this->row->parent, '', 1, 1); ?>
				</td>
			</tr>
			<tr>
				<td valign="top" class="key">
					<label for="access">
						<?php echo JText::_('Access Level'); ?>:
					</label>
				</td>
				<td>
					<?php echo $this->lists['access']; ?>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
				<script language="javascript" type="text/javascript">
				if (document.forms.adminForm.image.options.value!=''){
					jsimg='../<?echo $cparams->get('image_path'); ?>/' + getSelectedValue('adminForm', 'image');
				} else {
					jsimg='../images/M_images/blank.png';
				}
				document.write('<img src=' + jsimg + ' name="imagelib" width="80" height="80" border="2" alt="<?php echo JText::_('Preview', true); ?>" />');
				</script>
				</td>
			</tr>

		</table>
	</fieldset>

	<fieldset class="adminform">
		<legend><?php echo JText::_('Description'); ?></legend>

		<table class="admintable">
			<tr>
				<td valign="top" colspan="3">
					<?php
					// parameters : areaname, content, width, height, cols, rows, show xtd buttons
					echo $editor->display('description',  $this->row->description, '550', '300', '60', '20', array('pagebreak', 'readmore')) ;
					?>
				</td>
			</tr>
			</table>
	</fieldset>
</div>
<div class="col width-40">
	<fieldset class="adminform">
		<legend><?php echo JText::_('Parameters'); ?></legend>

		<?php
			jimport('joomla.html.pane');
			$groups = $this->params->getGroups();
			if (count($groups) && $groups) {
				$pane = &JPane::getInstance('sliders');
				echo $pane->startPane("menu-pane");
				foreach($groups as $groupname => $group) {
					if ($groupname == '_default') {
						$title = 'Standard';
					} else {
						$title = ucfirst($groupname);
					}
					if ($this->params->getNumParams($groupname)) {
						echo $pane->startPanel(JText :: _('Parameters - '.$title), $groupname.'-page');
						echo $this->params->render('params', $groupname);
						echo $pane->endPanel();
					}

				}
				echo $pane->endPane();
			} else {
				echo '<div style="text-align: center; padding: 5px; ">'.JText::_('There are no parameters for this item').'</div>';
			}
			?>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_categories" />
<input type="hidden" name="oldtitle" value="<?php echo $this->row->title ; ?>" />
<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
<input type="hidden" name="extension" value="<?php echo $this->row->extension; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="redirect" value="<?php echo $this->redirect; ?>" />
<?php echo JHtml::_('form.token'); ?>
</form>
