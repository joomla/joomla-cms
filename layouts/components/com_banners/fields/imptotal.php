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

// Create the input for total impressions.
?>
<input type="text" name="<?php echo $name; ?>" id="<?php echo $id; ?>" size="9" value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>" readonly="readonly" class="validate-numeric text_area" onclick="if (document.getElementById('<?php echo $id; ?>_unlimited').checked) document.getElementById('<?php echo $id; ?>').value='';"/>
<fieldset class="checkboxes impunlimited"><input id="<?php echo $id; ?>_unlimited" type="checkbox" <?php echo $checked; ?> onclick="if (document.getElementById('<?php echo $id; ?>_unlimited').checked) document.getElementById('<?php echo $id; ?>').value='';" />
	<label for="<?php echo $id; ?>_unlimited" id="jform-imp" type="text"><?php echo JText::_('COM_BANNERS_UNLIMITED'); ?></label>
</fieldset>


<a class="btn" onclick="document.getElementById('<?php echo $id; ?>').value='0';">
	<span class="icon-refresh"></span><?php echo JText::_('COM_BANNERS_RESET_IMPMADE'); ?>
</a>
