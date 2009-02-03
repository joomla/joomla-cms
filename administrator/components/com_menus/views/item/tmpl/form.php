<?php defined('_JEXEC') or die('Restricted access'); ?>

<script language="javascript" type="text/javascript">
<!--
function submitbutton(pressbutton) {
	var form = document.adminForm;
	var type = form.type.value;

	if (pressbutton == 'cancelItem') {
		submitform(pressbutton);
		return;
	}
	if ((type != "separator") && (trim(form.name.value) == "")){
		alert("<?php echo JText::_('Item must have a title', true); ?>");
	}
	<?php if($this->name == "Article Layout"){ ?>
	else if(document.getElementById('id_id').value == 0){
		alert("<?php echo JText::_('Please select an Article', true); ?>");
	} <?php } ?> else {
		submitform(pressbutton);
	}
}
//-->
</script>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">
	<table class="admintable" width="100%">
		<tr valign="top">
			<td width="60%">
				<!-- Menu Item Type Section -->
				<fieldset>
					<legend>
						<?php echo JText::_('Menu Item Type'); ?>
					</legend>
					<div style="float:right">
						<button type="button" onclick="location.href='index.php?option=com_menus&amp;task=type&amp;menutype=<?php echo $this->item->menutype;?><?php echo $this->item->expansion; ?>&amp;cid[]=<?php echo $this->item->id; ?>';">
							<?php echo JText::_('Change Type'); ?></button>
					</div>
					<h2><?php echo $this->name; ?></h2>
					<div>
						<?php echo $this->description; ?>
					</div>
				</fieldset>
				<!-- Menu Item Details Section -->
				<fieldset>
					<legend>
						<?php echo JText::_('Menu Item Details'); ?>
					</legend>
					<table width="100%">
						<?php if ($this->item->id) { ?>
						<tr>
							<td class="key" width="20%" align="right">
								<?php echo JText::_('ID'); ?>:
							</td>
							<td width="80%">
								<strong><?php echo $this->item->id; ?></strong>
							</td>
						</tr>
						<?php } ?>
						<tr>
							<td class="key" align="right">
								<?php echo JText::_('Title'); ?>:
							</td>
							<td>
								<input class="inputbox" type="text" name="name" size="50" maxlength="255" value="<?php echo $this->item->name; ?>" />
							</td>
						</tr>
						<tr>
							<td class="key" align="right">
								<?php echo JText::_('Alias'); ?>:
							</td>
							<td>
								<input class="inputbox" type="text" name="alias" size="50" maxlength="255" value="<?php echo $this->item->alias; ?>" />
							</td>
						</tr>
						<tr>
							<td class="key" align="right">
								<?php echo JText::_('Link'); ?>:
							</td>
							<td>
								<input class="inputbox" type="text" name="link" size="50" maxlength="255" value="<?php echo $this->item->link; ?>" <?php echo $this->lists->disabled;?> />
							</td>
						</tr>
						<tr>
							<td class="key" align="right">
								<?php echo JText::_('Display in'); ?>:
							</td>
							<td>
								<?php
									echo JHtml::_(
										'select.genericlist',
										$this->menutypes,
										'menutype',
										array(
											'format.depth' => 8,
											'list.attr' => 'class="inputbox" size="1"',
											'list.select' => $this->item->menutype,
											'option.key' => 'menutype',
											'option.text' => 'title',
										)
									);
								?>
							</td>
						</tr>
						<tr>
							<td class="key" align="right" valign="top">
								<?php echo JText::_('Parent Item'); ?>:
							</td>
							<td>
								<?php echo MenusHelper::Parent($this->item); ?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top" align="right">
								<?php echo JText::_('Published'); ?>:
							</td>
							<td>
								<?php echo $this->lists->published ?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top" align="right">
								<?php echo JText::_('Ordering'); ?>:
							</td>
							<td>
								<?php echo JHtml::_('menu.ordering', $this->item, $this->item->id); ?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top" align="right">
								<?php echo JText::_('Access Level'); ?>:
							</td>
							<td>
								<?php echo JHtml::_('list.accesslevel',  $this->item); ?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top" align="right">
								<?php echo JText::_('On Click, Open in'); ?>:
							</td>
							<td>
								<?php echo MenusHelper::Target($this->item); ?>
							</td>
						</tr>
					</table>
				</fieldset>
			</td>
			<!-- Menu Item Parameters Section -->
			<td width="40%">
				<?php
					echo $this->pane->startPane("menu-pane");
					echo $this->pane->startPanel(JText :: _('Parameters - Basic'), "param-page");
					echo $this->urlparams->render('urlparams');
					if($this->params->getNumParams()) :
						echo $this->params->render();
					endif;

					if(!count($this->params->getNumParams('params')) && !count($this->urlparams->getNumParams('urlparams'))) :
						echo "<div style=\"text-align: center; padding: 5px; \">".JText::_('There are no parameters for this item')."</div>";
					endif;
					echo $this->pane->endPanel();

					$groups = $this->params->getGroups();
					if(count($groups)) {
						foreach($groups as $groupname => $group) {
							if($groupname != 'state' && $groupname != '_default') {
								if($this->params->getNumParams($groupname)) {
									echo $this->pane->startPanel(JText :: _('Parameters - '.ucfirst($groupname)), $groupname.'-page');
									echo $this->params->render('params', $groupname);
									echo $this->pane->endPanel();
								}
							}
						}
					}
					if ($this->comp) :
						echo $this->pane->startPanel(JText :: _('Parameters - Component'), "component-page");
						echo $this->comp->render('params');
						echo $this->pane->endPanel();
					endif;

					if ($this->params->getNumParams('state')) :
						echo $this->pane->startPanel(JText :: _('Parameters - System'), "system-page");
						echo $this->params->render('params', 'state');
						echo $this->pane->endPanel();
					endif;
					echo $this->pane->endPane();
				?>
			</td>
		</tr>
	</table>

	<?php echo $this->item->linkfield; ?>

	<input type="hidden" name="option" value="com_menus" />
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="componentid" value="<?php echo $this->item->componentid; ?>" />
	<input type="hidden" name="type" value="<?php echo $this->item->type; ?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>