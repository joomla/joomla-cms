<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$title = ($displayData['inputvalue'] ? JHtml::_('date', $displayData['value'], null, null) : '');
$value = htmlspecialchars($displayData['inputvalue'], ENT_COMPAT, 'UTF-8');

?>
<div<?php echo $displayData['div_class']; ?>>
	<input type="text" title="<?php echo $title; ?>" name="<?php echo $displayData['name']; ?>" id="<?php echo $displayData['id']; ?>" value="<?php echo $value; ?>" <?php echo $displayData['attribs']; ?> />
	<button type="button" class="btn" id="<?php echo $displayData['id']; ?>_img"<?php echo $displayData['btn_style']; ?>>
		<i class="icon-calendar"></i>
	</button>
</div>