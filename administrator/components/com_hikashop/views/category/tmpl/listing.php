<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>

<?php if($this->config->get('category_explorer')){?>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
<div id="page-category">
	<table style="width:100%">
		<tr>
			<td style="vertical-align:top;border:1px solid #CCC;background-color: #F3F3F3" width="200px">
				<?php echo hikashop_setExplorer('category&task=listing',$this->pageInfo->filter->filter_id,false,$this->type); ?>
			</td>
			<td style="vertical-align:top;">
<?php } else { ?>
<div id="page-category" class="row-fluid">
	<div class="span2">
		<?php echo hikashop_setExplorer('category&task=listing',$this->pageInfo->filter->filter_id,false,$this->type); ?>
	</div>
	<div class="span10">
<?php } ?>
<?php } ?>
		<?php $count = 7; ?>
			<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=category" method="post"  name="adminForm" id="adminForm">
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
				<table style="width:100%">
					<tr>
						<td>
<?php } else {?>
				<div class="row-fluid">
					<div class="span6">
<?php } ?>

							<?php
								 if ( !empty( $this->extrafilters)) {
									 foreach($this->extrafilters as $name => $filterObj) {
										 if(is_string($filterObj)){
											 echo $filterObj;
										 }elseif( isset( $filterObj->objSearch) && method_exists($filterObj->objSearch,'displayFilter')){
											 echo $filterObj->objSearch->displayFilter($name, $this->pageInfo->filter);
										 }else if ( isset( $filterObj->filter_html_search)){
											 echo $filterObj->filter_html_search;
										 }
									 }
								 }
							?>
<?php if(!$this->config->get('category_explorer')){ ?>
							<a href="<?php echo hikashop_completeLink('category&task=listing&filter_id=0'); ?>"><?php echo JText::_( 'ROOT' ); ?>/</a>
							<?php echo $this->breadCrumb.'<br/>'; ?>
<?php } ?>
							<?php echo JText::_( 'FILTER' ); ?>:
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
							<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" />
							<button class="btn" onclick="this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
							<button class="btn" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
						</td>
						<td>
<?php } else { ?>
						<div class="input-prepend input-append" style="margin-top:4px;">
							<span class="add-on"><i class="icon-filter"></i></span>
							<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" onchange="this.form.submit();" />
							<button class="btn" onclick="this.form.limitstart.value=0;this.form.submit();"><i class="icon-search"></i></button>
							<button class="btn" onclick="this.form.limitstart.value=0;document.getElementById('search').value='';this.form.submit();"><i class="icon-remove"></i></button>
						</div>
					</div>
					<div class="span6">
						<div class="expand-filters" style="width:auto;float:right">
<?php } ?>
							<?php
								if ( !empty( $this->extrafilters)) {
									foreach($this->extrafilters as $name => $filterObj) {
										if(is_string($filterObj)){
											echo $filterObj;
										}elseif(isset( $filterObj->objDropdown) && method_exists($filterObj->objDropdown,'displayFilter')){
											echo $filterObj->objDropdown->displayFilter($name, $this->pageInfo->filter);
										}else if ( isset( $filterObj->filter_html_dropdown)){
											echo $filterObj->filter_html_dropdown;
										}
									}
								}
							?>
							<?php echo $this->childDisplay; ?>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
						</td>
					</tr>
				</table>
<?php } else {?>
						</div>
						<div style="clear:both"></div>
					</div>
				</div>
<?php } ?>
				<table id="hikashop_category_listing" class="adminlist table table-striped table-hover" cellpadding="1">
					<thead>
						<tr>
							<th class="title titlenum">
								<?php echo JText::_( 'HIKA_NUM' );?>
							</th>
							<th class="title titlebox">
								<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
							</th>
							<th class="title titlebox">
								<?php echo JText::_('HIKA_EDIT'); ?>
							</th>
							<?php if($this->category_image){ $count++; ?>
							<th class="title titlebox">
								<?php echo JText::_('HIKA_IMAGE'); ?>
							</th>
							<?php }?>
							<th class="title">
								<?php echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'a.category_name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
							</th>
							<?php
							if(!empty($this->fields)){
								foreach($this->fields as $field){ $count++;
									echo '<th class="title">'.JHTML::_('grid.sort', $this->fieldsClass->trans($field->field_realname), 'a.'.$field->field_namekey, $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ).'</th>';
								}
							}

							if(!empty($this->extrafields)) {
								foreach($this->extrafields as $namekey => $extrafield) { $count++;
									echo '<th class="hikashop_category_'.$namekey.'_title title">'.$extrafield->name.'</th>'."\r\n";
								}
							} ?>
							<th class="title titleorder">
								<?php if(!$this->pageInfo->selectedType){
									echo JHTML::_('grid.sort', JText::_( 'HIKA_ORDER' ), 'a.category_ordering',$this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value );
									if ($this->order->ordering) echo JHTML::_('grid.order',  $this->rows );
								}else{ ?>
									<a href="#" title="<?php echo JText::_('CHANGE_SUB_ELEMENT_FILTER_TO_REORDER_ELEMENTS'); ?>"><?php echo JText::_( 'HIKA_ORDER' ); ?></a>
								<?php } ?>
							</th>
							<th class="title titletoggle">
								<?php echo JHTML::_('grid.sort',   JText::_('HIKA_PUBLISHED'), 'a.category_published', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
							</th>
							<th class="title">
								<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'a.category_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
							</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="<?php echo $count; ?>">
								<?php echo $this->pagination->getListFooter(); ?>
								<?php echo $this->pagination->getResultsCounter(); ?>
							</td>
						</tr>
					</tfoot>
					<tbody>
						<?php
							$k = 0;
							for($i = 0,$a = count($this->rows);$i<$a;$i++){
								$row =& $this->rows[$i];
								$publishedid = 'category_published-'.$row->category_id;
						?>
							<tr class="<?php echo "row$k"; ?>">
								<td align="center">
								<?php echo $this->pagination->getRowOffset($i); ?>
								</td>
								<td align="center">
									<?php echo JHTML::_('grid.id', $i, $row->category_id ); ?>
								</td>
								<td width="1%" align="center">
									<?php if($this->manage){ ?>
										<a href="<?php echo hikashop_completeLink('category&task=edit&cid[]='.$row->category_id); ?>">
											<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="edit"/>
										</a>
									<?php } ?>
								</td>
								<?php if($this->category_image){ ?>
								<td>
									<?php echo $this->image->display(@$row->file_path,true,"",'','', 100, 100); ?>
								</td>
								<?php } ?>
								<td>
									<a href="<?php echo hikashop_completeLink('category&filter_id='.$row->category_id); ?>">
										<?php echo $row->translation; ?>
									</a>
								</td>
								<?php
								if(!empty($this->fields)){
									foreach($this->fields as $field){
										$namekey = $field->field_namekey;

										echo '<td>'.$this->fieldsClass->show($field,$row->$namekey).'</td>';
									}
								}
								if(!empty($this->extrafields)) {
									foreach($this->extrafields as $namekey => $extrafield) {
										$value = '';
										if( isset($extrafield->value)) {
											$n = $extrafield->value;
											$value = $row->$n;
										} else if(!empty($extrafield->obj)) {
											$n = $extrafield->obj;
											$value = $n->showfield($this, $namekey, $row);
										} else if( isset( $row->$namekey)) {
											$value = $row->$namekey;
										}
										echo '<td class="hikashop_category_'.$namekey.'_value">'.$value.'</td>';
									}
								} ?>
								<td class="order">
									<?php if(!$this->pageInfo->selectedType){
										if($this->manage){ ?>
											<span><?php echo $this->pagination->orderUpIcon( $i, $this->order->reverse XOR ( $row->category_ordering >= @$this->rows[$i-1]->category_ordering ), $this->order->orderUp, 'Move Up',$this->order->ordering ); ?></span>
											<span><?php echo $this->pagination->orderDownIcon( $i, $a, $this->order->reverse XOR ( $row->category_ordering <= @$this->rows[$i+1]->category_ordering ), $this->order->orderDown, 'Move Down' ,$this->order->ordering); ?></span>
											<input type="text" name="order[]" size="5" <?php if(!$this->order->ordering) echo 'disabled="disabled"'?> value="<?php echo $row->category_ordering; ?>" class="text_area" style="text-align: center" />
											<?php
										}else{ echo $row->category_ordering; }
									}else{ ?>
										<a href="#" title="<?php echo JText::_('CHANGE_SUB_ELEMENT_FILTER_TO_REORDER_ELEMENTS'); ?>"><img src="<?php echo HIKASHOP_IMAGES; ?>delete2.png" alt="<?php echo JText::_('HIKA_DELETE'); ?>"></a>
									<?php } ?>
								</td>
								<td align="center">
									<?php if($this->manage){ ?>
										<span id="<?php echo $publishedid ?>" class="spanloading"><?php echo $this->toggleClass->toggle($publishedid,(int) $row->category_published,'category') ?></span>
									<?php }else{ echo $this->toggleClass->display('activate',$row->category_published); } ?>
								</td>
								<td width="1%" align="center">
									<?php echo $row->category_id; ?>
								</td>
							</tr>
						<?php
								$k = 1-$k;
							}
						?>
					</tbody>
				</table>
				<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" id="filter_id" name="filter_id" value="<?php echo $this->pageInfo->filter->filter_id; ?>" />
				<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
				<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
				<?php echo JHTML::_( 'form.token' ); ?>
			</form>
<?php if($this->config->get('category_explorer')){?>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
		</tr>
	</table>
</div>
<?php } else { ?>
	</div>
</div>
<?php } ?>
<?php } ?>
