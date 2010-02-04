<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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
		var toggler = document.id('submenu')
		var element = document.id('config-document')
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
		<div id="page-site" class="tab">
			<div class="noshow">
				<div class="width-65 fltlft">
					<?php echo $this->loadTemplate('site'); ?>
					<?php echo $this->loadTemplate('metadata'); ?>
				</div>
				<div class="width-35 fltrt">
					<?php echo $this->loadTemplate('seo'); ?>
					<?php echo $this->loadTemplate('cookie'); ?>
				</div>
			</div>
		</div>
		<div id="page-system" class="tab">
			<div class="noshow">
				<div class="width-60 fltlft">
					<?php echo $this->loadTemplate('system'); ?>
				</div>
				<div class="width-40 fltrt">
					<?php echo $this->loadTemplate('debug'); ?>
					<?php echo $this->loadTemplate('cache'); ?>
					<?php echo $this->loadTemplate('session'); ?>
				</div>
			</div>
		</div>
		<div id="page-server" class="tab">
			<div class="noshow">
				<div class="width-60 fltlft">
					<?php echo $this->loadTemplate('server'); ?>
					<?php echo $this->loadTemplate('locale'); ?>
					<?php echo $this->loadTemplate('ftp'); ?>
				</div>
				<div class="width-40 fltrt">
					<?php echo $this->loadTemplate('database'); ?>
					<?php echo $this->loadTemplate('mail'); ?>
				</div>
			</div>
		</div>
		<div id="page-permissions" class="tab">
			<div class="noshow">
				<?php echo $this->loadTemplate('permissions'); ?>
			</div>
		</div>
	</div>
	<div class="clr"></div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
