<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

$languages = array();
$languages = JLanguageHelper::createLanguageList( $mosConfig_lang );
array_unshift( $languages, mosHTML::makeOption( '', JText::_( 'Default' ) ) );
$lists['langs'] = mosHTML::selectList( $languages, 'lang', ' class="inputbox" id="language"', 'value', 'text', '' );

$handle = mosGetParam( $_REQUEST, 'handle', NULL );

$lang = $mainframe->getLanguage();

$tstart = mosProfiler::getmicrotime();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $lang->getTag();?>" lang="<?php echo $lang->getTag();?>" dir="<?php echo $lang->isRTL() ? 'rtl' : 'ltr'; ?>">
<head>
<title><?php echo $mosConfig_sitename; ?> - <?php echo JText::_( 'Administration' ); ?> [Joomla]</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css">
@import url(templates/joomla_admin/css/admin_login<?php echo $lang->isRTL() ? '_rtl' : ''; ?>.css);
</style>
<script language="javascript" type="text/javascript">
	function setFocus() {
		document.loginForm.username.select();
		document.loginForm.username.focus();
	}
</script>
<link rel="shortcut icon" href="<?php echo JURL_SITE .'/images/favicon.ico';?>" />
</head>
<body onload="setFocus();">
<div id="wrapper">
	<div id="header">
		<div id="joomla"><?php echo JText::_( 'Administration' ); ?></div>
		<div id="version"><?php echo JText::_( 'Version#' ); ?></div>
	</div>
</div>
<div id="ctr" align="center">
	<div class="login">
		<div class="login-form">
			<img src="templates/joomla_admin/images/login.gif" alt="<?php echo JText::_( 'Login' ); ?>" />
			<form action="index.php" method="post" name="loginForm" id="loginForm">
			<div class="form-block">
				<?php if ($handle) {
				echo '<div class="timeout">' . JText::_( 'Session_Timeout' ) . '</div>';
				} ?>
				<div class="inputlabel">
					<label for="username">
						<?php echo JText::_( 'Username' ); ?>
					</label>
				</div>
				<div>
					<input name="username" id="username" type="text" class="inputbox" size="15" />
				</div>
				<div class="inputlabel">
					<label for="password">
						<?php echo JText::_( 'Password' ); ?>
					</label>
				</div>
				<div>
					<input name="passwd" id="password" type="password" class="inputbox" size="15" />
				</div>
				<div class="inputlabel">
					<label for="language">
						<?php echo JText::_( 'Language' ); ?>
					</label>
				</div>
				<div>
					<?php echo $lists['langs']; ?>
				</div>
				<div class="flushstart" >
					<input type="submit" name="submit" class="button" value="<?php echo JText::_( 'Login' ); ?>" />
				</div>
			</div>
			</form>
		</div>
		<div class="login-text">
			<div class="ctr"><img src="templates/joomla_admin/images/security.png" width="64" height="64" alt="<?php echo JText::_( 'security' ); ?>" /></div>
			<p><?php echo JText::_( 'Welcome to Joomla!' ); ?></p>
			<p><?php echo JText::_( 'DESCUSEVALIDLOGIN' ); ?></p>
		</div>
		<div class="clr"></div>
	</div>
</div>
<div id="break"></div>
<noscript>
<?php echo JText::_( 'WARNJAVASCRIPT' ); ?>
</noscript>
<div class="footer" align="center">
	<div align="center">
		<?php echo $_VERSION->URL; ?>
	</div>
</div>
</body>
</html>