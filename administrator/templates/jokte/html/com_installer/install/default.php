<?php
/**
 * @version		$Id: default.php 16457 2010-04-26 00:07:16Z eddieajau $
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.5
 */

// no direct access
defined('_JEXEC') or die;
?>
<?php if ($this->showMessage) : ?>
<?php echo $this->loadTemplate('message'); ?>
<?php endif; ?>
<?php echo $this->loadTemplate('form'); ?>
