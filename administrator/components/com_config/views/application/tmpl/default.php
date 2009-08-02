<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

// Load tooltips behavior
JHtml::_('behavior.tooltip');
JHtml::_('behavior.switcher');

// Special treatment to get the submenu to work.
$this->document->setBuffer($this->loadTemplate('navigation'), 'modules', 'submenu');
$this->document->addScriptDeclaration("
	document.switcher = null;
	window.addEvent('domready', function(){
	 	toggler = $('submenu')
	  	element = $('config-document')
	  	if (element) {
	  		document.switcher = new JSwitcher(toggler, element, {cookieName: toggler.getAttribute('class')});
	  	}
	});
");

?>
<form action="<?php echo JRoute::_('index.php?option=com_config');?>" method="post" name="adminForm">
	<?php if ($this->ftp) : ?>
		<?php echo $this->loadTemplate('ftplogin'); ?>
	<?php endif; ?>
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
						<?php echo $this->loadTemplate('cookie'); ?>
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
							<legend><?php echo JText::_('User Settings'); ?></legend>
							<?php echo $this->usersParams->render('userparams'); ?>
						</fieldset>
						<fieldset class="adminform">
							<legend><?php echo JText::_('Media Settings'); ?>
			<span class="error hasTip" title="<?php echo JText::_('Warning');?>::<?php echo JText::_('WARNPATHCHANGES'); ?>">
				<?php echo $this->warningIcon(); ?>
			</span>
							</legend>
							<?php echo $this->mediaParams->render('mediaparams'); ?>
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

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
