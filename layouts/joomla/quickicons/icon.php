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

$id      = empty($displayData['id']) ? '' : (' id="' . $displayData['id'] . '"');
$target  = empty($displayData['target']) ? '' : (' target="' . $displayData['target'] . '"');
$onclick = empty($displayData['onclick']) ? '' : (' onclick="' . $displayData['onclick'] . '"');

// The title for the link (a11y)
$title   = empty($displayData['title']) ? '' : (' title="' . $this->escape($displayData['title']) . '"');

// The information
$text    = empty($displayData['text']) ? '' : ('<span class="j-links-link">' . $displayData['text'] . '</span>');

$class = '';

if ($id !== '')
{
	$class = ($displayData['id'] === 'plg_quickicon_joomlaupdate'
		|| $displayData['id'] === 'plg_quickicon_extensionupdate'
		|| $displayData['id'] === 'plg_quickicon_overridecheck') ? ' class="pulse"' : '';
}

?>
<li class="col mb-3 d-flex <?php echo !empty($displayData['linkadd']) ? 'flex-column' : ''; ?>">
	<a <?php echo $id . $class; ?> href="<?php echo $displayData['link']; ?>"<?php echo $target . $onclick . $title; ?>>

		<?php if (isset($displayData['amount'])): ?>
			<div class="quickicon-amount d-flex align-items-end">
				<?php
				$amount = (int) $displayData['amount'];
				if ($amount <  100000):
					echo '<span class="quickicon-amount">' . $amount . '</span>';
				else:
					echo '<span class="quickicon-amount">' . floor($amount / 1000) . '<span class="thsd">' . $amount % 1000 . '</span></span>';
				endif;
				?>
			</div>
		<?php elseif (isset($displayData['image'])): ?>
			<div class="quickicon-icon d-flex align-items-end">
				<div class="<?php echo $displayData['image']; ?>" aria-hidden="true"></div>
			</div>
		<?php endif; ?>
		<?php // Name indicates the component
			if (isset($displayData['name'])): ?>
			<div class="quickicon-name d-flex align-items-end"><?php echo htmlspecialchars($displayData['name']); ?></div>
		<?php endif; ?>
		<?php // Information or action
			if (isset($displayData['text'])): ?>
			<div class="quickicon-text d-flex align-items-end"><?php echo $text; ?></div>
		<?php endif; ?>
	</a>
	<?php // Add the link to the edit-form
		if (!empty($displayData['linkadd'])): ?>
			<a class="btn-block text-center quickicon-linkadd j-links-link py-1" href="<?php echo $displayData['linkadd']; ?>">
				<span class="fa fa-pencil-square mr-2" aria-hidden="true"></span>
				<?php echo Text::_('MOD_QUICKICON_ADD_NEW'); ?>
			</a>
	<?php endif; ?>
</li>

