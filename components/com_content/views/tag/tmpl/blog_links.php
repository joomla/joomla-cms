<?php
/**
 * @package Tag View Feature for Joomla!
 * @copyright Copyright (c) 2016 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;
?>

<ol class="nav nav-tabs nav-stacked">
	<?php foreach ($this->link_items as &$item) : ?>
		<li>
			<a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language)); ?>">
				<?php echo $item->title; ?></a>
		</li>
	<?php endforeach; ?>
</ol>
