<jdoc:comment>
@copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
@license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
Joomla! is free software. This version may have been modified pursuant
to the GNU General Public License, and as distributed it includes or
is derivative of works licensed under the GNU General Public License or
other free or open source software licenses.
See COPYRIGHT.php for copyright notices and details.
</jdoc:comment>

<?php
global $mosConfig_lang;

$languages = array();
$languages = JLanguageHelper::createLanguageList( $mosConfig_lang );
array_unshift( $languages, mosHTML::makeOption( '', JText::_( 'Default' ) ) );
$lists['langs'] = mosHTML::selectList( $languages, 'lang', ' class="inputbox"', 'value', 'text', '' );

$lang = $mainframe->getLanguage();

$tstart = mosProfiler::getmicrotime();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{LANG_TAG}" lang="{LANG_TAG}" dir="{LANG_DIR}" >
<head>
<jdoc:placeholder type="head" />
<jdoc:tmpl name="isRTL" varscope="login.php" type="condition" conditionvar="LANG_ISRTL">
	<jdoc:sub condition="1">
		<link href="templates/{TEMPLATE}/css/login_rtl.css" rel="stylesheet" type="text/css" />
	</jdoc:sub>
	<jdoc:sub condition="0">
		<link href="templates/{TEMPLATE}/css/login.css" rel="stylesheet" type="text/css" />
	</jdoc:sub>
</jdoc:tmpl>
<script language="javascript" type="text/javascript">
	function setFocus() {
		document.loginForm.username.select();
		document.loginForm.username.focus();
	}
</script>
</head>
<body onload="setFocus();">

<form action="index.php" method="post" name="loginForm" id="loginForm">

<div id="wrapper">
	<div id="header">
		<div id="joomla">
			<jdoc:translate>Administration</jdoc:translate>
		</div>
		
		<div id="version">
			<jdoc:translate>Version#</jdoc:translate>
		</div>
	</div>
</div>

<div id="ctr" align="center">
	<div class="login">
		<div class="login-form">
			<h1><jdoc:translate>Login</jdoc:translate></h1>
			<div class="form-block">
				<div class="inputlabel">
					<label for="username">
						<jdoc:translate>Username</jdoc:translate>
					</label>
				</div>
				
				<div>
					<input name="username" id="username" type="text" class="inputbox" size="15" />
				</div>
				
				<div class="inputlabel">
					<label for="password">
						<jdoc:translate>Password</jdoc:translate>
					</label>
				</div>
				
				<div>
					<input name="passwd" id="password" type="password" class="inputbox" size="15" />
				</div>
				
				<div class="inputlabel">
					<label for="lang">
						<jdoc:translate>Language</jdoc:translate>
					</label>
				</div>
				
				<div>
					<?php echo $lists['langs']; ?>
				</div>
				
				<div class="flushstart" >
					<input type="submit" name="submit" class="button" value="<jdoc:translate>Login</jdoc:translate>" />
					<input type="hidden" name="option" value="login" />
				</div>
			</div>
		</div>
		
		<div class="login-text">
			<div class="ctr">
				<img src="templates/joomla_admin/images/security.png" width="64" height="64" alt="<jdoc:translate>security</jdoc:translate>" />
			</div>
			
			<p><jdoc:translate>Welcome to Joomla!</jdoc:translate></p>
			<p><jdoc:translate>DESCUSEVALIDLOGIN</jdoc:translate></p>
			<p>&nbsp;</p>
			<p>
				<a href="<?php echo $mainframe->getSiteURL(); ?>"><jdoc:translate>Return to site Home Page</jdoc:translate></a>
			</p>			
		</div>
		
		<div class="clr"></div>
	</div>
</div>

<div id="break"></div>

<noscript>
	<jdoc:translate key="WARNJAVASCRIPT" />
</noscript>

<div class="footer" align="center">
	<div align="center">
		<?php echo $_VERSION->URL; ?>
	</div>
</div>

</form>

</body>
</html>