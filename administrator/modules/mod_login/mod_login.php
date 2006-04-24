<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

//$browserLang = JLanguageHelper::detectLanguage();
// forced to default
$browserLang = null;

$languages = array();
$languages = JLanguageHelper::createLanguageList($browserLang );
array_unshift( $languages, mosHTML::makeOption( '', JText::_( 'Default' ) ) );
$langs = mosHTML::selectList( $languages, 'lang', ' class="inputbox"', 'value', 'text', $browserLang );
?>

<form action="index.php" method="post" name="loginForm" id="loginForm">
	<p>
		<label for="username"><?php echo JText::_('Username'); ?></label><br />
		<input name="username" id="username" type="text" class="inputbox" size="15" />
	</p>

	<p>
		<label for="password"><?php echo JText::_('Password'); ?></label><br />
		<input name="passwd" id="password" type="password" class="inputbox" size="15" />
	</p>
	<?php
	if(JRequest::getVar('option') == 'login') {
		if(!JSession::get('guest')) {
			echo "<p>";
			echo JText::_( 'LOGIN_INCORRECT' );
			echo  "</p>";
		}
	}
	?>
	<p>
		<label for="lang"><?php echo JText::_('Language'); ?></label><br />
		<?php echo $langs; ?>
	</p>
	<div class="button1-left">
		<div class="next">
			<a onclick="loginForm.submit();">
				<jdoc:translate>Login</jdoc:translate>
			</a>
		</div>
	</div>
	<input type="submit" name="dosubmit" value="submit" style="visibility:hidden;" />
	<input type="hidden" name="option" value="login" />
</form>