<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.language.helper');
//$browserLang = JLanguageHelper::detectLanguage();
// forced to default
$browserLang = null;
$lang =& JFactory::getLanguage();

$languages = array();
$languages = JLanguageHelper::createLanguageList($browserLang );
array_unshift( $languages, JHTML::_('select.option',  '', JText::_( 'Default' ) ) );
$langs = JHTML::_('select.genericlist',   $languages, 'lang', ' class="inputbox"', 'value', 'text', $browserLang );
?>
<?php if(JPluginHelper::isEnabled('authentication', 'openid')) : ?>
	<?php JHTML::_('script', 'openid.js'); ?>
<?php endif; ?>
<form action="<?php echo JRoute::_( 'index.php', true, $params->get('usesecure')); ?>" method="post" name="login" id="form-login" style="clear: both;">
	<p id="form-login-username">
		<label for="username"><?php echo JText::_('Username'); ?></label>
		<input name="username" id="username" type="text" class="inputbox" size="15" />
	</p>

	<p id="form-login-password">
		<label for="password"><?php echo JText::_('Password'); ?></label>
		<input name="passwd" id="password" type="password" class="inputbox" size="15" />
	</p>
	<?php
	if($error = JError::getError(true)) {
		echo '<p id="login-error-message">';
		echo $error->get('message');
		echo '<p>';
	}
	?>
	<p id="form-login-lang" style="clear: both;">
		<label for="lang"><?php echo JText::_('Language'); ?></label>
		<?php echo $langs; ?>
	</p>
	<div class="button_holder">
	<div class="button1">
		<div class="next">
			<a onclick="login.submit();">
				<?php echo JText::_( 'Login' ); ?>
			</a>
			<input type="submit" style="border: 0; padding: 0; margin: 0; width: 0px; height: 0px;" value="<?php echo JText::_( 'Login' ); ?>" />
		</div>
	</div>
	</div>
	<div class="clr"></div>
	<input type="hidden" name="option" value="com_login" />
	<input type="hidden" name="task" value="login" />
	<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
