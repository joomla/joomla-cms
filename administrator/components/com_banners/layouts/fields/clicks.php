<?php
/**
 * @package     Joomla.Admin
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

extract($displayData);

JHtml::_('bootstrap.tooltip');

// Create the input for clicks.
?>
<input class="input-small" type="text" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>" readonly="readonly" />
<a class="btn" onclick="document.getElementById('<?php echo $id; ?>').value='0';">
	<span class="icon-refresh"></span><?php echo JText::_('COM_BANNERS_RESET_CLICKS'); ?>
</a>
