<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>					<table class="admintable"  width="100%">
						<tr>
							<td class="key">
									<?php echo JText::_( 'HIKA_NAME' ); ?>
							</td>
							<td>
								<?php
								$systemOrderStatuses = array('created', 'confirmed', 'cancelled', 'refunded', 'shipped');
								if(!empty($this->element->category_id) && in_array($this->element->category_name, $systemOrderStatuses) && !@$this->translation && @$this->element->category_type=='status'){ ?>
									<input id="category_name" type="hidden" size="80" name="<?php echo $this->category_name_input; ?>" value="<?php echo $this->escape(@$this->element->category_name); ?>" /><?php echo $this->escape(@$this->element->category_name); ?>
								<?php }else{ ?>
									<input id="category_name" type="text" size="80" name="<?php echo $this->category_name_input; ?>" value="<?php echo $this->escape(@$this->element->category_name); ?>" />
								<?php }
									if(isset($this->category_name_published)){
										$publishedid = 'published-'.$this->category_name_id;
								?>
								<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->category_name_published,'translation') ?></span>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_( 'CATEGORY_DESCRIPTION' ); ?>
							</td>
							<td width="100%"></td>
						</tr>
						<tr>
							<td colspan="2" width="100%">
								<?php if(isset($this->category_description_published)){
										$publishedid = 'published-'.$this->category_description_id;
								?>
								<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->category_description_published,'translation') ?></span>
								<br/>
								<?php }
									$this->editor->content = @$this->element->category_description;
									echo $this->editor->display();
								?>
							</td>
						</tr>
						<?php if(empty($this->element->category_type) || in_array($this->element->category_type,array('product','manufacturer'))){ ?>
						<tr>
							<td class="key">
									<?php echo JText::_( 'CATEGORY_META_DESCRIPTION' ); ?>
							</td>
							<td>
								<textarea id="category_meta_description" cols="46" rows="2" name="<?php echo $this->category_meta_description_input; ?>"><?php echo $this->escape(@$this->element->category_meta_description); ?></textarea>
								<?php if(isset($this->category_meta_description_published)){
										$publishedid = 'published-'.$this->category_meta_description_id;
								?>
								<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->category_meta_description_published,'translation') ?></span>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'CATEGORY_KEYWORDS' ); ?>
							</td>
							<td>
								<textarea id="category_keywords" cols="46" rows="1" name="<?php echo $this->category_keywords_input; ?>"><?php echo $this->escape(@$this->element->category_keywords); ?></textarea>
								<?php if(isset($this->category_keywords_published)){
										$publishedid = 'published-'.$this->category_keywords_id;
								?>
								<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->category_keywords_published,'translation') ?></span>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'PAGE_TITLE' ); ?>
							</td>
							<td>
								<textarea id="category_page_title" cols="46" rows="2" name="<?php echo $this->category_page_title_input; ?>"><?php if(!isset($this->element->category_page_title)) $this->element->category_page_title=''; echo $this->escape(@$this->element->category_page_title); ?></textarea>
								<?php if(isset($this->category_page_title_published)){
										$publishedid = 'published-'.$this->category_page_title_id;
								?>
								<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->category_page_title_published,'translation') ?></span>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'HIKA_ALIAS' ); ?>
							</td>
							<td>
								<textarea id="category_alias" cols="46" rows="2" name="<?php echo $this->category_alias_input; ?>"><?php if(!isset($this->element->category_alias))$this->element->category_alias=''; echo $this->escape(@$this->element->category_alias); ?></textarea>
								<?php if(isset($this->category_alias_published)){
										$publishedid = 'published-'.$this->category_alias_id;
								?>
								<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->category_alias_published,'translation') ?></span>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_( 'PRODUCT_CANONICAL' ); ?>
							</td>
							<td>
								<input type="text" id="category_canonical" name="<?php echo $this->category_canonical_input; ?>" value="<?php if(!isset($this->element->category_alias))$this->element->category_alias=''; echo $this->escape(@$this->element->category_canonical); ?>"/>
								<?php if(isset($this->category_canonical_published)){
										$publishedid = 'published-'.$this->category_canonical_id;
								?>
								<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->category_canonical_published,'translation') ?></span>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
					</table>
