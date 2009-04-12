<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
$cparams = JComponentHelper::getParams ('com_media');
?>

<?php if ( $this->params->get( 'show_page_title' )): ?>
<div class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<?php echo $this->params->get( 'page_title' ); ?>
</div>
<?php endif; ?>

<table width="100%" cellpadding="0" cellspacing="0" border="0" class="contentpaneopen<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<tr>
		<td id="title">

		<!-- Title Position -->
		<?php if ( $this->contact->name && $this->contact->params->get( 'show_name' ) ) : ?>
			<div class="contentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
				<?php echo $this->contact->name; ?>
			</div>
		<?php endif; ?>
		<?php if(count($this->pos_title) >= 1 || $this->showFormTitle): ?>
		<?php foreach($this->pos_title as $this->info): ?>
			<?php if($this->info->data && $this->info->show_field && $this->info->access <= $this->user->get('aid', 0)): ?>
			<div class="contactinfo<?php echo $this->info->params->get( 'css_tag' ); ?><?php echo $this->params->get( 'pageclass_sfx' ); ?>">
				<span class="contactinfotitle<?php echo $this->info->params->get( 'css_tag' ); ?><?php echo $this->params->get( 'pageclass_sfx' ); ?>">
					<?php echo $this->info->params->get( 'marker_title' ); ?>
				</span>
				<span><?php echo $this->info->data; ?></span>
			</div>
			<?php endif; ?>
		<?php endforeach; if($this->showFormTitle):?>
			<div>
				<?php echo $this->loadTemplate('form'); ?>
			</div>
		<?php endif; ?>
		<?php endif; ?>
			<br/>
		</td>
	</tr>

	<!-- Top Position -->
	<?php if(count($this->pos_top) >= 1 || $this->showFormTop): ?>
	<tr>
		<td id="top">
		<?php if(count($this->pos_top) >= 1): foreach($this->pos_top as $this->info): ?>
			<?php if($this->info->data && $this->info->show_field && $this->info->access <= $this->user->get('aid', 0)): ?>
			<div class="contactinfo<?php echo $this->info->params->get( 'css_tag' ); ?><?php echo $this->params->get( 'pageclass_sfx' ); ?>">
				<span class="contactinfotitle<?php echo $this->info->params->get( 'css_tag' ); ?><?php echo $this->params->get( 'pageclass_sfx' ); ?>">
					<?php echo $this->info->params->get( 'marker_title' ); ?>
				</span>
				<span><?php echo $this->info->data; ?></span>
			</div>
			<?php endif; ?>
		<?php endforeach; endif; if($this->showFormTop):?>
			<div>
				<?php echo $this->loadTemplate('form'); ?>
			</div>
		<?php endif; ?>
			<br/>
		</td>
	</tr>
	<?php endif; ?>
	<?php if(count($this->pos_left) >= 1 || count($this->pos_main) >= 1 || count($this->pos_right) >= 1): ?>
	<tr>
		<td>
			<table width="100%" cellpadding="0" cellspacing="0" border="0" >
				<tr>

					<!-- Left Position -->
					<?php  if(count($this->pos_left) >= 1 || $this->showFormLeft):?>
					<td id="left" valign="top" style="padding-right:10px;">
					<?php if(count($this->pos_left) >= 1): foreach($this->pos_left as $this->info): ?>
						<?php if($this->info->data && $this->info->show_field && $this->info->access <= $this->user->get('aid', 0)): ?>
						<div class="contactinfo<?php echo $this->info->params->get( 'css_tag' ); ?><?php echo $this->params->get( 'pageclass_sfx' ); ?>">
							<span class="contactinfotitle<?php echo $this->info->params->get( 'css_tag' ); ?><?php echo $this->params->get( 'pageclass_sfx' ); ?>">
								<?php echo $this->info->params->get( 'marker_title' ); ?>
							</span>
							<span><?php echo $this->info->data; ?></span>
						</div>
						<?php endif; ?>
					<?php endforeach; endif; if($this->showFormLeft):?>
						<div>
							<?php echo $this->loadTemplate('form'); ?>
						</div>
					<?php endif; ?>
					</td>
					<?php endif; ?>

					<!-- Main Position -->
					<?php  if(count($this->pos_main) >= 1 || $this->showFormMain):?>
					<td id="main" valign="top">
					<?php if(count($this->pos_main) >= 1): foreach($this->pos_main as $this->info): ?>
						<?php if($this->info->data && $this->info->show_field && $this->info->access <= $this->user->get('aid', 0)): ?>
						<div class="contactinfo<?php echo $this->info->params->get( 'css_tag' ); ?><?php echo $this->params->get( 'pageclass_sfx' ); ?>">
							<span class="contactinfotitle<?php echo $this->info->params->get( 'css_tag' ); ?><?php echo $this->params->get( 'pageclass_sfx' ); ?>">
								<?php echo $this->info->params->get( 'marker_title' ); ?>
							</span>
							<span><?php echo $this->info->data; ?></span>
						</div>
						<?php endif; ?>
					<?php endforeach; endif; if($this->showFormMain):?>
						<div>
							<?php echo $this->loadTemplate('form'); ?>
						</div>
					<?php endif; ?>
					</td>
					<?php endif; ?>

					<!-- Right Position -->
					<?php if(count($this->pos_right) >= 1 || $this->showFormRight):?>
					<td id="right" valign="top" style="padding-left:10px;">
					<?php if(count($this->pos_right) >= 1): foreach($this->pos_right as $this->info): ?>
						<?php if($this->info->data && $this->info->show_field && $this->info->access <= $this->user->get('aid', 0)): ?>
							<div class="contactinfo<?php echo $this->info->params->get( 'css_tag' ); ?><?php echo $this->params->get( 'pageclass_sfx' ); ?>">
								<span class="contactinfotitle<?php echo $this->info->params->get( 'css_tag' ); ?><?php echo $this->params->get( 'pageclass_sfx' ); ?>">
									<?php echo $this->info->params->get( 'marker_title' ); ?>
								</span>
								<span><?php echo $this->info->data; ?></span>
							</div>
						<?php endif; ?>
					<?php endforeach; endif; if($this->showFormRight):?>
						<div>
							<?php echo $this->loadTemplate('form'); ?>
						</div>
					<?php endif; ?>
					</td>
					<?php endif; ?>
				</tr>
			</table>
		</td>
	</tr>
	<?php endif; ?>

	<!-- Bottom Position -->
	<?php if(count($this->pos_bottom) >= 1 || $this->showFormBottom): ?>
	<tr>
		<td id="bottom">
			<br/>
		<?php if(count($this->pos_bottom) >= 1): foreach($this->pos_bottom as $this->info): ?>
			<?php if($this->info->data && $this->info->show_field && $this->info->access <= $this->user->get('aid', 0)): ?>
			<div class="contactinfo<?php echo $this->info->params->get( 'css_tag' ); ?><?php echo $this->params->get( 'pageclass_sfx' ); ?>">
				<span class="contactinfotitle<?php echo $this->info->params->get( 'css_tag' ); ?><?php echo $this->params->get( 'pageclass_sfx' ); ?>">
					<?php echo $this->info->params->get( 'marker_title' ); ?>
				</span>
				<span><?php echo $this->info->data; ?></span>
			</div>
			<?php endif; ?>
		<?php endforeach; endif; if($this->showFormBottom):?>
			<div>
				<?php echo $this->loadTemplate('form'); ?>
			</div>
		<?php endif; ?>
		</td>
	</tr>
	<?php endif; ?>
</table>