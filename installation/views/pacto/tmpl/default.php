<?php
/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div id="step">
	<div class="far-right">
<?php if ($this->document->direction == 'ltr') : ?>
		<div class="button1-right"><div class="prev"><a href="index.php?view=license" onclick="return Install.goToPage('license');" rel="prev" title="<?php echo JText::_('JPREVIOUS'); ?>"><?php echo JText::_('JPREVIOUS'); ?></a></div></div>
		<div class="button1-left"><div class="next"><a href="index.php?view=database" onclick="return Install.goToPage('database');" rel="next" title="<?php echo JText::_('JNEXT'); ?>"><?php echo JText::_('JNEXT'); ?></a></div></div>
<?php elseif ($this->document->direction == 'rtl') : ?>
		<div class="button1-right"><div class="prev"><a href="index.php?view=database" onclick="return Install.goToPage('database');" rel="next" title="<?php echo JText::_('JNEXT'); ?>"><?php echo JText::_('JNEXT'); ?></a></div></div>
		<div class="button1-left"><div class="next"><a href="index.php?view=license" onclick="return Install.goToPage('license');" rel="prev" title="<?php echo JText::_('JPREVIOUS'); ?>"><?php echo JText::_('JPREVIOUS'); ?></a></div></div>
<?php endif; ?>
	</div>
	<h2><?php echo JText::_('INSTL_CONTRATO'); ?></h2>
</div>
<form action="index.php" method="post" id="adminForm" class="form-validate">
	<div id="installer">
		<div class="m">
			<h3><?php echo JText::_('INSTL_CONTRATO_SOCIAL'); ?></h3>
			<iframe src="contrasoc.html" class="license" marginwidth="25" scrolling="auto"></iframe>
		</div>
	</div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
