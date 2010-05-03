<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	templates.hathor
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

// no direct access
defined('_JEXEC') or die;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
	<link rel="stylesheet" href="../system/css/error.css" type="text/css" />
</head>
<body class="errors">
	<div>
		<h1>
			<?php echo $this->error->getCode() ?> - <?php echo JText::_('AN_ERROR_HAS_OCCURRED') ?>
		</h1>
	</div>
	<div>
		<p><?php echo $this->error->getMessage(); ?></p>
		<p>
			<?php if ($this->debug) :
				echo $this->renderBacktrace();
			endif; ?>
		</p>
	</div>
</body>
</html>