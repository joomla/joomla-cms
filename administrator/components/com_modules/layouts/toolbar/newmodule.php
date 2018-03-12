<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$text = JText::_('JTOOLBAR_NEW');
?>
<button onclick="location.href='index.php?option=com_modules&amp;view=select'" class="btn btn-small btn-success" title="<?php echo $text; ?>">
	<span class="icon-plus icon-white" aria-hidden="true"></span>
	<?php echo $text; ?>
</button>
