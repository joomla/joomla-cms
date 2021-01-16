<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$text = JText::_('JTOOLBAR_CANCEL');
?>
<a onclick="location.href='index.php?option=com_modules'" class="toolbar" title="<?php echo $text; ?>">
	<span class="icon-32-cancel"></span> <?php echo $text; ?>
</a>
