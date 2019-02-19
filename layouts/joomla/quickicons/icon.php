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

// The number as string 
// $amount = HTMLHelper::_('string.abridge', '5757575757575757', 3, 3, false);
$amount = 

$class = '';

if ($id && is_numeric($id))
{
	$class = ($displayData['id'] === 'plg_quickicon_joomlaupdate' || $displayData['id'] === 'plg_quickicon_extensionupdate') ? ' class="pulse"' : '';
}

?>
<li class="col">
	<a <?php echo $id . $class; ?> href="<?php echo $displayData['link']; ?>"<?php echo $target . $onclick . $title; ?>>
		<?php if (isset($displayData['amount'])): ?>
			<div class="quickicon-amount d-flex align-items-end">
				<?php
				if ((int) $amount <  100000):
					echo $amount ; 
				else:
					echo floor($amount / 1000) . '<span class="thsd">' . TEXT::_('Thsd.') . '</span>';
				endif;
				?>
			</div>
		<?php elseif ( isset($displayData['image'])): ?>
			<div class="quickicon-icon d-flex align-items-end">
				<div class="<?php echo $displayData['image']; ?>" aria-hidden="true"></div>
			</div>
		<?php endif; ?>
		<?php // Name indicates the component	
			if (isset($displayData['name'])): ?>
			<div class="quickicon-name d-flex align-items-center"><?php echo htmlspecialchars($displayData['name']); ?></div>
		<?php endif; ?>
		<?php // Information or action	
		if (isset($displayData['text'])): ?>
			<div class="quickicon-text d-flex align-items-center"><?php echo $text; ?></div>
		<?php endif; ?>
	</a>
</li>
