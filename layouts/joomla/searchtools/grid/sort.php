<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$data = $displayData;
$icon = "icon-menu-2";
$sort = "none";
$caption = '';
$selected = '';
$id = '';

if ($data->order === $data->selected) :
	$icon = $data->orderIcon;
	$sort = ($data->direction === "asc" ? "ascending" : "descending");
	$caption = !empty($data->title) ? Text::_($data->title) . ' - ' . $sort : '';
	$selected = " selected";
	$id = "id=\"sorted\"";
endif;
?>

<a href="#" onclick="return false;" class="js-stools-column-order<?php echo $selected; ?>"
    <?php echo $id; ?>
    data-order="<?php echo $data->order; ?>" 
    data-direction="<?php echo strtoupper($data->direction); ?>" 
    <?php if (!empty($data->title)) : ?>
    data-name="<?php echo Text::_($data->title); ?>"   
    <?php endif; ?>
    <?php if (!empty($caption)) : ?>
    data-caption="<?php echo $caption; ?>"
    <?php endif; ?>
    data-sort="<?php echo $sort; ?>">
	<span class="<?php echo $icon; ?>" aria-hidden="true"></span>
	<?php if (!empty($data->title)) : ?>
		<?php echo Text::_($data->title); ?>
	<?php endif; ?>
</a>

