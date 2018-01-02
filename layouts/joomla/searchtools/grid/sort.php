<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$data = $displayData;

$title = htmlspecialchars(Text::_($data->tip ?: $data->title));
HTMLHelper::_('bootstrap.popover');
?>
<a href="#" onclick="return false;" class="js-stools-column-order hasPopover"
   data-order="<?php echo $data->order; ?>" data-direction="<?php echo strtoupper($data->direction); ?>" data-name="<?php echo Text::_($data->title); ?>"
   title="<?php echo $title; ?>" data-content="<?php echo htmlspecialchars(Text::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN')); ?>" data-placement="top">
<?php if (!empty($data->icon)) : ?>
	<span class="<?php echo $data->icon; ?>" aria-hidden="true"></span>
<?php endif; ?>
<?php if (!empty($data->title)) : ?>
	<?php echo Text::_($data->title); ?>
<?php endif; ?>
<?php if ($data->order == $data->selected) : ?>
	<span class="<?php echo $data->orderIcon; ?>" aria-hidden="true"></span>
<?php endif; ?>
</a>
