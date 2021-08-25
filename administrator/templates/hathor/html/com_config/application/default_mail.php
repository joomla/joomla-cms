<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('jquery.token');
JHtml::_('script', 'system/sendtestmail.js', array('version' => 'auto', 'relative' => true));

JFactory::getDocument()->addScriptDeclaration('
	var sendtestmail_url = "' . addslashes(JUri::base()) . 'index.php?option=com_config&task=config.sendtestmail.application&format=json";
 ');
?>
<div class="width-100">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_CONFIG_MAIL_SETTINGS'); ?></legend>
		<ul class="adminformlist">
			<?php foreach ($this->form->getFieldset('mail') as $field): ?>
				<li>
					<?php echo $field->label; ?>
					<?php echo $field->input; ?>
				</li>
			<?php endforeach; ?>
		</ul>
		<button type="button" class="btn btn-small" id="sendtestmail">
			<span><?php echo JText::_('COM_CONFIG_SENDMAIL_ACTION_BUTTON'); ?></span>
		</button>
	</fieldset>
</div>
