<?php // @version $Id: default.php 11371 2008-12-30 01:31:50Z ian $
defined('_JEXEC') or die;
?>

<?php
		$lang = &JFactory::getLanguage();
		$myrtl =$this->newsfeed->rtl;
		 if ($lang->isRTL() && $myrtl==0){
		   $direction= "direction:rtl !important;";
		   $align= "text-align:right !important;";
		   }
		 else if ($lang->isRTL() && $myrtl==1){
		   $direction= "direction:ltr !important;";
		   $align= "text-align:left !important;";
		   }
		  else if ($lang->isRTL() && $myrtl==2){
		   $direction= "direction:rtl !important;";
		   $align= "text-align:right !important;";
		   }
		else if ($myrtl==0) {
		$direction= "direction:ltr !important;";
		   $align= "text-align:left !important;";
		   }
		else if ($myrtl==1) {
		$direction= "direction:ltr !important;";
		   $align= "text-align:left !important;";
		   }
		else if ($myrtl==2) {
		   $direction= "direction:rtl !important;";
		   $align= "text-align:right !important;";
		   }
?>
<?php if ($this->params->get('show_page_title', 1)) : ?>
<h1 style="<?php echo $direction; ?><?php echo $align; ?>" class="componentheading<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php echo $this->escape($this->params->get('page_title')); ?>
</h1>
<?php endif; ?>

<h2 style="<?php echo $direction; ?><?php echo $align; ?>" class="componentheading<?php echo $this->params->get('pageclass_sfx'); ?>">
	<a href="<?php echo $this->newsfeed->channel['link']; ?>" target="_blank">
		<?php echo str_replace('&apos;', "'", $this->newsfeed->channel['title']); ?></a>
</h2>

<?php if ($this->params->get('show_feed_description'))  : ?>
<div style="<?php echo $direction; ?><?php echo $align; ?>" class="feed_description">
	<?php echo str_replace('&apos;', "'", $this->newsfeed->channel['description']); ?>
</div>
<?php endif; ?>

<?php if (isset($this->newsfeed->image['url']) && isset($this->newsfeed->image['title']) && $this->params->get('show_feed_image')) : ?>
<p style="<?php echo $direction; ?><?php echo $align; ?>">
<img src="<?php echo $this->newsfeed->image['url']; ?>" alt="<?php echo $this->newsfeed->image['title']; ?>" />
</p>
<?php endif; ?>

<?php if (count($this->newsfeed->items)) : ?>
<div style="<?php echo $direction; ?><?php echo $align; ?>">
<ul style="<?php echo $direction; ?><?php echo $align; ?>">
	<?php foreach ($this->newsfeed->items as $item) : ?>
	<li style="<?php echo $direction; ?><?php echo $align; ?>">
		<?php if (!is_null($item->get_link())) : ?>
		<a href="<?php echo $item->get_link(); ?>" target="_blank">
			<?php echo $item->get_title(); ?></a>
		<?php endif; ?>
		<?php if ($this->params->get('show_item_description') && $item->get_description()) : ?>
		<br />
		<?php $text = $this->limitText($item->get_description(), $this->params->get('feed_word_count'));
		echo str_replace('&apos;', "'", $text); ?>
		<br /><br />
		<?php endif; ?>
	</li>
	<?php endforeach; ?>
</ul>
</div>
<?php endif;
