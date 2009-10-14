<?php defined('_JEXEC') or die; ?>

<?php JHTML::_('behavior.tooltip'); ?>


<?php
	// clean item data
	JFilterOutput::objectHTMLSafe( $this->plugin, ENT_QUOTES, '' );
?>

<?php
	$this->plugin->nameA = '';
	if ( $this->plugin->extension_id ) {
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
<div class="width-60 fltlft">
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'Details' ); ?></legend>
	
		<label id="jform_plugname-lbl" class="required" for="jform_plugname"><?php echo JText::_( 'Name' ); ?>:</label>
		<input id="jform_plugname" class="inputbox" type="text" size="35" value="<?php echo $this->plugin->name; ?>" name="jform[plugname]"/>
		
				
		<label id="jform_plugenabled-lbl" class="" for="jform_plugenabled"><?php echo JText::_( 'JEnabled' ); ?>:</label>
		<fieldset id="jform_plugenabled" class="radio">
			<?php echo $this->lists['enabled']; ?>
		</fieldset>
		
		<label id="jform_plugtype-lbl" class="" for="jform_plugtype"><?php echo JText::_( 'Type' ); ?>:</label>
		<input id="jform_plugtype" class="readonly" type="text" readonly="readonly" size="16" value="<?php echo $this->plugin->folder; ?>" name="jform[plugtype]"/>
		
		<label id="jform_plugfile-lbl" class="" for="jform_plugfile"><?php echo JText::_( 'Plugin file' ); ?>:</label>
		<input id="jform_plugfile" class="readonly" type="text" readonly="readonly" size="25" value="<?php echo $this->plugin->element; ?>.php" name="jform[plugfile]"/>
		
		<label id="jform_plugaccess-lbl" class="" for="jform_plugaccess"><?php echo JText::_( 'Access Level' ); ?>:</label>
		<?php echo $this->lists['access']; ?>
		
		<label id="jform_plugorder-lbl" class="" for="jform_plugorder"><?php echo JText::_( 'Order' ); ?>:</label>
		<?php echo $this->lists['ordering']; ?>
		
		<label id="jform_plugdesc-lbl" class="" for="jform_plugdesc"><?php echo JText::_( 'Description' ); ?>:</label>
		<p class="jform_desc"><?php echo JText::_( $this->plugin->description ); ?></p>
	
	</fieldset>
</div>
<div class="width-40 fltrt">
	<?php
		jimport('joomla.html.pane');
		$pane = &JPane::getInstance('sliders');
		echo $pane->startPane('plugin-pane');
		echo $pane->startPanel(JText :: _('Plugin Parameters'), 'param-page');
		if($output = $this->params->render('params')) :
			echo "<fieldset class='panelform-legacy'>".$output."</fieldset>";
		else :
			echo "<div class=\"noparams-notice\">".JText::_('There are no parameters for this item')."</div>";
		endif;
		echo $pane->endPanel();

		if ($this->params->getNumParams('advanced')) {
			echo $pane->startPanel(JText :: _('Advanced Parameters'), "advanced-page");
			if($output = $this->params->render('params', 'advanced')) :
				echo "<fieldset class='panelform-legacy'>".$output."</fieldset>";
			else :
				echo "<div class=\"noparams-notice\">".JText::_('There are no advanced parameters for this item')."</div>";
			endif;
			echo $pane->endPanel();
		}
		echo $pane->endPane();
	?>
</div>
<div class="clr"></div>

	<input type="hidden" name="option" value="com_plugins" />
	<input type="hidden" name="extension_id" value="<?php echo $this->plugin->extension_id; ?>" />
	<input type="hidden" name="cid[]" value="<?php echo $this->plugin->extension_id; ?>" />
	<input type="hidden" name="client" value="<?php echo $this->plugin->client_id; ?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>