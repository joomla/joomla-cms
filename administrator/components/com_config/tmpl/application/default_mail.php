<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use \Joomla\CMS\Layout\LayoutHelper;

defined('_JEXEC') or die;

\JHtml::_('webcomponent', ['joomla-field-send-mail' => 'system/fields/joomla-sendtestmail.min.js'], ['version' => 'auto', 'relative' => true]);

// Load JavaScript message titles
JText::script('ERROR');
JText::script('WARNING');
JText::script('NOTICE');
JText::script('MESSAGE');

// Add strings for JavaScript error translations.
JText::script('JLIB_JS_AJAX_ERROR_CONNECTION_ABORT');
JText::script('JLIB_JS_AJAX_ERROR_NO_CONTENT');
JText::script('JLIB_JS_AJAX_ERROR_OTHER');
JText::script('JLIB_JS_AJAX_ERROR_PARSE');
JText::script('JLIB_JS_AJAX_ERROR_TIMEOUT');

// Ajax request data.
$ajaxUri = JRoute::_('index.php?option=com_config&task=application.sendtestmail&format=json&' . Joomla\CMS\Session\Session::getFormToken() . '=1');

$this->name = JText::_('COM_CONFIG_MAIL_SETTINGS');
$this->fieldsname = 'mail';
?>

<joomla-field-sendtestmail url="<?php echo $ajaxUri; ?>">
	<?php echo LayoutHelper::render('joomla.content.options_default', $this); ?>

	<button class="btn btn-primary" type="button" id="sendtestmail">
		<span><?php echo JText::_('COM_CONFIG_SENDMAIL_ACTION_BUTTON'); ?></span>
	</button>
</joomla-field-sendtestmail>
