<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$data = $displayData;
$icon = "icon-menu-2";
$sort = '';
$caption = '';
$selected = '';
$id = '';

if ($data->order === $data->selected) :
	$icon = $data->orderIcon;
	$sort = $data->direction === 'asc' ? 'ascending' : 'descending';
	$caption = !empty($data->title) ? Text::_($data->title) . ' - ' . $sort : Text::_('JGRID_HEADING_ID');
	$selected = ' selected';
	$id = 'id="sorted"';
endif;
?>

<a href="" onclick="return false;" class="js-stools-column-order<?php echo $selected; ?> js-stools-button-sort"
	<?php echo $id; ?>
	data-order="<?php echo $data->order; ?>"
	data-direction="<?php echo strtoupper($data->direction); ?>"
	data-caption="<?php echo $caption; ?>"
	<?php if (!empty($sort)) : ?>
		data-sort="<?php echo $sort; ?>"
	<?php endif; ?>>
	<?php // The following statement has been concatenated purposely to remove whitespace. ?>
	<?php // Please leave as is. ?>
	<?php if (!empty($data->title)) : ?><span><?php echo Text::_($data->title); ?></span><?php endif; ?><span
		class="ml-1 <?php echo $icon; ?>"
		aria-hidden="true"></span>
	<span class="sr-only">
		<?php echo Text::_('JGLOBAL_SORT_BY'); ?>
		<?php echo (!empty($data->title)) ? Text::_($data->title) : Text::_('JGRID_HEADING_ORDERING'); ?>
	</span>
</a>
