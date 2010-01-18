<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_newsfeeds
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<?php
		$lang = &JFactory::getLanguage();
		$myrtl =$this->newsfeed->rtl;
		$direction = " ";

		if ($lang->isRTL() && $myrtl==0){
		   $direction= " redirect-rtl";
		   }
		else if ($lang->isRTL() && $myrtl==1){
		   $direction= " redirect-ltr";
		   }
		else if ($lang->isRTL() && $myrtl==2){
		   $direction= " redirect-rtl";
		   }
		else if ($myrtl==0) {
			$direction= " redirect-ltr";
		   }
		else if ($myrtl==1) {
			$direction= " redirect-ltr";
		   }
		else if ($myrtl==2) {
		   $direction= " redirect-rtl";
		   }
?>

<div class="newsfeed<?php echo $this->params->get('pageclass_sfx')?><?php echo $direction; ?>">

<?php if ($this->params->get('show_page_title', 1)) : ?>
	<h2 class="<?php echo $direction; ?>">
		<?php if ($this->escape($this->params->get('page_heading'))) :?>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		<?php else : ?>
			<?php echo $this->escape($this->params->get('page_title')); ?>
		<?php endif; ?>
	</h2>
<?php endif; ?>

	<h3 class="<?php echo $direction; ?>">
		<a href="<?php echo $this->newsfeed->channel['link']; ?>" target="_blank">
			<?php echo str_replace('&apos;', "'", $this->newsfeed->channel['title']); ?></a>
	</h3>

<!-- Show Description -->
<?php if ($this->params->get('show_feed_description')) : ?>
	<div>
		<?php echo str_replace('&apos;', "'", $this->newsfeed->channel['description']); ?>
	</div>
<?php endif; ?>

<!-- Show Image -->
<?php if (isset($this->newsfeed->image['url']) && isset($this->newsfeed->image['title']) && $this->params->get('show_feed_image')) : ?>
<div>
		<img src="<?php echo $this->newsfeed->image['url']; ?>" alt="<?php echo $this->newsfeed->image['title']; ?>" />
</div>
<?php endif; ?>

<!-- Show items -->
<ol>
	<?php foreach ($this->newsfeed->items as $item) :  ?>
		<li>
			<?php if (!is_null($item->get_link())) : ?>
				<a href="<?php echo $item->get_link(); ?>" target="_blank">
					<?php echo $item->get_title(); ?></a>
			<?php endif; ?>
			<?php if ($this->params->get('show_item_description') && $item->get_description()) : ?>
				<div>
				<?php $text = $this->limitText($item->get_description(), $this->params->get('feed_word_count'));
					echo str_replace('&apos;', "'", $text);
				?>
				</div>
			<?php endif; ?>
			</li>
		<?php endforeach; ?>
		</ol>
</div>

