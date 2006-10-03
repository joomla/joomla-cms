<script language="javascript" type="text/javascript">
<!-- 
function submitbutton(pressbutton) {
	var form = document.adminForm;
	var type = form.type.value;
	if (pressbutton == 'cancelItem') {
	
		submitform( pressbutton );
		return;
	}
	if ( (type != "separator") && (trim( form.name.value ) == "") ){
		alert( "<?php echo JText::_( 'Item must have a name', true ); ?>" );
	} else {
		submitform( pressbutton );
	}
}
//-->
</script>
<form action="index.php" method="post" name="adminForm">
	<table class="admintable" width="100%">
		<tr valign="top">
			<td width="60%">
				<!-- Menu Item Type Section -->
				<fieldset>
					<legend>
						<?php echo JText::_( 'Menu Item Type' ); ?>
					</legend>
					<div style="float:right">
						<button type="button" onclick="location.href='index.php?option=com_menus&amp;task=type&amp;menutype=<?php echo $this->item->menutype;?><?php echo $this->item->expansion; ?>&amp;cid[]=<?php echo $this->item->id; ?>';">
							<?php echo JText::_( 'Change Type' ); ?></button>
					</div>
					<h2><?php echo $this->name; ?></h2>
					<div>
						<?php echo $this->description; ?>
					</div>
				</fieldset>
				<!-- Menu Item Details Section -->
				<fieldset>
					<legend>
						<?php echo JText::_( 'Menu Item Details' ); ?>
					</legend>
					<table width="100%">
						<?php if ($this->item->id) { ?>
						<tr>
							<td class="key" width="20%" align="right">
								<?php echo JText::_( 'ID' ); ?>:
							</td>
							<td width="80%">
								<strong><?php echo $this->item->id; ?></strong>
							</td>
						</tr>
						<?php } ?>
						<tr>
							<td class="key" align="right">
								<?php echo JText::_( 'Name' ); ?>:
							</td>
							<td>
								<input class="inputbox" type="text" name="name" size="50" maxlength="255" value="<?php echo $this->item->name; ?>" />
							</td>
						</tr>
						<tr>
							<td class="key" align="right">
								<?php echo JText::_( 'Link' ); ?>:
							</td>
							<td>
								<input class="inputbox" type="text" name="link" size="50" maxlength="255" value="<?php echo $this->item->link; ?>" <?php echo $this->lists->disabled;?> />
							</td>
						</tr>
						<tr>
							<td class="key" align="right">
								<?php echo JText::_( 'Display in' ); ?>:
							</td>
							<td>
								<?php echo mosHTML::selectList( $this->menutypes, 'menutype', 'class="inputbox" size="1"', 'menutype', 'title', $this->item->menutype );?>
							</td>
						</tr>
						<tr>
							<td class="key" align="right" valign="top">
								<?php echo JText::_( 'Parent Item' ); ?>:
							</td>
							<td>
								<?php echo JMenuHelper::Parent( $this->item ); ?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top" align="right">
								<?php echo JText::_( 'Published' ); ?>:
							</td>
							<td>
								<?php echo $this->lists->published ?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top" align="right">
								<?php echo JText::_( 'Ordering' ); ?>:
							</td>
							<td>
								<?php echo mosAdminMenus::Ordering( $this->item, $this->item->id ); ?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top" align="right">
								<?php echo JText::_( 'Access Level' ); ?>:
							</td>
							<td>
								<?php echo mosAdminMenus::Access( $this->item ); ?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top" align="right">
								<?php echo JText::_( 'On Click, Open in' ); ?>:
							</td>
							<td>
								<?php echo JMenuHelper::Target( $this->item ); ?>
							</td>
						</tr>
					</table>
				</fieldset>
			</td>
			<!-- Menu Item Parameters Section -->
			<td width="40%">
				<?php	
					$this->pane->startPane("menu-pane");
					$this->pane->startPanel(JText :: _('Menu Item Parameters'), "param-page");
					echo $this->urlparams->render('urlparams');
					echo $this->params->render('params');
					$this->pane->endPanel();
					$this->pane->startPanel(JText :: _('Advanced Parameters'), "advanced-page");
					echo $this->advanced->render('params');
					$this->pane->endPanel();
					$this->pane->endPane();
				?>
			</td>
		</tr>
	</table>

	<?php echo $this->item->linkfield; ?>

	<input type="hidden" name="option" value="com_menus" />
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="componentid" value="<?php echo $this->item->componentid; ?>" />
	<input type="hidden" name="menutype" value="<?php echo $this->item->menutype; ?>" />
	<input type="hidden" name="type" value="<?php echo $this->item->type; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="hidemainmenu" value="0" />
</form>
