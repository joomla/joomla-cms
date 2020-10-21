<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_syndicate
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Set alt-text for the image only if no text is displayed
$alt = (!$params->get('display_text', 1) && empty($text)) ? JText::_('MOD_SYNDICATE_DEFAULT_FEED_ENTRIES') : '';
?>
<a href="<?php echo $link; ?>" class="syndicate-module<?php echo $moduleclass_sfx; ?>">
	<?php echo JHtml::_('image', 'system/livemarks.png', $alt, null, true); ?>
	<?php if ($params->get('display_text', 1)) : ?>
		<span>
		<?php if (str_replace(' ', '', $text) !== '') : ?>
			<?php echo $text; ?>
		<?php else : ?>
			<?php echo JText::_('MOD_SYNDICATE_DEFAULT_FEED_ENTRIES'); ?>
		<?php endif; ?>
		</span>
	<?php endif; ?>
</a>
