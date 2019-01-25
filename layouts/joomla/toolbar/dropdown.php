<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$direction = Factory::getLanguage()->isRtl() ? 'dropdown-menu-right' : '';


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

HTMLHelper::_('webcomponent', 'vendor/joomla-custom-elements/joomla-dropdown.min.js', ['version' => 'auto', 'relative' => true]);
?>
<?php if ($hasButtons && trim($button) !== ''): ?>
	<div class="joomla-dropdown-container" role="group">
		<?php echo $button; ?>

		<?php if ($toggleSplit ?? true): ?>
			<button class="<?php echo $caretClass ?? ''; ?> dropdown-toggle dropdown-toggle-split"
				id="<?php echo $id; ?>">
				<span class="sr-only"><?php echo Text::_('JGLOBAL_TOGGLE_DROPDOWN'); ?></span>
			</button>
		<?php endif; ?>

		<?php if (trim($dropdownItems) !== ''): ?>
			<joomla-dropdown for="#<?php echo $id; ?>">
				<?php echo $dropdownItems; ?>
			</joomla-dropdown>
		<?php endif; ?>
	</div>
<?php endif; ?>
