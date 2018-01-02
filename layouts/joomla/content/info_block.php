<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;

$blockPosition = $displayData['params']->get('info_block_position', 0);

?>
<dl class="article-info text-muted">

	<?php if ($displayData['position'] === 'above' && ($blockPosition == 0 || $blockPosition == 2)
			|| $displayData['position'] === 'below' && ($blockPosition == 1)
			) : ?>

		<dt class="article-info-term">
			<?php if ($displayData['params']->get('info_block_show_title', 1)) : ?>
				<?php echo Text::_('COM_CONTENT_ARTICLE_INFO'); ?>
			<?php endif; ?>
		</dt>

		<?php if ($displayData['params']->get('show_author') && !empty($displayData['item']->author )) : ?>
			<?php echo $this->sublayout('author', $displayData); ?>
		<?php endif; ?>

		<?php if ($displayData['params']->get('show_parent_category') && !empty($displayData['item']->parent_slug)) : ?>
			<?php echo $this->sublayout('parent_category', $displayData); ?>
		<?php endif; ?>

		<?php if ($displayData['params']->get('show_category')) : ?>
			<?php echo $this->sublayout('category', $displayData); ?>
		<?php endif; ?>

		<?php if ($displayData['params']->get('show_associations')) : ?>
			<?php echo $this->sublayout('associations', $displayData); ?>
		<?php endif; ?>

		<?php if ($displayData['params']->get('show_publish_date')) : ?>
			<?php echo $this->sublayout('publish_date', $displayData); ?>
		<?php endif; ?>

	<?php endif; ?>

	<?php if ($displayData['position'] === 'above' && ($blockPosition == 0)
			|| $displayData['position'] === 'below' && ($blockPosition == 1 || $blockPosition == 2)
			) : ?>
		<?php if ($displayData['params']->get('show_create_date')) : ?>
			<?php echo $this->sublayout('create_date', $displayData); ?>
		<?php endif; ?>

		<?php if ($displayData['params']->get('show_modify_date')) : ?>
			<?php echo $this->sublayout('modify_date', $displayData); ?>
		<?php endif; ?>

		<?php if ($displayData['params']->get('show_hits')) : ?>
			<?php echo $this->sublayout('hits', $displayData); ?>
		<?php endif; ?>
	<?php endif; ?>
</dl>
