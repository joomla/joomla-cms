<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$text = JText::_('JTOOLBAR_NEW');
?>
<a href="javascript:void(0)" onclick="location.href='index.php?option=com_modules&amp;view=select'" class="toolbar">
	<span class="icon-32-new"></span>
	<?php echo $text; ?>
</a>
