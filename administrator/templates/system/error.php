<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title><?php echo $this->error->getCode(); ?> - <?php echo htmlspecialchars($this->error->getMessage()); ?></title>
	<link rel="stylesheet" href="templates/system/css/error.css" type="text/css" />
</head>
<body>
	<table width="550" align="center" class="outline">
	<tr>
		<td align="center">
			<h1><?php echo $this->error->getCode() ?> - <?php echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED') ?></h1>
			<p><?php echo htmlspecialchars($this->error->getMessage()); ?></p>
		</td>
	</tr>
	<tr>
		<td width="39%" align="center">
			<p><?php echo htmlspecialchars($this->error->getMessage()); ?></p>
			<p><a href="index.php"><?php echo JText::_('JGLOBAL_TPL_CPANEL_LINK_TEXT') ?></a></p>
			<p>
				<?php if ($this->debug) :
					echo $this->renderBacktrace();
				endif; ?>
			</p>
		</td>
	</tr>
	</table>
</body>
</html>
