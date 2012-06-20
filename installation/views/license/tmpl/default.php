<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<form action="index.php" method="post" id="adminForm" class="form-validate form-horizontal">
	<div id="installer">
		<fieldset>
			<legend><?php echo JText::_('INSTL_GNU_GPL_LICENSE'); ?></legend>
			<div class="control-group">
				<label for="" class="control-label">
					<?php echo JText::_('INSTL_LICENSE'); ?>
				</label>
				<div class="controls">
					<iframe src="gpl.html" class="thumbnail span9 license" height="300" marginwidth="25" scrolling="auto"></iframe>
				</div>
			</div>
			<div class="form-actions">
			<?php if ($this->document->direction == 'ltr') : ?>
					<span class="prev"><a class="btn" href="index.php?view=preinstall" onclick="return Install.goToPage('preinstall');" rel="prev" title="<?php echo JText::_('JPREVIOUS'); ?>"><?php echo JText::_('JPREVIOUS'); ?></a></span> 
					<span class="next"><a class="btn btn-primary" href="index.php?view=database" onclick="return Install.goToPage('database');" rel="next" title="<?php echo JText::_('JNEXT'); ?>"><?php echo JText::_('JNEXT'); ?></a></span>
			<?php elseif ($this->document->direction == 'rtl') : ?>
					<span class="prev"><a class="btn btn-primary" href="index.php?view=database" onclick="return Install.goToPage('database');" rel="next" title="<?php echo JText::_('JNEXT'); ?>"><?php echo JText::_('JNEXT'); ?></a></span> 
					<span class="next"><a class="btn" href="index.php?view=preinstall" onclick="return Install.goToPage('preinstall');" rel="prev" title="<?php echo JText::_('JPREVIOUS'); ?>"><?php echo JText::_('JPREVIOUS'); ?></a></span>
			<?php endif; ?>
			</div>
		</fieldset>
	</div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
