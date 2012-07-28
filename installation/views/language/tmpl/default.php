<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div id="step">
	<div class="buttons">
		<div class="button"><a href="#" onclick="Install.submitform();" rel="next" title="<?php echo JText::_('JNext'); ?>"><?php echo JText::_('JNext'); ?></a></div>
	</div>
	<h2><?php echo JText::_('INSTL_LANGUAGE_TITLE'); ?></h2>
</div>

<form action="index.php" method="post" id="adminForm" class="form-validate">
	<div id="installer">
		<h3><?php echo JText::_('INSTL_SELECT_LANGUAGE_TITLE'); ?></h3>
		<div class="install-text">
			<?php echo JText::_('INSTL_SELECT_LANGUAGE_DESC'); ?>
		</div>
		<div class="install-body">
			<fieldset>
				<?php echo $this->form->getInput('language'); ?>
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="task" value="setup.setlanguage" />
	<?php echo JHtml::_('form.token'); ?>
</form>
