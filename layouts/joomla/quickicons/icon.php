<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;

$id      = empty($displayData['id']) ? '' : (' id="' . $displayData['id'] . '"');
$target  = empty($displayData['target']) ? '' : (' target="' . $displayData['target'] . '"');
$onclick = empty($displayData['onclick']) ? '' : (' onclick="' . $displayData['onclick'] . '"');

if (isset($displayData['ajaxurl'])) {
	$size = 'small';
	$dataUrl = 'data-url="' . $displayData['ajaxurl'] . '"';
} else {
	$size = 'big';
	$dataUrl = '';
}

// The title for the link (a11y)
$title = empty($displayData['title']) ? '' : (' title="' . $this->escape($displayData['title']) . '"');

// The information
$text = empty($displayData['text']) ? '' : ('<span class="j-links-link">' . $displayData['text'] . '</span>');

$tmp = [];

// Set id and class pulse for update icons
if ($id && ($displayData['id'] === 'plg_quickicon_joomlaupdate'
	|| $displayData['id'] === 'plg_quickicon_extensionupdate'
	|| $displayData['id'] === 'plg_quickicon_privacycheck'
	|| $displayData['id'] === 'plg_quickicon_overridecheck'
	|| !empty($displayData['class'])))
{
	$tmp[] = 'pulse';
}

// Add the button class
if (!empty($displayData['class']))
{
	$tmp[] = $this->escape($displayData['class']);
}

// Make the class string
$class = !empty($tmp) ? 'class="' . implode(' ', array_unique($tmp)) . '"' : '';

?>
<?php // If it is a button with two links: make it a list
	if (isset($displayData['linkadd'])): ?>
		<li class="quickicon-group col mb-3">
		<ul class="list-unstyled">
			<li class="quickicon">
	<?php else: ?>		
		<li class="quickicon quickicon-single col mb-3">
	<?php endif; ?>	

		<a <?php echo $id . $class; ?> href="<?php echo $displayData['link']; ?>"<?php echo $target . $onclick . $title; ?>>
			<?php if (isset($displayData['image'])): ?>
				<div class="quickicon-icon d-flex align-items-end <?php echo $size ?>">
					<div class="<?php echo $displayData['image']; ?>" aria-hidden="true"></div>
				</div>
			<?php endif; ?>
			<?php if (isset($displayData['ajaxurl'])) : ?>
				<div class="quickicon-amount" <?php echo $dataUrl ?> aria-hidden="true">
					<span class="fa fa-spinner" aria-hidden="true"></span>
				</div>
				<div class="quickicon-sr-desc sr-only"></div>
			<?php endif; ?>
			<?php // Name indicates the component
			if (isset($displayData['name'])): ?>
				<div class="quickicon-name d-flex align-items-end" <?php echo isset($displayData['ajaxurl']) ? ' aria-hidden="true"' : ''; ?>>
					<?php echo Text::_($displayData['name']); ?>
				</div>
			<?php endif; ?>
			<?php // Information or action from plugins
			if (isset($displayData['text'])): ?>
				<div class="quickicon-text d-flex align-items-center">
					<?php echo $text; ?>
				</div>
			<?php endif; ?>
		</a>
	</li>
	<?php // Add the link to the edit-form
	if (isset($displayData['linkadd'])): ?>
		<li class="btn-block quickicon-linkadd j-links-link">
			<a href="<?php echo $displayData['linkadd']; ?>">
				<span class="fa fa-plus mr-2" aria-hidden="true"></span>
				<span class="sr-only"><?php echo Text::_($displayData['name'] . '_ADD_SRONLY'); ?></span>
				<span aria-hidden="true"><?php echo Text::_($displayData['name'] . '_ADD'); ?></span>
			</a>
		</li>
	</ul>
	</li>
	<?php endif; ?>
