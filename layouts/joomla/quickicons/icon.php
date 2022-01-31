<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$id      = empty($displayData['id']) ? '' : (' id="' . $displayData['id'] . '"');
$target  = empty($displayData['target']) ? '' : (' target="' . $displayData['target'] . '"');
$onclick = empty($displayData['onclick']) ? '' : (' onclick="' . $displayData['onclick'] . '"');

if (isset($displayData['ajaxurl'])) {
	$dataUrl = 'data-url="' . $displayData['ajaxurl'] . '"';
} else {
	$dataUrl = '';
}

// The title for the link (a11y)
$title = empty($displayData['title']) ? '' : (' title="' . $this->escape($displayData['title']) . '"');

// The information
$text = empty($displayData['text']) ? '' : ('<span class="j-links-link">' . $displayData['text'] . '</span>');

// Make the class string
$class = empty($displayData['class']) ? '' : (' class="' . $this->escape($displayData['class']) . '"');

?>
<?php // If it is a button with two links: make it a list
	if (isset($displayData['linkadd'])): ?>
		<li class="quickicon-group">
		<ul class="list-unstyled d-flex w-100">
			<li class="quickicon">
	<?php else: ?>
		<li class="quickicon quickicon-single">
	<?php endif; ?>

		<a <?php echo $id . $class; ?> href="<?php echo $displayData['link']; ?>"<?php echo $target . $onclick . $title; ?>>
			<div class="quickicon-info">
				<?php if (isset($displayData['image'])): ?>
					<div class="quickicon-icon">
						<div class="<?php echo $displayData['image']; ?>" aria-hidden="true"></div>
					</div>
				<?php endif; ?>
				<?php if (isset($displayData['ajaxurl'])) : ?>
					<div class="quickicon-amount" <?php echo $dataUrl ?> aria-hidden="true">
						<span class="icon-spinner" aria-hidden="true"></span>
					</div>
					<div class="quickicon-sr-desc visually-hidden"></div>
				<?php endif; ?>
			</div>
			<?php // Name indicates the component
			if (isset($displayData['name'])): ?>
				<div class="quickicon-name d-flex align-items-end" <?php echo isset($displayData['ajaxurl']) ? ' aria-hidden="true"' : ''; ?>>
					<?php echo Text::_($displayData['name']); ?>
				</div>
			<?php endif; ?>
			<?php // Information or action from plugins
			if (isset($displayData['text'])): ?>
				<div class="quickicon-name d-flex align-items-center">
					<?php echo $text; ?>
				</div>
			<?php endif; ?>
		</a>
	</li>
	<?php // Add the link to the edit-form
	if (isset($displayData['linkadd'])): ?>
		<li class="quickicon-linkadd j-links-link d-flex">
			<a class="d-flex" href="<?php echo $displayData['linkadd']; ?>" title="<?php echo Text::_($displayData['name'] . '_ADD'); ?>">
				<span class="icon-plus" aria-hidden="true"></span>
			</a>
		</li>
	</ul>
	</li>
	<?php endif; ?>
