<?php
/** $Id: default_form.php 10094 2008-03-02 04:35:10Z instance $ */
defined( '_JEXEC' ) or die( 'Restricted access' );
?>

<?php foreach($this->categories as $this->category): ?>
	<?php if(isset($this->data[$this->category->title]) && count($this->data[$this->category->title]) > 0): ?>
		<tr>
			<td colspan="4"class="directorycategory<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
				<?php if($this->params->get('linkcat')): ?>
					<a href="<?php echo $this->category->link; ?>"><?php echo $this->category->title; ?></a>
				<?php else: echo $this->category->title; ?>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td colspan="4">
				<?php if(count($this->data[$this->category->title]) > 0): ?>
				<?php foreach($this->data[$this->category->title] as $this->contact): ?>
				<div class="directorybox<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
					<?php if(($this->contact->params->get('show_name') && $this->contact->name) || (isset($this->contact->pos_title) && count($this->contact->pos_title) >= 1)): ?>
					<div class="directorytitle<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
						<div class="contentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
							<?php if($this->params->get('link')): ?>
							<a href="<?php echo $this->contact->link; ?>">
								<?php echo $this->contact->name; ?>
							</a>
							<?php else: echo $this->contact->name;?>
							<?php endif; ?>
						</div>
						<?php foreach($this->contact->pos_title as $this->contact->info):?>
						<?php if($this->contact->info->data && $this->contact->info->show_field && $this->contact->info->access <= $this->user->get('aid', 0)): ?>
						<div class="directoryfield<?php echo $this->contact->info->params->get( 'css_tag' ); ?><?php echo $this->params->get( 'pageclass_sfx' ); ?>">
							<span class="directoryfieldtitle<?php echo $this->contact->info->params->get( 'css_tag' ); ?><?php echo $this->params->get( 'pageclass_sfx' ); ?>">
								<?php echo $this->contact->info->params->get( 'marker_title' ); ?>
							</span>
							<span><?php echo $this->contact->info->data; ?></span>
						</div>
						<?php endif; ?>
						<?php endforeach; ?>
					</div>
					<div class="directoryinfo<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
						<!-- Top Position -->
						<?php if(isset($this->contact->pos_top) && count($this->contact->pos_top) >= 1): ?>
						<?php foreach($this->contact->pos_top as $this->contact->info):?>
						<?php if($this->contact->info->data && $this->contact->info->show_field && $this->contact->info->access <= $this->user->get('aid', 0)): ?>
						<div class="directoryfield<?php echo $this->contact->info->params->get( 'css_tag' ); ?><?php echo $this->params->get( 'pageclass_sfx' ); ?>">
							<span class="directoryfieldtitle<?php echo $this->contact->info->params->get( 'css_tag' ); ?><?php echo $this->params->get( 'pageclass_sfx' ); ?>">
								<?php echo $this->contact->info->params->get( 'marker_title' ); ?>
							</span>
							<span><?php echo $this->contact->info->data; ?></span>
						</div>
						<?php endif; ?>
						<?php endforeach; ?>
						<?php endif; ?>

						<?php if((isset($this->contact->pos_left) && count($this->contact->pos_left) >= 1) || (isset($this->contact->pos_main) && count($this->contact->pos_main) >= 1) || (isset($this->contact->pos_right) && count($this->contact->pos_right) >= 1)): ?>
						<table width="100%">
							<tr>
								<!-- Left Position -->
								<?php if(isset($this->contact->pos_left) && count($this->contact->pos_left) >= 1): ?>
								<td valign="top" width="1%">
									<?php foreach($this->contact->pos_left as $this->contact->info):?>
									<?php if($this->contact->info->data && $this->contact->info->show_field && $this->contact->info->access <= $this->user->get('aid', 0)): ?>
									<div class="directoryfield<?php echo $this->contact->info->params->get( 'css_tag' ); ?><?php echo $this->params->get( 'pageclass_sfx' ); ?>">
										<span class="directoryfieldtitle<?php echo $this->contact->info->params->get( 'css_tag' ); ?><?php echo $this->params->get( 'pageclass_sfx' ); ?>">
											<?php echo $this->contact->info->params->get( 'marker_title' ); ?>
										</span>
										<span><?php echo $this->contact->info->data; ?></span>
									</div>
									<?php endif; ?>
									<?php endforeach; ?>
								</td>
								<?php endif; ?>

								<!-- Main Position -->
								<?php if(isset($this->contact->pos_main) && count($this->contact->pos_main) >= 1): ?>
								<td valign="top">
									<?php foreach($this->contact->pos_main as $this->contact->info):?>
									<?php if($this->contact->info->data && $this->contact->info->show_field && $this->contact->info->access <= $this->user->get('aid', 0)): ?>
									<div class="directoryfield<?php echo $this->contact->info->params->get( 'css_tag' ); ?><?php echo $this->params->get( 'pageclass_sfx' ); ?>">
										<span class="directoryfieldtitle<?php echo $this->contact->info->params->get( 'css_tag' ); ?><?php echo $this->params->get( 'pageclass_sfx' ); ?>">
											<?php echo $this->contact->info->params->get( 'marker_title' ); ?>
										</span>
										<span><?php echo $this->contact->info->data; ?></span>
									</div>
									<?php endif; ?>
									<?php endforeach; ?>
								</td>
								<?php endif; ?>

								<!-- Right Position -->
								<?php if(isset($this->contact->pos_right) && count($this->contact->pos_right) >= 1): ?>
								<td valign="top" width="1%">
									<?php foreach($this->contact->pos_right as $this->contact->info):?>
									<?php if($this->contact->info->data && $this->contact->info->show_field && $this->contact->info->access <= $this->user->get('aid', 0)): ?>
									<div class="directoryfield<?php echo $this->contact->info->params->get( 'css_tag' ); ?><?php echo $this->params->get( 'pageclass_sfx' ); ?>">
										<span class="directoryfieldtitle<?php echo $this->contact->info->params->get( 'css_tag' ); ?><?php echo $this->params->get( 'pageclass_sfx' ); ?>">
											<?php echo $this->contact->info->params->get( 'marker_title' ); ?>
										</span>
										<span><?php echo $this->contact->info->data; ?></span>
									</div>
									<?php endif; ?>
									<?php endforeach; ?>
								</td>
								<?php endif; ?>
							</tr>
						</table>
						<?php endif; ?>

						<!-- Bottom Position -->
						<?php if(isset($this->contact->pos_bottom) && count($this->contact->pos_bottom) >= 1): ?>
						<?php foreach($this->contact->pos_bottom as $this->contact->info):?>
						<?php if($this->contact->info->data && $this->contact->info->show_field && $this->contact->info->access <= $this->user->get('aid', 0)): ?>
						<div class="directoryfield<?php echo $this->contact->info->params->get( 'css_tag' ); ?><?php echo $this->params->get( 'pageclass_sfx' ); ?>">
							<span class="directoryfieldtitle<?php echo $this->contact->info->params->get( 'css_tag' ); ?><?php echo $this->params->get( 'pageclass_sfx' ); ?>">
								<?php echo $this->contact->info->params->get( 'marker_title' ); ?>
							</span>
							<span><?php echo $this->contact->info->data; ?></span>
						</div>
						<?php endif; ?>
						<?php endforeach; ?>
						<?php endif; ?>
					</div>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
				<?php endif; ?>
			</td>
		</tr>
	<?php endif; ?>
<?php endforeach; ?>