<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<script type="text/javascript">
function alphabetFilter(val){
	document.getElementById('alpha').value=val;
	document.contactForm.submit();
}
</script>

<?php if ( $this->params->get( 'show_page_title' ) ) : ?>
<div class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<?php 	if ($this->category->title && $this->params->get('show_title') ) : echo $this->escape($this->params->get('page_title')).' - '.$this->escape($this->category->title);
				else : echo $this->escape($this->params->get('page_title'));
				endif; ?>
</div>
<?php endif; ?>

<div class="contentpane<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<?php if ($this->category->image || $this->category->description) : ?>
		<div class="contentdescription<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
			<?php if ($this->params->get('show_description_image')) : ?>
				<img src="<?php echo $this->baseurl .'/'. $this->cparams->get('image_path') . '/'. $this->category->image; ?>" align="<?php echo $this->category->image_position; ?>" hspace="6" alt="<?php echo JText::_( 'CONTACT' ); ?>" />
			<?php endif;
				if ($this->params->get('show_description')) :
					echo $this->category->description;
				endif; ?>
		</div>
	<?php endif; ?>

	<form action="<?php echo $this->action; ?>" method="post" name="contactForm">
	<table  width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
		<?php if($this->params->get('alphabet') || $this->params->get('search')): ?>
		<thead>
			<tr>
				<td colspan="2" height="50">
					<?php if($this->params->get('alphabet')): ?>
						<?php foreach($this->alphabet as $letter): ?>
							<a href="javascript:alphabetFilter('<?php echo $letter; ?>')"><?php echo $letter; ?></a>
						<?php endforeach; ?> |
						<a href="javascript:alphabetFilter('')"><?php echo JText::_( 'RESET' ); ?></a>
						<input type="text" name="alphabet" id="alpha" value="" style="display:none;"/>
					<?php endif; ?>
				</td>
				<td align="right" nowrap="nowrap" colspan="2">
					<?php if($this->params->get('search')): ?>
						<?php echo JText::_( 'FILTER' ); ?>:
				 		<input type="text" name="search" id="searchword" size="15" value="<?php echo $this->lists['search'];?>" class="inputbox" onchange="document.contactForm.submit();"/>
						<button onclick="this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
						<button onclick="document.getElementById('searchword').value='';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
					<?php endif; ?>
				</td>
			</tr>
		</thead>
		<?php endif; ?>
		<tfoot>
			<tr>
				<td nowrap="nowrap" height="50">
					<?php if ($this->params->get('show_limit')) :
						echo JText::_('DISPLAY #') .'&nbsp;';
						echo $this->pagination->getLimitBox();
					endif; ?>
				</td>
				<td align="center" colspan="2" class="sectiontablefooter<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
					<?php echo $this->pagination->getPagesLinks(); ?>
				</td>
				<td align="right" class="sectiontablefooter<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
					<?php echo $this->pagination->getPagesCounter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<tr>
				<td colspan="4">
				<?php if(count($this->contacts) > 0): ?>
				<?php foreach($this->contacts as $this->contact): ?>
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
			<?php if($this->params->get('linktop')): ?>
			<tr>
				<td colspan="4" class="toplink">
					<a href="#top" name="backTop">Back to top</a>
				</td>
			</tr>
			<?php endif; ?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="com_contact" />
	<input type="hidden" name="catid" value="<?php echo $this->category->id;?>" />
	</form>
</div>