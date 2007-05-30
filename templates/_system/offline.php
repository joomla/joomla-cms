<?php
/**
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="head" />
	<link rel="stylesheet" href="templates/_system/css/offline.css" type="text/css" />
	<link rel="stylesheet" href="templates/_system/css/general.css" type="text/css" />
</head>
<body>
<jdoc:include type="message" />
	<div id="frame" class="outline">
		<img src="images/joomla_logo_black.jpg" alt="Joomla! Logo" align="middle" />
		<h1>
			<?php echo $mainframe->getCfg('sitename'); ?>
		</h1>
	<p>
		<?php echo $mainframe->getCfg('offline_message'); ?>
	</p>
	<form action="index.php" method="post" name="login" id="frmlogin">
	<fieldset class="input">
		<p>
			<label for="username"><?php echo JText::_('Username') ?></label><br />
			<input name="username" id="username" type="text" class="inputbox" alt="username" size="18" />
		</p>
		<p>
			<label for="passwd"><?php echo JText::_('Password') ?></label><br />
			<input type="password" name="passwd" class="inputbox" size="18" alt="password" />
		</p>
		<p>
			<label for="remember"><?php echo JText::_('Remember me') ?></label>
			<input type="checkbox" name="remember" class="inputbox" value="yes" alt="Remember Me" />
			<input type="submit" name="Submit" class="button" value="<?php echo JText::_('LOGIN') ?>" />
		</p>
	</fieldset>
	<input type="hidden" name="option" value="com_user" />
	<input type="hidden" name="task" value="login" />
	</form>
	</div>
</body>
</html>