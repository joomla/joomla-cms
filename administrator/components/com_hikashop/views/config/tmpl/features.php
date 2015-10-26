<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
echo $this->leftmenu(
	'features',
	array(
		'#features_main' => JText::_('MAIN'),
		'#features_vote' => JText::_('VOTE_AND_COMMENT'),
		'#features_affiliate' => JText::_('AFFILIATE'),
		'#features_wishlist' => JText::_('WISHLIST'),
		'#features_waitlist' => JText::_('HIKA_WAITLIST'),
		'#features_compare' => JText::_('COMPARE'),
		'#features_sef' => JText::_('SEF_URL_OPTIONS'),
		'#features_filter' => JText::_('FILTER'),
		'#features_atom' => JText::_('ALL_FEED')
	)
);
?>
<div id="page-features" class="rightconfig-container <?php if(HIKASHOP_BACK_RESPONSIVE) echo 'rightconfig-container-j30';?>">
	<table style="width:100%;">
		<tr>
			<td valign="top" width="50%">
	<!-- MAIN -->
				<div id="features_main">
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'MAIN' ); ?></legend>
					<table class="admintable table" cellspacing="1">
						<tr>
							<td class="key">
								<?php echo JText::_('CATALOGUE_MODE'); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', 'config[catalogue]','onchange="if(this.value==1) alert(\''.JText::_('CATALOGUE_MODE_WARNING',true).'\');"',$this->config->get('catalogue'));?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('ENABLE_MULTI_CART'); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', 'config[enable_multicart]','',$this->config->get('enable_multicart',1));?>
							</td>
						</tr>
					</table>
				</fieldset>
				</div>
	<!-- VOTE -->
				<div id="features_vote">
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'VOTE_AND_COMMENT' ); ?></legend>
					<table class="admintable table" cellspacing="1">
						<tr id="vote_display_line">
							<td class="key">
								<?php echo JText::_('DISPLAY_VOTE_OF_PRODUCTS');?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', 'config[default_params][show_vote_product]' , '',@$this->default_params['show_vote_product']); ?>
							</td>
						</tr>
						<tr id="hika_hide_vote">
							<td class="key">
									<?php echo JText::_('ENABLE_STATUS'); ?>
							</td>
							<td>
								<?php
									$arr = array(
										JHTML::_('select.option', 'nothing', JText::_('HIKA_VOTE_NOTHING') ),
										JHTML::_('select.option', 'vote', JText::_('HIKA_VOTE_ONLY') ),
										JHTML::_('select.option', 'comment', JText::_('HIKA_COMMENT_ONLY') ),
										JHTML::_('select.option', 'two', JText::_('HIKA_VOTE_OR_COMMENT') ),
										JHTML::_('select.option', 'both', JText::_('HIKA_VOTE_AND_COMMENT') )
									);
									echo JHTML::_('hikaselect.genericlist', $arr, "config[enable_status_vote]", 'class="inputbox" size="1"', 'value', 'text', $this->config->get('enable_status_vote',0));
								?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('ACCESS_VOTE'); ?>
							</td>
							<td>
								<?php
									$arr = array(
										JHTML::_('select.option', 'public', JText::_('HIKA_VOTE_PUBLIC') ),
										JHTML::_('select.option', 'registered', JText::_('HIKA_VOTE_REGISTERED') ),
										JHTML::_('select.option', 'buyed', JText::_('HIKA_VOTE_BOUGHT') )
									);
									echo JHTML::_('hikaselect.genericlist', $arr, "config[access_vote]", 'class="inputbox" size="1"', 'value', 'text', $this->config->get('access_vote',0));
								?>
							</td>
						</tr>
						<tr><td style="padding: 4px 0 4px 0;"></td></tr>
						<tr>
							<td class="key" >
								<?php echo JText::_('STAR_NUMBER'); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[vote_star_number]" value="<?php echo $this->config->get('vote_star_number',5);?>" />
							</td>
						</tr>
						<tr><td style="padding: 4px 0 4px 0;"></td></tr>
						<tr>
							<td class="key" >
								<?php echo JText::_('EMAIL_COMMENT'); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', "config[email_comment]" , '', $this->config->get('email_comment',0)); ?>
							</td>
						</tr>
						<tr>
							<td class="key" >
								<?php echo JText::_('PUBLISHED_COMMENT'); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', "config[published_comment]" , '', $this->config->get('published_comment',1)); ?>
							</td>
						</tr>
						<tr>
							<td class="key" >
								<?php echo JText::_('EMAIL_NEW_COMMENT'); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[email_each_comment]" value="<?php echo $this->config->get('email_each_comment');?>" />
							</td>
						</tr>
						<tr>
							<td class="key" >
								<?php echo JText::_('COMMENT_BY_PERSON_BY_PRODUCT'); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[comment_by_person_by_product]" value="<?php echo $this->config->get('comment_by_person_by_product',5);?>" />
							</td>
						</tr>
						<tr><td style="padding: 4px 0 4px 0;"></td></tr>
						<tr>
							<td class="key" >
								<?php echo JText::_('NUMBER_COMMENT_BY_PRODUCT'); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[number_comment_product]" value="<?php echo $this->config->get('number_comment_product',30); ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_('VOTE_COMMENT_SORT'); ?>
							</td>
							<td>
								<?php
									$arr = array(
										JHTML::_('select.option', 'date', JText::_('DATE') ),
										JHTML::_('select.option', 'date_desc', JText::_('DATE_DESC') ),
										JHTML::_('select.option', 'helpful', JText::_('HELPFUL') )
									);
									echo JHTML::_('hikaselect.genericlist', $arr, "config[vote_comment_sort]", 'class="inputbox" size="1"', 'value', 'text', $this->config->get('vote_comment_sort',0));
								?>
							</td>
						</tr>
						<tr>
							<td class="key" >
								<?php echo JText::_('VOTE_COMMENT_SORT_FRONTEND'); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', "config[vote_comment_sort_frontend]" , '', $this->config->get('vote_comment_sort_frontend',0)); ?>
							</td>
						</tr>
						<tr>
							<td class="key" >
								<?php echo JText::_('SHOW_LISTING_COMMENT'); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', "config[show_listing_comment]" , '', $this->config->get('show_listing_comment',0)); ?>
							</td>
						</tr>
						<tr>
							<td class="key" >
								<?php echo JText::_('SHOW_COMMENT_DATE'); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', "config[show_comment_date]" , '', $this->config->get('show_comment_date',0)); ?>
							</td>
						</tr>
						<tr>
							<td class="key" >
								<?php echo JText::_('USEFUL_RATING'); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', "config[useful_rating]" , '', $this->config->get('useful_rating',1)); ?>
							</td>
						</tr>
						<tr>
							<td class="key" >
								<?php echo JText::_('REGISTER_NOTE_COMMENT'); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', "config[register_note_comment]" , '', $this->config->get('register_note_comment',0)); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_('VOTE_USEFUL_STYLE'); ?>
							</td>
							<td>
								<?php
									$arr = array(
										JHTML::_('select.option', 'helpful', JText::_('HIKA_VOTE_USEFUL_COUNT') ),
										JHTML::_('select.option', 'thumbs', JText::_('HIKA_VOTE_USEFUL_HAND') ),
									);
									echo JHTML::_('hikaselect.genericlist', $arr, "config[vote_useful_style]", 'class="inputbox" size="1"', 'value', 'text', $this->config->get('vote_useful_style',0));
								?>
							</td>
						</tr>
					</table>
				</fieldset>
				</div>
	<!-- AFFILIATE -->
<?php
$pluginClass = hikashop_get('class.plugins');
$plugin = JPluginHelper::getPlugin('system', 'hikashopaffiliate');
if(empty($plugin)){
	$affiliate_active = false;
}else{
	$affiliate_active = true;
}
if(hikashop_level(2)&&$affiliate_active) {
	$this->setLayout('affiliate');
	echo $this->loadTemplate();
}
?>
	<!-- WISHLIST -->
				<div id="features_wishlist">
				<fieldset class="adminform">
				<legend><?php echo JText::_( 'WISHLIST' ); ?></legend>
				<?php if(hikashop_level(1)){ ?>
						<table class="admintable table" cellspacing="1">
							<tr>
								<td class="key">
									<?php echo JText::_('ENABLE_WISHLIST'); ?>
								</td>
								<td>
									<?php echo JHTML::_('hikaselect.booleanlist', 'config[enable_wishlist]','',$this->config->get('enable_wishlist',1)); ?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<?php echo JText::_('HIDE_WISHLIST_GUEST'); ?>
								</td>
								<td>
									<?php echo JHTML::_('hikaselect.booleanlist', 'config[hide_wishlist_guest]','',$this->config->get('hide_wishlist_guest',1));?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<?php echo JText::_('DISPLAY_ADD_TO_WISHLIST_BUTTON_FOR_FREE_PRODUCT'); ?>
								</td>
								<td>
									<?php echo JHTML::_('hikaselect.booleanlist', 'config[display_add_to_wishlist_for_free_products]','',$this->config->get('display_add_to_wishlist_for_free_products',1));?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<?php echo JText::_('CHECKOUT_CONVERT_CART'); ?>
								</td>
								<td>
									<?php echo JHTML::_('hikaselect.booleanlist', 'config[checkout_convert_cart]','',$this->config->get('checkout_convert_cart',1));?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<?php echo JText::_('WISHLIST_TO_COMPARE'); ?>
								</td>
								<td>
									<?php echo JHTML::_('hikaselect.booleanlist', 'config[wishlist_to_compare]','',$this->config->get('wishlist_to_compare',1));?>
								</td>
							</tr>
						</table>
						<?php }else{
							echo hikashop_getUpgradeLink('essential');
						} ?>
				</fieldset>
				</div>
	<!-- WAITLIST -->
				<div id="features_waitlist">
				<fieldset class="adminform">
				<legend><?php echo JText::_( 'HIKA_WAITLIST' ); ?></legend>
						<table class="admintable table" cellspacing="1">
							<tr>
								<td class="key">
									<?php echo JText::_('ACTIVATE_WAITLIST'); ?>
								</td>
								<td>
									<?php if(hikashop_level(1)){
										echo $this->waitlist->display('config[product_waitlist]',$this->config->get('product_waitlist',0));
									}else{
										echo '<small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
									} ?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<?php echo JText::_('WAITLIST_SUBSCRIBE_LIMIT'); ?>
								</td>
								<td>
									<?php if(hikashop_level(1)){
										?><input class="inputbox" type="text" name="config[product_waitlist_sub_limit]" value="<?php echo $this->config->get('product_waitlist_sub_limit','20'); ?>"/><?php
									}else{
										echo '<small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
									} ?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<?php echo JText::_('WAITLIST_SEND_LIMIT'); ?>
								</td>
								<td>
									<?php if(hikashop_level(1)){
										?><input class="inputbox" type="text" name="config[product_waitlist_send_limit]" value="<?php echo $this->config->get('product_waitlist_send_limit','5'); ?>"/><?php
									}else{
										echo '<small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
									} ?>
								</td>
							</tr>
						</table>
				</fieldset>
				</div>
	<!-- COMPARE -->
				<div id="features_compare">
				<fieldset class="adminform">
				<legend><?php echo JText::_( 'COMPARE' ); ?></legend>
						<table class="admintable table" cellspacing="1">
							<tr>
								<td class="key">
									<?php echo JText::_('COMPARE_MODE'); ?>
								</td>
								<td>
									<?php if(hikashop_level(2)){
										echo $this->compare->display('config[show_compare]',$this->config->get('show_compare'));
									}else{
										echo '<small style="color:red">'.JText::_('ONLY_FROM_HIKASHOP_BUSINESS').'</small>';
									} ?>
								</td>
							</tr>
							<?php if(hikashop_level(2)){ ?>
							<tr>
								<td class="key">
									<?php echo JText::_('COMPARE_LIMIT'); ?>
								</td>
								<td>
									<input class="inputbox" type="text" name="config[compare_limit]" value="<?php echo $this->config->get('compare_limit','5'); ?>"/>
								</td>
							</tr>
							<?php } ?>
							<tr>
								<td class="key">
									<?php echo JText::_('COMPARE_TO_WISHLIST'); ?>
								</td>
								<td>
									<?php echo JHTML::_('hikaselect.booleanlist', 'config[compare_to_wishlist]','',$this->config->get('compare_to_wishlist',1));?>
								</td>
							</tr>
						</table>
				</fieldset>
				</div>
	<!-- SEF -->
				<div id="features_sef">
				<fieldset class="adminform" style="width:95%">
					<legend><?php echo JText::_( 'SEF_URL_OPTIONS' ); ?></legend>
						<table class="table">
							<tr>
								<td>
										<?php
											$sefOptions='';
											if($this->config->get('activate_sef',1)==0){
												$sefOptions='style="display:none"';
											}
										?>
										<table class="admintable" cellspacing="1" width="100%">
											<tr>
												<td class="key">
													<?php echo JText::_('SIMPLIFIED_BREADCRUMBS'); ?>
												</td>
												<td>
													<?php echo JHTML::_('hikaselect.booleanlist', "config[simplified_breadcrumbs]",'',$this->config->get('simplified_breadcrumbs',1)); ?>
												</td>
											</tr>
											<tr>
												<td class="key">
													<?php echo JText::_('REMOVE_ID_IN_URLS'); ?>
												</td>
												<td>
													<?php echo JHTML::_('hikaselect.booleanlist', "config[sef_remove_id]",'',$this->config->get('sef_remove_id',0)); ?>
												</td>
											</tr>
											<tr>
												<td class="key">
													<?php echo JText::_('REMOVE_ID_IN_CART_URLS'); ?>
												</td>
												<td>
													<?php echo JHTML::_('hikaselect.booleanlist', "config[sef_cart_remove_id]",'',$this->config->get('sef_cart_remove_id',0)); ?>
												</td>
											</tr>
											<tr>
												<td class="key">
													<?php echo JText::_('ALIAS_AUTO_FILL'); ?>
												</td>
												<td>
													<?php echo JHTML::_('hikaselect.booleanlist', "config[alias_auto_fill]",'',$this->config->get('alias_auto_fill',1)); ?>
												</td>
											</tr>
											<?php //option removed on the 07/02/2015... We still keep the option for persons having it disabled as it should always be enabled
											if(!$this->config->get('activate_sef',1)){ ?>
												<tr>
													<td class="key">
														<?php echo JText::_('ACTIVATE_SMALLER_URL'); ?>
													</td>
													<td>
														<?php echo JHTML::_('hikaselect.booleanlist', "config[activate_sef]",'onclick="setVisible(this.value);"',$this->config->get('activate_sef',1)); ?>
													</td>
												</tr>
											<?php } ?>
											<tr id="sef_cat_name" <?php echo $sefOptions; ?>>
												<td class="key">
													<?php echo JText::_('CATEGORY_LISTING_SEF_NAME'); ?>
												</td>
												<td>
													<input class="inputbox" type="text" id="cat_sef" name="config[category_sef_name]" value="<?php echo $this->config->get('category_sef_name', 'category'); ?>" onchange="checkSEF(this,document.getElementById('prod_sef').value,'<?php echo $this->config->get('category_sef_name', 'category'); ?>');">
												</td>
											</tr>
											<tr id="sef_prod_name" <?php echo $sefOptions; ?>>
												<td class="key">
													<?php echo JText::_('PRODUCT_SHOW_SEF_NAME'); ?>
												</td>
												<td>
													<input class="inputbox" type="text" id="prod_sef" name="config[product_sef_name]" value="<?php echo $this->config->get('product_sef_name', 'product'); ?>" onchange="checkSEF(this,document.getElementById('cat_sef').value,'<?php echo $this->config->get('product_sef_name', 'category'); ?>');">
												</td>
											</tr>
											<tr id="sef_checkout_name" <?php echo $sefOptions; ?>>
												<td class="key">
													<?php echo JText::_('CART_SEF_NAME'); ?>
												</td>
												<td>
													<input class="inputbox" type="text" id="prod_sef" name="config[cart_sef_name]" value="<?php echo $this->config->get('cart_sef_name', 'cart'); ?>">
												</td>
											</tr>
											<tr id="sef_checkout_name" <?php echo $sefOptions; ?>>
												<td class="key">
													<?php echo JText::_('WISHLIST_SEF_NAME'); ?>
												</td>
												<td>
													<input class="inputbox" type="text" id="prod_sef" name="config[wishlist_sef_name]" value="<?php echo $this->config->get('wishlist_sef_name', 'wishlist'); ?>">
												</td>
											</tr>
											<tr id="sef_checkout_name" <?php echo $sefOptions; ?>>
												<td class="key">
													<?php echo JText::_('CHECKOUT_SEF_NAME'); ?>
												</td>
												<td>
													<input class="inputbox" type="text" id="prod_sef" name="config[checkout_sef_name]" value="<?php echo $this->config->get('checkout_sef_name', 'checkout'); ?>">
												</td>
											</tr>
											<tr>
												<td class="key">
														<?php echo JText::_('FORCE_CANONICAL_URLS_ON_LISTINGS'); ?>
												</td>
												<td>
													<?php
														$arr = array(
															JHTML::_('select.option', 0, JText::_('NO_GENERATE_URL') ),
															JHTML::_('select.option', 1, JText::_('USE_CANONICAL_URL_IF_SPECIFIED') ),
															JHTML::_('select.option', 2, JText::_('USE_CANONICAL_URL_AND_GENERATE_IT_IF_MISSING') ),
														);
														echo JHTML::_('hikaselect.genericlist', $arr, "config[force_canonical_urls]", 'class="inputbox" size="1"', 'value', 'text', $this->config->get('force_canonical_urls',1));
													?>
												</td>
											</tr>
											<tr>
												<td class="key">
													<?php echo JText::_('AUTO_KEYWORDS_AND_METADESCRIPTION_FILLING'); ?>
												</td>
												<td>
													<?php echo JHTML::_('hikaselect.booleanlist', "config[auto_keywords_and_metadescription_filling]",'onchange="keywords_num_visible(this.value);" onload="keywords_num_visible(this.value);"',$this->config->get('auto_keywords_and_metadescription_filling',0)); ?>
												</td>
											</tr>

											<tr id="keyword_meta1" style="display:<?php if($this->config->get('auto_keywords_and_metadescription_filling', '0') == "0") echo "none" ?>;">
												<td class="key">
													<?php echo JText::_('KEYWORDS_NUMBER'); ?>
												</td>
												<td>
													<input class="inputbox" type="text" id="keywords_number" name="config[keywords_number]" value="<?php echo $this->config->get('keywords_number','0'); ?>"/>
													<script type="text/javascript">
													function keywords_num_visible(val) {
														if(val.localeCompare("0")== 0){
															var i = document.getElementById("keyword_meta1").style.display = 'none';
															var i = document.getElementById("keyword_meta2").style.display = 'none';
															var i = document.getElementById("keyword_meta3").style.display = 'none';
														}else if(val.localeCompare("1")== 0){
															var i = document.getElementById("keyword_meta1").style.display = '';
															var i = document.getElementById("keyword_meta2").style.display = '';
															var i = document.getElementById("keyword_meta3").style.display = '';
														}
														return true;
													}
													</script>
												</td>
											</tr>
											<tr id="keyword_meta2" style="display:<?php if($this->config->get('auto_keywords_and_metadescription_filling', '0') == "0") echo "none" ?>;">
												<td class="key">
													<?php echo JText::_('MAX_SIZE_OF_METADESCRIPTION'); ?>
												</td>
												<td>
													<input class="inputbox" type="text" id="max_size_of_metadescription" name="config[max_size_of_metadescription]" value="<?php echo $this->config->get('max_size_of_metadescription', '254'); ?>">
													<span>(Max 254 characters)</span>
												</td>
											</tr>
											<tr id="keyword_meta3" style="display:<?php if($this->config->get('auto_keywords_and_metadescription_filling', '0') == "0") echo "none" ?>;">
												<td class="key">
													<?php echo JText::_('KEYWORDS_EXCLUSION_LIST'); ?>
												</td>
												<td>
													<input class="inputbox" type="text" id="keywords_exclusion_list" name="config[keywords_exclusion_list]" value="<?php echo $this->config->get('keywords_exclusion_list', 'what,when,why,with,this,then,the,these,those,thus,they'); ?>">
												</td>
											</tr>

										</table>
								</td>
							</tr>
						</table>
				</fieldset>
				</div>
	<!-- FILTER -->
				<div id="features_filter">
				<fieldset class="adminform">
				<legend><?php echo JText::_( 'FILTER' ); ?></legend>
								<?php if(hikashop_level(2)){ ?>
								<table class="admintable table" cellspacing="1">
									<tr>
										<td class="key">
											<?php echo JText::_('NUMBER_OF_COLUMNS');?>
										</td>
										<td>
											<input name="config[filter_column_number]" type="text" value="<?php echo $this->config->get('filter_column_number',2)?>" />
										</td>
									</tr>
									<tr>
										<td class="key">
											<?php echo JText::_('LIMIT');?>
										</td>
										<td>
											<input name="config[filter_limit]" type="text" value="<?php echo $this->config->get('filter_limit')?>" />
										</td>
									</tr>
									<tr>
										<td class="key">
											<?php echo JText::_('HEIGHT');?>
										</td>
										<td>
											<input name="config[filter_height]" type="text" value="<?php echo $this->config->get('filter_height',100)?>" />
										</td>
									</tr>
									<tr>
										<td class="key" >
											<?php echo JText::_('SHOW_RESET_BUTTON'); ?>
										</td>
										<td>
											<?php echo JHTML::_('hikaselect.booleanlist', 'config[show_reset_button]' , '',@$this->config->get('show_reset_button',0)); ?>
										</td>
									</tr>
									<tr>
										<td class="key" >
											<?php echo JText::_('SHOW_FILTER_BUTTON'); ?>
										</td>
										<td>
											<?php echo JHTML::_('hikaselect.booleanlist', 'config[show_filter_button]' , '',@$this->config->get('show_filter_button',1)); ?>
										</td>
									</tr>
									<tr>
										<td class="key" >
											<?php echo JText::_('DISPLAY_FIELDSET'); ?>
										</td>
										<td>
											<?php echo JHTML::_('hikaselect.booleanlist', 'config[display_fieldset]' , '',@$this->config->get('display_fieldset',1)); ?>
										</td>
									</tr>
									<tr>
										<td class="key" >
											<?php echo JText::_('FILTER_BUTTON_POSITION'); ?>
										</td>
										<td>
											<?php echo $this->filterButtonType->display('config[filter_button_position]',$this->config->get('filter_button_position'));?>
										</td>
									</tr>
								</table>
								<?php }else{
									echo '<small style="color:red">'.JText::_('ONLY_FROM_HIKASHOP_BUSINESS').'</small>';
								} ?>
				</fieldset>
				</div>
																			<!-- ATOM & RSS -->
				<div id="features_atom">
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'ALL_FEED' ); ?></legend>
					<table class="admintable table" cellspacing="1">
						<tr>
							<td class="key">
								<?php echo JText::_('HIKA_TYPE'); ?>
							</td>
							<td>
								<?php echo $this->elements->hikarss_format; ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('HIKA_NAME'); ?>
							</td>
							<td>
								<input type="text" size="40" name="config[hikarss_name]" value="<?php echo $this->config->get('hikarss_name',''); ?>"/>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('HIKA_DESCRIPTION'); ?>
							</td>
							<td>
								<textarea cols="32" rows="5" name="config[hikarss_description]" ><?php echo $this->config->get('hikarss_description',''); ?></textarea>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('NUMBER_OF_ITEMS'); ?>
							</td>
							<td>
								<input type="text" size="40" name="config[hikarss_element]" value="<?php echo $this->config->get('hikarss_element','10'); ?>"/>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('ORDERING_FIELD'); ?>
							</td>
							<td>
								<?php echo $this->elements->hikarss_order; ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('SHOW_SUB_CATEGORIES');?>
							</td>
							<td>
								<?php echo $this->elements->hikarss_child; ?>
							</td>
						</tr>
					</table>
				</fieldset>
				</div>
			</td>
		</tr>
	</table>
</div>

<script language="JavaScript" type="text/javascript">
function checkSEF(obj,other,default_val){
	if(obj.value == other){
		obj.value = default_val;
		alert('you can\'t have the same SEF name for product and category');
	}
}
</script>
