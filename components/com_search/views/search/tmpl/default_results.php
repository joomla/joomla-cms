<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<table class="searchintro<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
<tr>
	<td colspan="3" >
		<?php echo JText::_( 'Search Keyword' ) .' <b>'. stripslashes($this->searchword) .'</b>'; ?>
	</td>
</tr>
<tr>
	<td>
		<br />
		<?php eval ('echo "'. $this->result .'";'); ?>
		<a href="http://www.google.com/search?q=<?php echo $this->searchword; ?>" target="_blank">
			<?php echo $this->image; ?>
		</a>
	</td>
</tr>
</table>
<br />
<div align="center">
	<div style="float: right;">
		<label for="limit">
			<?php echo JText::_( 'Display Num' ); ?>
		</label>
		<?php echo $this->pagination->getLimitBox( ); ?>
	</div>
	<div>
		<?php echo $this->pagination->getPagesCounter(); ?>
	</div>
</div>
<table class="contentpaneopen<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
<tr>
	<td>
	<?php
	foreach( $this->results as $result ) : ?>
		<fieldset>
			<div>
				<span class="small<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
					<?php echo $result->count.'. ';?>
				</span>
				<?php if ( $result->href ) :
					if ($result->browsernav == 1 ) : ?>
						<a href="<?php echo JRoute::_($result->href); ?>" target="_blank">
					<?php else : ?>
						<a href="<?php echo JRoute::_($result->href); ?>">
					<?php endif;

					echo $result->title;

					if ( $result->href ) : ?>
						</a>
					<?php endif;
					if ( $result->section ) : ?>
						<br />
						<span class="small<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
							(<?php echo $result->section; ?>)
						</span>
					<?php endif; ?>
				<?php endif; ?>
			</div>
			<div>
				<?php echo $result->text;?>
			</div>
			<?php
				if ( $this->params->get( 'show_date' )) : ?>
			<div class="small<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
				<?php echo $result->created; ?>
			</div>
			<?php endif; ?>
		</fieldset>
	<?php endforeach; ?>
	</td>
</tr>
<tr>
	<td colspan="3">
		<div align="center">
			<?php echo $this->pagination->getPagesLinks( ); ?>
		</div>
	</td>
</tr>
</table>