<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
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
