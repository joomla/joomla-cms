<?php /** $Id$ */ defined('_JEXEC') or die('Restricted access');
	JHtml::_('behavior.mootools');
	JHtml::_('behavior.tooltip');
	JHtml::_('behavior.switcher');
	$doc = JFactory::getDocument();
	$doc->addScriptDeclaration(
	'window.addEvent("domready", function() {' .
	'	new JSwitcher($("submenu"), $("config-document"), {cookieName: $("submenu").get("class")})' .
	'});'
	);
?>
<form action="<?php echo JRoute::_('index.php?option=com_config'); ?>" method="post" name="adminForm">
<?php if ($this->ftp) {
	echo $this->loadTemplate('ftp_auth');
} ?>
<div id="config-document">
	<div id="page-site">
		<table class="noshow">
			<tr>
				<td width="65%">
					<?php echo $this->renderGroup('site'); ?>
					<?php echo $this->renderGroup('metadata'); ?>
				</td>
				<td width="35%">
					<?php echo $this->renderGroup('seo'); ?>
				</td>
			</tr>
		</table>
	</div>
	<div id="page-system">
		<table class="noshow">
			<tr>
				<td width="60%">
					<?php echo $this->renderGroup('system'); ?>
				</td>
				<td width="40%">
					<?php echo $this->renderGroup('debug'); ?>
					<?php echo $this->renderGroup('cache'); ?>
					<?php if ($this->state->get('memcache') == true)
						echo $this->renderGroup('memcache');
					?>
					<?php echo $this->renderGroup('session'); ?>
				</td>
			</tr>
		</table>
	</div>	
	<div id="page-server">
		<table class="noshow">
			<tr>
				<td width="60%">
					<?php echo $this->renderGroup('server'); ?>
					<?php echo $this->renderGroup('locale'); ?>
					<?php echo $this->renderGroup('ftp'); ?>
				</td>
				<td width="40%">
					<?php echo $this->renderGroup('database'); ?>
					<?php echo $this->renderGroup('mail'); ?>
				</td>
			</tr>
		</table>
	</div>

</div>
<div class="clr"></div>

<?php echo JHtml::_('form.token'); ?>
<input type="hidden" name="option" value="com_config" />
<input type="hidden" name="task" value="" />

</form>
