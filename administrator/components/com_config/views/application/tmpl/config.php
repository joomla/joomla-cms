<?php /** $Id$ */ defined('_JEXEC') or die('Restricted access');
	JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
	JHtml::_('behavior.tooltip');
	JHtml::_('behavior.switcher');
?>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">
<?php if ($this->ftp) {
	echo $this->loadTemplate('ftp_auth');
} ?>
<div id="config-document">
	<div id="page-site">
		<table class="noshow">
			<tr>
				<td width="65%">
					<?php echo $this->loadTemplate('site'); ?>
					<?php echo $this->loadTemplate('metadata'); ?>
				</td>
				<td width="35%">
					<?php echo $this->loadTemplate('seo'); ?>
				</td>
			</tr>
		</table>
	</div>
	<div id="page-system">
		<table class="noshow">
			<tr>
				<td width="60%">
					<?php echo $this->loadTemplate('system'); ?>
					<fieldset class="adminform">
						<legend><?php echo JText::_( 'User Settings' ); ?></legend>
						<?php echo $this->userparams->render('userparams'); ?>
					</fieldset>
					<fieldset class="adminform">
						<legend><?php echo JText::_( 'Media Settings' ); ?></legend>
						<?php echo $this->mediaparams->render('mediaparams'); ?>
					</fieldset>
				</td>
				<td width="40%">
					<?php echo $this->loadTemplate('debug'); ?>
					<?php echo $this->loadTemplate('cache'); ?>
					<?php echo $this->loadTemplate('session'); ?>
				</td>
			</tr>
		</table>
	</div>
	<div id="page-server">
		<table class="noshow">
			<tr>
				<td width="60%">
					<?php echo $this->loadTemplate('server'); ?>
					<?php echo $this->loadTemplate('locale'); ?>
					<?php echo $this->loadTemplate('ftp'); ?>
				</td>
				<td width="40%">
					<?php echo $this->loadTemplate('database'); ?>
					<?php echo $this->loadTemplate('mail'); ?>
				</td>
			</tr>
		</table>
	</div>
</div>
<div class="clr"></div>

<input type="hidden" name="c" value="global" />
<input type="hidden" name="live_site" value="<?php echo isset($this->row->live_site) ? $this->row->live_site : ''; ?>" />
<input type="hidden" name="option" value="com_config" />
<input type="hidden" name="secret" value="<?php echo $this->row->secret; ?>" />
<input type="hidden" name="task" value="" />
<?php echo JHtml::_( 'form.token' ); ?>
</form>
