<?php // @version $Id$
defined('_JEXEC') or die('Restricted access');
?>

<?php if ( $this->params->get( 'show_page_title' ) ) : ?>
<h1 class="componentheading<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php echo $this->params->get('page_title'); ?>
</h1>
<?php endif; ?>

<h2 class="componentheading<?php echo $this->params->get('pageclass_sfx'); ?>">
	<a href="<?php echo $this->newsfeed->channel['link']; ?>" target="_blank">
		<?php echo str_replace('&apos;', "'", $this->newsfeed->channel['title']); ?>
	</a>
</h2>

<?php if ( $this->params->get( 'show_feed_description' ) )  : ?>
<div class="feed_description">
	<?php echo str_replace('&apos;', "'", $this->newsfeed->channel['description']); ?>
</div>
<?php endif; ?>

<?php if ( isset( $this->newsfeed->image['url'] ) && isset( $this->newsfeed->image['title'] ) && $this->params->get( 'show_feed_image' ) ) : ?>
<img src="<?php echo $this->newsfeed->image['url']; ?>" alt="<?php echo $this->newsfeed->image['title']; ?>" />
<?php endif; ?>

<?php if ( count( $this->newsfeed->items ) ) : ?>
<ul>
	<?php foreach ( $this->newsfeed->items as $item ) : ?>
	<li>
		<?php if ( !is_null( $item->get_link() ) ) : ?>
		<a href="<?php echo $item->get_link(); ?>" target="_blank">
			<?php echo $item->get_title(); ?>
		</a>
		<?php endif; ?>
		<?php if ( $this->params->get( 'show_item_description' ) && $item->get_description() ) : ?>
		<br />
		<?php $text = $this->limitText( $item->get_description(), $this->params->get( 'feed_word_count' ) );
		echo str_replace('&apos;', "'", $text); ?>
		<br /><br />
		<?php endif; ?>
	</li>
	<?php endforeach; ?>
</ul>
<?php endif;
