<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

use Joomla\CMS\Language\Text;

$blockPosition = $displayData['params']->get('info_block_position', 0);

?>
<dl class="article-info text-muted">

	<?php if ($displayData['position'] === 'above' && ($blockPosition == 0 || $blockPosition == 2)
			|| $displayData['position'] === 'below' && ($blockPosition == 1)
			) : ?>

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
