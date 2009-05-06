<?php /** $Id$ */ defined('_JEXEC') or die('Restricted access'); ?>
<?php $groups = $this->params->getGroups();
if (count($groups) > 1): 
$this->document->addScriptDeclaration('
window.addEvent("domready", function() {
	new JSwitcher($("submenu"), $("component-config-document"), {
		cookieName: $("submenu").get("class"),
		elementSelector: "fieldset.configuration-group"
	});
});
');
endif;
?>
<div id="component-config-document">
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">
	<fieldset>
		<div style="float: right">
			<button type="button" onclick="submitbutton('component.save');window.top.setTimeout('window.parent.SqueezeBox.close();', 700);">
				<?php echo JText::_('Save'); ?></button>
			<button type="button" onclick="window.parent.SqueezeBox.close();">
				<?php echo JText::_('Cancel'); ?></button>
		</div>
		<div class="configuration">
			<?php echo JText::_($this->component->name) ?>
		</div>
	</fieldset>
	<?php if (count($groups) > 1): ?>
	<div class="submenu-box">
		<div class="submenu-pad">
			<ul id="submenu" class="configuration-<?php echo $this->component->name; ?>">
				<?php foreach ($groups as $group => $count): ?>
				<li><a id="group-<?php echo $group; ?>"><?php echo JText::_($group == '_default' ? 'Configuration' : $group); ?></a></li>
				<?php endforeach; ?>
			</ul>
			<div class="clr"></div>
		</div>
	</div>
	<div class="clr"></div>
	
	<?php foreach ($groups as $group => $count): ?>
	<fieldset id="page-group-<?php echo $group; ?>" class="configuration-group">
		<legend><?php echo JText::_($group == '_default' ? 'Configuration' : $group); ?></legend>
		<?php echo $this->params->render('params', $group); ?>
	</fieldset>
	<?php endforeach; ?>
	
	<?php else: ?>
	<fieldset>
		<legend><?php echo JText::_('Configuration'); ?></legend>
		<?php echo $this->params->render();?>
	</fieldset>
	<?php endif; ?>

	<input type="hidden" name="id" value="<?php echo $this->component->id;?>" />
	<input type="hidden" name="component" value="<?php echo $this->component->option;?>" />

	<input type="hidden" name="option" value="com_config" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>
</div>