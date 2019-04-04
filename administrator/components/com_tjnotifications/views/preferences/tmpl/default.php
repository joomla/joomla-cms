<?php

// No direct access to this file
defined('_JEXEC') or die;

		$language = JFactory::getLanguage();
		$language->load('com_tjnotification', JPATH_SITE, 'en-GB', true);
		$language->load('com_tjnotification', JPATH_SITE, null, true);


?>

<script src="/jquery.min.js"></script>
<script type="text/javascript">
	const tjnBaseurl = "<?php echo JUri::root();?>";
	jQuery.noConflict();
	jQuery(".btn-group > .btn").click(function(){
    jQuery(this).addClass("active").siblings().removeClass("active");
});

	function addPreferance(pId,client,provider,key)
	{
		if(pId)
		{
			jQuery.ajaxSetup({
				global: false,
				type:'post',
				url:tjnBaseurl+'index.php?option=com_tjnotifications&task=preferences.save',
				dataType:'json',
				beforeSend: function () {
					jQuery('#ajax-loader'+pId).show();
				jQuery('#ajax-loader'+pId).html("<img src='<?php echo JURI::root();?>components/com_tjnotifications/images/ajax-loader.gif'><style='display:block'>");
				},
				complete: function () {
					jQuery('#ajax-loader'+pId).hide();
					jQuery('#tick'+pId).show();
					jQuery('#tick'+pId).html("<img src='<?php echo JURI::root();?>components/com_tjnotifications/images/tick.png'><style='display:block'>");

					setTimeout(function() {
						jQuery('#tick'+pId).hide();
					}, 5000);
				}
			});
			jQuery.ajax({
			data:
			{
				client_name:client,
				provider_name:provider,
				key:key,
			},
			success: function (response)
			{
				jQuery( '#display_info' ).html("Item successfully saved");
			}
				});
		 }
		 else
		 {
			jQuery( '#display_info' ).html("Item not successfully saved");
		}
	}
	function removePreferance(pId,client,provider,key)
	{
		 if(pId)
		 {
			jQuery.ajaxSetup({
				global: false,
				type:'post',
				url:tjnBaseurl+'index.php?option=com_tjnotifications&task=preferences.delete',

				beforeSend: function () {
					jQuery('#ajax-loader'+pId).show();
				   jQuery('#ajax-loader'+pId).html("<img src='<?php echo JURI::root();?>components/com_tjnotifications/images/ajax-loader.gif'><style='display:block'>");
				},
				complete: function () {
					jQuery('#ajax-loader'+pId).hide();
					jQuery('#tick'+pId).show();
					jQuery('#tick'+pId).html("<img src='<?php echo JURI::root();?>components/com_tjnotifications/images/tick.png'><style='display:block'>");
						setTimeout(function() {
						jQuery('#tick'+pId).hide();
					}, 5000);
				}
			});
			jQuery.ajax({
			dataType:'json',
			data:
			{
				client_name:client,
				provider_name:provider,
				key:key,
			},
			success: function (response)
			{
				jQuery( '#display_info' ).html("Item successfully saved");
			}
				});
		 }
		 else
		 {
			jQuery( '#display_info' ).html("Item not successfully saved");
		}
	}
</script>

<form action="index.php?option=com_tjnotifications&view=preferences" method="post" id="adminForm" name="adminForm">
	<div class="row-fluid">
		<div class="span6">
		</div>
	</div>

	<div id="display_info"></div>
		<ul class="nav nav-tabs">
		<?php if (!empty($this->clients)) : ?>
		<?php foreach ($this->clients as $i => $menu) :?>
			<li>
				<a data-toggle="tab" aria-controls="<?php echo($menu->client); ?>"
					href="<?php echo('#'.$menu->client); ?>">
					<?php echo str_replace("com_","",$menu->client); ?>
				</a>
			</li>
		<?php endforeach; ?>
		<?php endif; ?>
		</ul>

	<div class="tab-content">
	<?php foreach ($this->clients as $i => $menu) :?>
		<div class="tab-pane" id="<?php echo($menu->client); ?>" >

			<table class="table table-striped table-hover">
				<thead>
					<tr>
					<th width="20%">

					</th>
					<?php if (!empty($this->providers)) : ?>
						<?php foreach ($this->providers as $i => $head) :?>
							<th width="30%">
									<?php echo($head->provider); ?>
							</th>
						<?php endforeach; ?>
					<?php endif; ?>
					</tr>
				</thead>
				<tbody>
					<div class="row">
						<div class="col-md-3">
						<?php foreach($this->keys[$menu->client] as $key=>$values) : ?>
							<?php foreach($values as $value ) : ?>
							<tr>
								<td align="center">
									<?php echo $value; ?>
								</td>
								<?php foreach ($this->providers as $i => $head) :?>
								<td>
								<?php  $temp=0; ?>
								<?php foreach ($this->adminPreferences[$head->provider] as $adminKey => $admin) :?>
									<?php if($admin->client == $menu->client && $admin->key == $value) : $temp++; ?>

										<?php if(empty($this->preferences)) :  ?>

											<div class="control">
												<fieldset class="btn-group btn-group-yesno radio pull-left" >
													<input type="radio" id="<?php echo $value.$i; ?>" name="prefer" value="1" onclick="removePreferance('<?php echo $value.$i; ?>','<?php echo($menu->client); ?>','<?php echo($head->provider); ?>','<?php echo $value; ?>')" checked="checked" />
													<input type="radio" id="<?php echo $key.$i; ?>" name="prefer1" value="0" onclick="addPreferance('<?php echo $value.$i; ?>','<?php echo($menu->client); ?>','<?php echo($head->provider); ?>','<?php echo $value; ?>')" />
													<label class="btn-success" for="<?php echo $value.$i; ?>" ><?php echo JText::_('COM_TJNOTIFICATION_VIEWS_PREFERENCES_FIELD_ENABLE'); ?></label>
													<label class="btn" for="<?php echo $key.$i; ?>" ><?php echo JText::_('COM_TJNOTIFICATION_VIEWS_PREFERENCES_FIELD_DISABLE'); ?></label>
												</fieldset>
											</div>

											<div class="pull-right" id="<?php echo 'ajax-loader'.$value.$i; ?>"  ></div>
											<div class="pull-right" id="<?php echo 'tick'.$value.$i; ?>"  ></div>
										<?php else: $count=0; ?>

										<?php foreach ($this->preferences as $j => $prefer) :?>
											<?php if($prefer->client == $menu->client && $prefer->key == $value && $prefer->provider == $head->provider) : ?>
											<?php $count++; ?>
											<div class="control">
												<fieldset class="btn-group btn-group-yesno radio pull-left" >
														<input type="radio" id="<?php echo $menu->client.$value.$i; ?>" name="prefer" value="1" onclick="removePreferance('<?php echo $menu->client.$value.$i; ?>','<?php echo($menu->client); ?>','<?php echo($head->provider); ?>','<?php echo $value; ?>')"  />
														<input type="radio" id="<?php echo $menu->client.$key.$i; ?>" name="prefer1" value="0" onclick="addPreferance('<?php echo $menu->client.$value.$i; ?>','<?php echo($menu->client); ?>','<?php echo($head->provider); ?>','<?php echo $value; ?>')" checked="checked" />
														<label class="btn" for="<?php echo $menu->client.$value.$i; ?>" ><?php echo JText::_('COM_TJNOTIFICATION_VIEWS_PREFERENCES_FIELD_ENABLE'); ?></label>
														<label class="btn-danger" for="<?php echo $menu->client.$key.$i; ?>" ><?php echo JText::_('COM_TJNOTIFICATION_VIEWS_PREFERENCES_FIELD_DISABLE'); ?></label>
												</fieldset>
											</div>
											<?php endif;?>
										<?php endforeach; ?>

											<?php if ($count==0): ?>
												<div class="control">
													<fieldset class="btn-group btn-group-yesno radio pull-left" >
														<input type="radio" id="<?php echo $menu->client.$value.$i; ?>" name="prefer" value="1" onclick="removePreferance('<?php echo $menu->client.$value.$i; ?>','<?php echo($menu->client); ?>','<?php echo($head->provider); ?>','<?php echo $value; ?>')"  checked="checked" />
														<input type="radio" id="<?php echo $menu->client.$key.$i; ?>" name="prefer1" value="0"  onclick="addPreferance('<?php echo $menu->client.$value.$i; ?>','<?php echo($menu->client); ?>','<?php echo($head->provider); ?>','<?php echo $value; ?>')" />
														<label class="btn-success" for="<?php echo $menu->client.$value.$i; ?>" ><?php echo JText::_('COM_TJNOTIFICATION_VIEWS_PREFERENCES_FIELD_ENABLE'); ?></label>
														<label class="btn" for="<?php echo $menu->client.$key.$i; ?>" ><?php echo JText::_('COM_TJNOTIFICATION_VIEWS_PREFERENCES_FIELD_DISABLE'); ?></label>
													</fieldset>
												</div>
											<?php endif; ?>

										<?php endif;?>

									<?php endif;?>

									<?php endforeach; ?>

									<?php if ($temp == 0): ?>
										<span class="label label-warning"><?php echo JText::_('COM_TJNOTIFICATIONS_VIEW_PREFERENCES_TAB_UNSUBSCRIBED'); ?></span>
									<?php endif; ?>
									<div class="pull-right" id="<?php echo 'ajax-loader'.$menu->client.$value.$i; ?>" ></div>
									<div class="pull-right" id="<?php echo 'tick'.$menu->client.$value.$i; ?>" ></div>
									</td>
								<?php endforeach; ?>
							</tr>
							<?php endforeach; ?>
						<?php endforeach; ?>
						</div>
					</div>
				</tbody>
			</table>
	 	</div>
	<?php endforeach;?>
	</div>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<?php echo JHtml::_('form.token'); ?>
</form>

