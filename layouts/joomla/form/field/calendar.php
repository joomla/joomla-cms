<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Layout variables
 * ---------------------
 *
 * @var  string   $div_class A div CSS class
 * @var  string   $inputvalue The date value
 * @var  string   $fdate Formatted date
 * @var  string   $name The field name
 * @var  string   $id The field id
 * @var  string   $attribs Attributes of the input HTML tag
 * @var  string   $btn_style Button inline style
 */
extract($displayData);

?>
<div <?php echo $div_class; ?>>
	<input type="text" title="<?php echo $fdate; ?>"
		name="<?php echo $name; ?>" id="<?php echo $id; ?>"
		value="<?php echo htmlspecialchars($inputvalue, ENT_COMPAT, 'UTF-8'); ?>" <?php echo $attribs; ?>/>
	 <button type="button" class="btn" id="<?php echo $id; ?>_img" <?php echo $btn_style; ?>>
		<span class="icon-calendar"></span>
	</button>'
</div>
