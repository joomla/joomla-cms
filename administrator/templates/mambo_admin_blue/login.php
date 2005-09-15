<?php
/**
* @version $Id: login.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

$languages = array();
$languages = mosLanguageFactory::buildLanguageList( 'admin', $mosConfig_lang );
array_unshift( $languages, mosHTML::makeOption( '', $_LANG->_( 'Default' ) ) );
$lists['langs'] = mosHTML::selectList( $languages, 'lang', ' class="inputbox" id="language"', 'value', 'text', '' );

$handle = mosGetParam( $_REQUEST, 'handle', NULL );
$tstart = mosProfiler::getmicrotime();

// xml prolog
echo '<?xml version="1.0" encoding="'. $_LANG->iso() .'"?' .'>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo $_LANG->isoCode();?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_LANG->iso(); ?>" />
<title><?php echo $mosConfig_sitename; ?> - <?php echo $_LANG->_( 'Administration' ); ?> [Mambo]</title>
<link rel="stylesheet" href="templates/mambo_admin_blue/css/admin_login<?php echo $_LANG->rtl() ? '_rtl': ''; ?>.css" type="text/css" />
<link rel="shortcut icon" href="../images/favicon.ico" />
<meta name="robots" content="noindex, nofollow" />
<script language="javascript" type="text/javascript">
	function setFocus() {
		document.loginForm.username.select();
		document.loginForm.username.focus();
	}
</script>
</head>
<body onload="setFocus();">

<div id="wrapper">
	<div id="header">
	   <div id="mambo">
		   <img src="templates/mambo_admin_blue/images/header_text.png" alt="<?php echo $_LANG->_( 'Joomla! Logo' ); ?>" />
	   </div>
	</div>
</div>
<div id="ctr" align="center">

	<div class="login">
		<div class="login-form">
			<img src="templates/mambo_admin_blue/images/login.gif" alt="<?php echo $_LANG->_( 'Login' ); ?>" />
			<form action="index.php" method="post" name="loginForm" id="loginForm">
			<div class="form-block">
				<?php if ($handle) {
				echo '<div class="timeout">' . $_LANG->_( 'Session_Timeout' ) . '</div>';
				} ?>
				<div class="inputlabel">
					<label for="username">
						<?php echo $_LANG->_( 'Username' ); ?>
					</label>
				</div>
				<div>
					<input name="username" id="username" type="text" class="inputbox" size="15" />
				</div>
				<div class="inputlabel">
					<label for="password">
						<?php echo $_LANG->_( 'Password' ); ?>
					</label>
				</div>
				<div>
					<input name="passwd" id="password" type="password" class="inputbox" size="15" />
				</div>
				<div class="inputlabel">
					<label for="language">
						<?php echo $_LANG->_( 'Language' ); ?>
					</label>
				</div>
				<div>
					<?php echo $lists['langs']; ?>
				</div>
				<div class="flushstart" align="left">
					<input type="submit" name="submit" class="button" value="<?php echo $_LANG->_( 'Login' ); ?>" />
				</div>
			</div>
			<?php if (isset ($handle)) { ?>
			<input type="hidden" name="handle" value="<?php echo $handle; ?>">
			<?php } ?>
			</form>
		</div>
		<div class="login-text">
			<div class="ctr">
				<img src="templates/mambo_admin_blue/images/security.png" width="64" height="64" alt="<?php echo $_LANG->_( 'security' ); ?>" />
			</div>
			<p><?php echo $_LANG->_( 'Welcome to Mambo' ); ?>!</p>
			<p><a href="<?php echo $mosConfig_live_site;?>">
						<?php echo $mosConfig_sitename;?></a></p>
			<p><?php echo $_LANG->_( 'descUseValidLogin' ); ?></p>
		</div>
		<div class="clr"></div>
	</div>
</div>

<div id="break"></div>

<noscript>
<?php echo $_LANG->_( 'errorNoJavascript' ); ?>
</noscript>

<div class="footer" align="center">
	<?php
	mosFS::load( 'includes/footer.php' );
	?>
</div>

</body>
</html>
