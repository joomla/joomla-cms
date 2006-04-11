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

<div class="login-form">
	<h1><jdoc:translate>Login</jdoc:translate></h1>
	<div class="form-block">
	<div class="inputlabel">
		<label for="username">
			<?php echo JText::_('Username'); ?>
		</label>
	</div>
				
	<div>
		<input name="username" id="username" type="text" class="inputbox" size="15" />
	</div>
				
	<div class="inputlabel">
		<label for="password">
			<?php echo JText::_('Password'); ?>
		</label>
	</div>
				
	<div>
		<input name="passwd" id="password" type="password" class="inputbox" size="15" />
	</div>
	<?php 
	if(JRequest::getVar('option') == 'login') {		
		if(!JSession::get('guest')) {
			echo "<p>";
			echo JText::_( 'LOGIN_INCORRECT' );
			echo  "</p>";
		}
	}	
	?>	
	<div class="inputlabel">
		<label for="lang">
			<?php echo JText::_('Language'); ?>
		</label>
	</div>
				
	<div>
		<?php echo $langs; ?>
	</div>
				
	<div class="flushstart" >
		<input type="submit" name="submit" class="button" value="<?php echo JText::_('Login'); ?>" />
		<input type="hidden" name="option" value="login" />
	</div>
</div>
</div>