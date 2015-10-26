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
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=taxation" method="post"  name="adminForm" id="adminForm">
	<table>
		<tr>
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
			<td width="100%">
			</td>
			<td nowrap="nowrap">
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
				<?php echo $this->taxType->display("taxation_type",$this->pageInfo->filter->taxation_type,false);?>
				<?php echo $this->ratesType->display("tax_namekey",$this->pageInfo->filter->tax_namekey,false);?>
<?php
						if(file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'helpers'.DS.'utils.php')){
							include_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'helpers'.DS.'utils.php');
							if ( class_exists( 'MultisitesHelperUtils') && method_exists( 'MultisitesHelperUtils', 'getComboSiteIDs')) {
								$comboSiteIDs = MultisitesHelperUtils::getComboSiteIDs( @$this->pageInfo->filter->taxation_site_id, 'taxation_site_id', JText::_( 'SELECT_A_SITE'), 'onchange="document.adminForm.submit();"');
								if( !empty( $comboSiteIDs)){
									echo $comboSiteIDs;
								}
							}
						}
?>

			</td>
		</tr>
	</table>
	<?php $columns = 8; ?>
	<table id="hikashop_taxation_listing" class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<?php if(hikashop_isAllowed($this->config->get('acl_taxation_manage','all'))){
					$columns++; ?>
					<th class="title titlebox">
						<?php echo JText::_('HIKA_EDIT'); ?>
					</th>
				<?php } ?>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('TAXATION_CATEGORY'), 'c.category_name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('RATE'), 'b.tax_rate', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JText::_('RESTRICTIONS'); ?>
				</th>
				<?php
					$count_extrafields = 0;
					if(!empty($this->extrafields)) {
						foreach($this->extrafields as $namekey => $extrafield) {
							echo '<th class="hikashop_taxation_'.$namekey.'_title title">'.$extrafield->name.'</th>'."\r\n";
						}
						$count_extrafields = count($this->extrafields);
					}
				?>
<?php if(file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'helpers'.DS.'utils.php')){ $count_extrafields++; ?>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_( 'SITE_ID' ), 'a.taxation_site_id',$this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
<?php } ?>
				<th class="title titletoggle">
					<?php echo JText::_( 'CUMULATIVE_TAX' ); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JHTML::_('grid.sort',   JText::_('HIKA_PUBLISHED'), 'a.taxation_published', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'a.taxation_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="<?php echo ($columns+$count_extrafields);?>">
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
					$publishedid = 'taxation_published-'.$row->taxation_id;
			?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="center">
					<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td align="center">
						<?php echo JHTML::_('grid.id', $i, $row->taxation_id ); ?>
					</td>
					<?php if(hikashop_isAllowed($this->config->get('acl_taxation_manage','all'))){ ?>
						<td align="center">
							<a href="<?php echo hikashop_completeLink('taxation&task=edit&taxation_id='.$row->taxation_id); ?>">
								<img class="hikashop_go" src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="<?php echo JText::_('HIKA_EDIT'); ?>" />
							<a/>
						</td>
					<?php } ?>
					<td>
						<?php if(hikashop_isAllowed($this->config->get('acl_category_manage','all'))){ ?>
							<?php echo @$row->category_name; ?>
							<a href="<?php echo hikashop_completeLink('category&task=edit&category_id='.@$row->category_id); ?>">
						<?php } ?>
								<img class="hikashop_go" src="<?php echo HIKASHOP_IMAGES; ?>go.png" alt="go" />
						<?php if(hikashop_isAllowed($this->config->get('acl_category_manage','all'))){ ?>
							</a>
						<?php } ?>
					</td>
					<td>
						<?php if(!empty($row->tax_namekey)){?>
							<?php echo $row->tax_namekey.' ('.(@$row->tax_rate*100).'%)'; ?>
							<?php if($this->manage){ ?>
								<a href="<?php echo hikashop_completeLink('tax&task=edit&return=taxation&tax_namekey='.@$row->tax_namekey); ?>">
							<?php } ?>
									<img class="hikashop_go" src="<?php echo HIKASHOP_IMAGES; ?>go.png" alt="go" />
							<?php if($this->manage){ ?>
								</a>
							<?php } ?>
						<?php }else{
							echo '0%';
						}?>
					</td>
					<td>
						<?php
						$restrictions = array();
						foreach($row->restrictions as $name => $restriction){
							$restrictions[] = '<b>'.JText::_($name).'</b>: '.$restriction;
						}
						echo implode('<br/>',$restrictions);
						?>
					</td>
					<?php
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
							echo '<td class="hikashop_taxation_'.$namekey.'_value">'.$value.'</td>';
						}
					}

					if(file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'helpers'.DS.'utils.php')){ ?>
						<td class="title">
							<?php echo $row->taxation_site_id; ?>
						</td>
					<?php } ?>
					<td align="center">
						<?php echo $this->toggleClass->display('activate',$row->taxation_cumulative); ?>
					</td>
					<td align="center">
						<?php if($this->manage){ ?>
							<span id="<?php echo $publishedid ?>" class="spanloading"><?php echo $this->toggleClass->toggle($publishedid,(int) $row->taxation_published,'taxation') ?></span>
						<?php }else{ echo $this->toggleClass->display('activate',$row->taxation_published); } ?>
					</td>
					<td width="1%" align="center">
						<?php echo $row->taxation_id; ?>
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
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
