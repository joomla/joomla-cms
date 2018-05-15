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

/**
 * @var  string  $id
 * @var  string  $onclick
 * @var  string  $class
 * @var  string  $text
 * @var  string  $btnClass
 * @var  string  $tagName
 * @var  string  $htmlAttributes
 * @var  string  $hasButtons
 * @var  string  $button
 * @var  string  $dropdownItems
 * @var  string  $caretClass
 * @var  string  $toggleSplit
 */
extract($displayData, EXTR_OVERWRITE);
?>
<?php if ($hasButtons && trim($button) !== ''): ?>
	<div id="<?php echo $id; ?>" class="btn-group dropdown-<?php echo $name ?? ''; ?>" role="group" aria-label="Button Dropdown">
		<?php echo $button; ?>

		<?php if ($toggleSplit ?? true): ?>
			<button type="button" class="<?php echo $caretClass ?? ''; ?> dropdown-toggle dropdown-toggle-split"
				data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<span class="sr-only">Toggle Dropdown</span>
			</button>
		<?php endif; ?>

		<?php if (trim($dropdownItems) !== ''): ?>
			<div class="dropdown-menu dropdown-menu-right">
				<?php echo $dropdownItems; ?>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>
