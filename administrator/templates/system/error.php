<?php
/**
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
	<link rel="stylesheet" href="templates/system/css/error.css" type="text/css" />
</head>
<body>
	<table width="550" align="center" class="outline">
	<tr>
		<td align="center">
			<h1>
				<?php echo $this->error->get('code') ?> - <?php echo JText::_('An error has occurred') ?>
			</h1>
		</td>
	</tr>
	<tr>
		<td width="39%" align="center">
			<p><?php echo $this->error->get('message'); ?></p>
			<p>
				<?php if($this->debug) :
					print_r($this->error->get('info'));
					echo '<hr />';
					echo $this->renderBacktrace();
				endif; ?>
			</p>
		</td>
	</tr>
	</table>
</body>
</html>
