<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::script('system/sendtestmail.js', false, true);
JFactory::getDocument()->addScriptDeclaration('
	var sendtestmail_url = "' . addslashes(JUri::base()) . 'index.php?option=com_config&task=config.sendtestmail.application&format=json&' . JSession::getFormToken() . '=1";
 ');

$this->name = JText::_('COM_CONFIG_MAIL_SETTINGS');
$this->fieldsname = 'mail';
echo JLayoutHelper::render('joomla.content.options_default', $this);

echo '<button type="button" class="btn btn-small" id="sendtestmail">
		<span>' . JText::_('COM_CONFIG_SENDMAIL_ACTION_BUTTON') . '</span>
	</button>';
