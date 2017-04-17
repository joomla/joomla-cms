<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div style="float:right">
	<?php
		echo $this->popup->display(
			'<img src="'.HIKASHOP_IMAGES.'add.png"/>'.JText::_('ADD'),
			'ADD',
			hikashop_completeLink("product&task=selectcategory", true),
			'category_add_button',
			860, 480, '', '', 'button'
		);
	?>
</div>
<br/>
<table class="adminlist table table-striped table-hover" cellpadding="1" width="100%">
	<thead>
		<tr>
			<th class="title">
				<?php echo JText::_('HIKA_NAME'); ?>
			</th>
			<th class="title titletoggle">
				<?php echo JText::_('HIKA_DELETE'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('ID'); ?>
			</th>
		</tr>
	</thead>
	<tbody id="category_listing">
		<?php
			if(!empty($this->element->categories)){
				$k = 0;
				$class = hikashop_get('class.category');
				for($i = 0,$a = count($this->element->categories);$i<$a;$i++){
					$row =& $this->element->categories[$i];
					if(!empty($row->category_id)){
						$parents = $class->getParents($row->category_id);
						$html = array();
					?>
						<tr id="category_<?php echo $row->category_id;?>">
							<td>
								<?php
								foreach($parents as $parent) {
									if($parent->category_type != 'product' && $parent->category_type != 'vendor')
										continue;
									$html[] = '<a href="'. hikashop_completeLink('category&task=edit&cid='.$parent->category_id).'">'.$parent->category_name.'</a>';
								}
								if(empty($html)) {
									$parent = end($parents);
									$html[] = '<a href="'. hikashop_completeLink('category&task=edit&cid='.$parent->category_id).'">'.$parent->category_name.'</a>';
								}
								echo implode(' / ',$html); ?>
							</td>
							<td align="center">
								<a href="#" onclick="return deleteRow('category_div_<?php echo $row->category_id;?>','category[<?php echo $row->category_id;?>]','category_<?php echo $row->category_id;?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/></a>
							</td>
							<td width="1%" align="center">
								<?php echo $row->category_id; ?>
								<div id="category_div_<?php echo $row->category_id;?>">
									<input type="hidden" name="category[<?php echo $row->category_id;?>]" id="category[<?php echo $row->category_id;?>]" value="<?php echo $row->category_id;?>"/>
								</div>
							</td>
						</tr>
					<?php
					}
					$k = 1-$k;
				}
			}
		?>
	</tbody>
</table>

