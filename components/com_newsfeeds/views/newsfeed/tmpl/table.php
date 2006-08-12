<div style="direction: <?php echo $newsfeed->rtl ? 'rtl' :'ltr'; ?>; text-align: <?php echo $newsfeed->rtl ? 'right' :'left'; ?>">
<table width="100%" class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">
<?php if ( $params->get( 'header' ) ) : ?>
<tr>
	<td class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>" colspan="2">
		<?php echo $params->get( 'header' ); ?>
	</td>
</tr>
<?php endif; ?>
<tr>
	<td class="contentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<a href="<?php echo ampReplace( $channel['link'] ); ?>" target="_blank">
			<?php echo str_replace('&apos;', "'", $channel['title']); ?>
		</a>
	</td>
</tr>
<?php if ( $params->get( 'feed_descr' ) ) : ?>
<tr>
	<td>
		<?php echo str_replace('&apos;', "'", $channel['description']); ?>
		<br />
		<br />
	</td>
</tr>
<?php endif; ?>
<?php if ( isset($image['url']) && isset($image['title']) && $params->get( 'feed_image' ) ) : ?>
<tr>
	<td>
		<img src="<?php echo $image['url']; ?>" alt="<?php echo $image['title']; ?>" />
	</td>
</tr>
<?php endif; ?>
<tr>
	<td>
		<ul>
		<?php foreach ( $items as $item ) :  ?>
			<li>
			<?php if ( !is_null( $item['link'] ) ) : ?>
				<a href="<?php echo ampReplace( $item['link'] ); ?>" target="_blank">
					<?php echo $item['title']; ?>
				</a>
			<?php endif; ?>
			<?php if ( $params->get( 'item_descr' ) ) : ?>
				<br />
				<?php $text = NewsfeedsViewNewsfeed::limitText($item['description'], $params->get( 'word_count' )); 
					echo str_replace('&apos;', "'", $text);
				?>
				<br />
				<br />
			<?php endif; ?>
			</li>
		<?php endforeach; ?>
		</ul>
	</td>
</tr>
</table>
</div>		