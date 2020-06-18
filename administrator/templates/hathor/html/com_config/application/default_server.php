<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   © 2006 Open Source Matters, Inc. <https://www.joomla.org/contribute-to-joomla.html>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="width-100">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_CONFIG_SERVER_SETTINGS'); ?></legend>
		<ul class="adminformlist">
			<?php foreach ($this->form->getFieldset('server') as $field): ?>
				<li>
					<?php echo $field->label; ?>
					<?php echo $field->input; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</fieldset>
</div>
