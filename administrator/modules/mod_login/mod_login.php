<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.language.helper');
//$browserLang = JLanguageHelper::detectLanguage();
// forced to default
$browserLang = null;
$lang = &JFactory::getLanguage();

$languages = array();
$languages = JLanguageHelper::createLanguageList($browserLang);
array_unshift($languages, JHtml::_('select.option',  '', JText::_('Default')));
$langs = JHtml::_('select.genericlist',   $languages, 'lang', ' class="inputbox"', 'value', 'text', $browserLang);
?>
<?php if (JPluginHelper::isEnabled('authentication', 'openid')) :
		$lang->load('plg_authentication_openid', JPATH_ADMINISTRATOR);
		$langScript = 	'var JLanguage = {};'.
						' JLanguage.WHAT_IS_OPENID = \''.JText::_('WHAT_IS_OPENID').'\';'.
						' JLanguage.LOGIN_WITH_OPENID = \''.JText::_('LOGIN_WITH_OPENID').'\';'.
						' JLanguage.NORMAL_LOGIN = \''.JText::_('NORMAL_LOGIN').'\';'.
						' var modlogin = 1;';
		$document = &JFactory::getDocument();
		$document->addScriptDeclaration($langScript);
		JHtml::_('script', 'openid.js');
endif; ?>
<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" name="login" id="form-login" style="clear: both;">
	<p id="form-login-username">
		<label for="mod-login-username"><?php echo JText::_('Username'); ?></label>
		<input name="username" id="mod-login-username" type="text" class="inputbox" size="15" />
	</p>

	<p id="form-login-password">
		<label for="mod-login-password"><?php echo JText::_('Password'); ?></label>
		<input name="passwd" id="mod-login-password" type="password" class="inputbox" size="15" />
	</p>
	<?php
	if ($error = JError::getError(true)) {
		echo '<p id="login-error-message">';
		echo $error->get('message');
		echo '<p>';
	}
	?>
	<p id="form-login-language">
		<label for="lang"><?php echo JText::_('Language'); ?></label>
		<?php echo $langs; ?>
	</p>
	<div class="button-holder">
	<div class="button1">
		<div class="next">
			<a href="#" onclick="login.submit();">
				<?php echo JText::_('Log_in'); ?></a>

		</div>
	</div>
	</div>
	<div class="clr"></div>
	<input type="submit" class="login-submit" value="<?php echo JText::_('Log_in'); ?>" />
	<input type="hidden" name="option" value="com_login" />
	<input type="hidden" name="task" value="login" />
	<?php echo JHtml::_('form.token'); ?>
</form>
