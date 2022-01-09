<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php if ($this->showMessage) : ?>
<?php echo $this->loadTemplate('message'); ?>
<?php endif; ?>
<?php echo $this->loadTemplate('form'); ?>
