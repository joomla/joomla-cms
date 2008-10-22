<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php JHTML::_('behavior.tooltip'); ?>

<?php
	JToolBarHelper::title( JText::_( 'Plugin' ) .': <small><small>[' .JText::_('Edit'). ']</small></small>', 'plugin.png' );
	JToolBarHelper::save();
	JToolBarHelper::apply();
	JToolBarHelper::cancel( 'cancel', 'Close' );
	JToolBarHelper::help( 'screen.plugins.edit' );
?>

<?php
	// clean item data
	JFilterOutput::objectHTMLSafe( $this->plugin, ENT_QUOTES, '' );
?>

<?php
	$this->plugin->nameA = '';
	if ( $this->plugin->extensionid ) {
		$row->nameA = '<small><small>[ '. $this->plugin->name .' ]</small></small>';
	}
?>
<script language="javascript" type="text/javascript">
	function submitbutton(pressbutton) {
		if (pressbutton == "cancel") {
			submitform(pressbutton);
			return;
		}
		// validation
		var form = document.adminForm;
		if (form.name.value == "") {
			alert( "<?php echo JText::_( 'Plugin must have a name', true ); ?>" );
		} else if (form.element.value == "") {
			alert( "<?php echo JText::_( 'Plugin must have a filename', true ); ?>" );
		} else {
			submitform(pressbutton);
		}
	}
</script>

<form action="index.php" method="post" name="adminForm">
<div class="col width-60">
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'Details' ); ?></legend>
	<table class="admintable">
		<tr>
			<td width="100" class="key">
				<label for="name">
					<?php echo JText::_( 'Name' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="name" id="name" size="35" value="<?php echo $this->plugin->name; ?>" />
			</td>
		</tr>
		<tr>
			<td valign="top" class="key">
				<?php echo JText::_( 'Enabled' ); ?>:
			</td>
			<td>
				<?php echo $this->lists['enabled']; ?>
			</td>
		</tr>
		<tr>
			<td valign="top" class="key">
				<label for="folder">
					<?php echo JText::_( 'Type' ); ?>:
				</label>
			</td>
			<td>
				<?php echo $this->plugin->folder; ?>
			</td>
		</tr>
		<tr>
			<td valign="top" class="key">
				<label for="element">
					<?php echo JText::_( 'Plugin file' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="element" id="element" size="35" value="<?php echo $this->plugin->element; ?>" />.php
			</td>
		</tr>
		<tr>
			<td valign="top" class="key">
				<label for="access">
					<?php echo JText::_( 'Access Level' ); ?>:
				</label>
			</td>
			<td>
				<?php echo $this->lists['access']; ?>
			</td>
		</tr>
		<tr>
			<td valign="top" class="key">
				<?php echo JText::_( 'Order' ); ?>:
			</td>
			<td>
				<?php echo $this->lists['ordering']; ?>
			</td>
		</tr>
		<tr>
			<td valign="top" class="key">
				<?php echo JText::_( 'Description' ); ?>:
			</td>
			<td>
				<?php echo JText::_( $this->plugin->description ); ?>
			</td>
		</tr>
		</table>
	</fieldset>
</div>
<div class="col width-40">
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'Parameters' ); ?></legend>
	<?php
		jimport('joomla.html.pane');
		$pane =& JPane::getInstance('sliders');
		echo $pane->startPane("plugin-pane");
		echo $pane->startPanel(JText :: _('Plugin Parameters'), "param-page");
		if($output = $this->params->render('params')) :
			echo $output;
		else :
			echo "<div style=\"text-align: center; padding: 5px; \">".JText::_('There are no parameters for this item')."</div>";
		endif;
		echo $pane->endPanel();

		if ($this->params->getNumParams('advanced')) {
			echo $pane->startPanel(JText :: _('Advanced Parameters'), "advanced-page");
			if($output = $this->params->render('params', 'advanced')) :
				echo $output;
			else :
				echo "<div  style=\"text-align: center; padding: 5px; \">".JText::_('There are no advanced parameters for this item')."</div>";
			endif;
			echo $pane->endPanel();
		}

		if ($this->params->getNumParams('legacy')) {
			echo $pane->startPanel(JText :: _('Legacy Parameters'), "legacy-page");
			if($output = $this->params->render('params', 'legacy')) :
				echo $output;
			else :
				echo "<div  style=\"text-align: center; padding: 5px; \">".JText::_('There are no legacy parameters for this item')."</div>";
			endif;
			echo $pane->endPanel();
		}
		echo $pane->endPane();
	?>
	</fieldset>
</div>
<div class="clr"></div>

	<input type="hidden" name="option" value="com_plugins" />
	<input type="hidden" name="extensionid" value="<?php echo $this->plugin->extensionid; ?>" />
	<input type="hidden" name="cid[]" value="<?php echo $this->plugin->extensionid; ?>" />
	<input type="hidden" name="client" value="<?php echo $this->plugin->client_id; ?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>