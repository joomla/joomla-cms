<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>				<table class="admintable table"  width="100%">
						<tr class="hikashop_product_name_row">
							<td class="key">
									<?php echo JText::_( 'HIKA_NAME' ); ?>*
							</td>
							<td>
								<input id="product_name" type="text" size="80" name="<?php echo $this->product_name_input; ?>" value="<?php echo $this->escape(@$this->element->product_name); ?>" />
								<?php if(isset($this->product_name_published)){
										$publishedid = 'published-'.$this->product_name_id;
								?>
								<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->product_name_published,'translation') ?></span>
								<?php } ?>
							</td>
						</tr>
						<tr class="hikashop_product_description_row">
							<td colspan="2" width="100%">
								<table class="admintable"  width="100px">
									<tr>
										<td class="key">
											<?php echo JText::_( 'PRODUCT_DESCRIPTION' ); ?>
										</td>
									</tr>
								</table>
								<?php if(isset($this->product_description_published)){
										$publishedid = 'published-'.$this->product_description_id;
								?>
								<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->product_description_published,'translation') ?></span>
								<br/>
								<?php }
									$this->editor->content = @$this->element->product_description;
									echo $this->editor->display();
								?>
							</td>
						</tr>
						<?php if($this->element->product_type=='main'){ ?>
							<tr class="hikashop_product_url_row">
								<td class="key">
										<?php echo JText::_( 'URL' ); ?>
								</td>
								<td>
									<input id="product_url" type="text" size="80" name="<?php echo $this->product_url_input; ?>" value="<?php echo $this->escape(@$this->element->product_url); ?>" />
									<?php if(isset($this->product_url_published)){
											$publishedid = 'published-'.$this->product_url_id;
									?>
									<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->product_url_published,'translation') ?></span>
									<?php } ?>
								</td>
							</tr>
							<tr class="hikashop_product_meta_description_row">
								<td class="key">
										<?php echo JText::_( 'PRODUCT_META_DESCRIPTION' ); ?>
								</td>
								<td>
									<textarea id="product_meta_description" cols="46" rows="2" name="<?php echo $this->product_meta_description_input; ?>"><?php echo $this->escape(@$this->element->product_meta_description); ?></textarea>
									<?php if(isset($this->product_meta_description_published)){
											$publishedid = 'published-'.$this->product_meta_description_id;
									?>
									<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->product_meta_description_published,'translation') ?></span>
									<?php } ?>
								</td>
							</tr>
							<tr class="hikashop_product_keywords_row">
								<td class="key">
										<?php echo JText::_( 'PRODUCT_KEYWORDS' ); ?>
								</td>
								<td>
									<textarea id="product_keywords" cols="46" rows="2" name="<?php echo $this->product_keywords_input; ?>"><?php echo $this->escape(@$this->element->product_keywords); ?></textarea>
									<?php if(isset($this->product_keywords_published)){
											$publishedid = 'published-'.$this->product_keywords_id;
									?>
									<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->product_keywords_published,'translation') ?></span>
									<?php } ?>
								</td>
							</tr>
							<tr class="hikashop_product_page_title_row">
								<td class="key">
										<?php echo JText::_( 'PAGE_TITLE' ); ?>
								</td>
								<td>
									<textarea id="product_page_title" cols="46" rows="2" name="<?php echo $this->product_page_title_input; ?>"><?php echo $this->escape(@$this->element->product_page_title); ?></textarea>
									<?php if(isset($this->product_page_title_published)){
											$publishedid = 'published-'.$this->product_page_title_id;
									?>
									<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->product_page_title_published,'translation') ?></span>
									<?php } ?>
								</td>
							</tr>
							<tr class="hikashop_product_alias_row">
								<td class="key">
										<?php echo JText::_( 'HIKA_ALIAS' ); ?>
								</td>
								<td>
									<textarea id="product_alias" cols="46" rows="2" name="<?php echo $this->product_alias_input; ?>"><?php echo $this->escape(@$this->element->product_alias); ?></textarea>
									<?php if(isset($this->product_alias_published)){
											$publishedid = 'published-'.$this->product_alias_id;
									?>
									<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->product_alias_published,'translation') ?></span>
									<?php } ?>
								</td>
							</tr>
							<tr class="hikashop_product_canonical_row">
								<td class="key">
										<?php echo JText::_( 'PRODUCT_CANONICAL' ); ?>
								</td>
								<td>
									<input type="text" id="product_canonial" name="<?php echo $this->product_canonical_input; ?>" value="<?php echo $this->escape(@$this->element->product_canonical); ?>"/>
									<?php if(isset($this->product_canonical_published)){
											$publishedid = 'published-'.$this->product_canonical_id;
									?>
									<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->product_canonical_published,'translation') ?></span>
									<?php } ?>
								</td>
							</tr>
<?php if(HIKASHOP_J30) {
	$tagsHelper = hikashop_get('helper.tags');
	if($tagsHelper->isCompatible()) {
?>							<tr class="hikashop_product_tags">
								<td class="key">
										<?php echo JText::_('JTAG'); ?>
								</td>
								<td>
<?php
		$tags = $tagsHelper->loadTags('product', $this->element);
		echo $tagsHelper->renderInput($tags);
	}
?>								</td>
							</tr>
<?php } ?>
						<?php } ?>
					</table>
