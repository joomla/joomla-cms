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

if (isset($displayData['name']))
{
	$add  = Text::plural($displayData['name'], 1);
	$name = Text::plural($displayData['name'], 2);
}
else
{
	$add  = '';
	$name = '';
}
?>

<li class="quickicon col mb-3 d-flex <?php echo !empty($displayData['linkadd']) ? 'flex-column' : ''; ?>">
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
			<div class="quickicon-name d-flex align-items-end">
				<?php echo htmlspecialchars($name); ?>
			</div>
		<?php endif; ?>
		<?php // Information or action from plugins
		if (isset($displayData['text'])): ?>
			<div class="quickicon-text d-flex align-items-center">
				<?php echo $text; ?>
			</div>
		<?php endif; ?>
	</a>
	<?php // Add the link to the edit-form
	if (!empty($displayData['linkadd'])): ?>
			<a class="btn-block quickicon-linkadd j-links-link" href="<?php echo $displayData['linkadd']; ?>">
				<span class="fa fa-plus mr-2" aria-hidden="true"></span>
				<span class="sr-only"><?php echo Text::sprintf('MOD_QUICKICON_ADD_NEW', $add); ?></span>
				<span aria-hidden="true"><?php echo $add; ?></span>
			</a>
	<?php endif; ?>
</li>

