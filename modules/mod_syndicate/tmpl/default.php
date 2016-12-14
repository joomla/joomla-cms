<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_syndicate
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<a href="<?php echo $link ?>" class="syndicate-module<?php echo $moduleclass_sfx ?>" <?php if ($params->get('modulecss_id')) : ?> id="<?php echo $params->get('module_id'); ?>"<?php endif;?>>
	<?php echo JHtml::_('image', 'system/livemarks.png', 'feed-image', null, true); ?>
	<?php if ($params->get('display_text', 1)) : ?>
		<span>
		<?php if (str_replace(' ', '', $text) != '') : ?>
			<?php echo $text; ?>
		<?php else : ?>
			<?php echo JText::_('MOD_SYNDICATE_DEFAULT_FEED_ENTRIES'); ?>
		<?php endif; ?>
		</span>
	<?php endif; ?>
</a>
