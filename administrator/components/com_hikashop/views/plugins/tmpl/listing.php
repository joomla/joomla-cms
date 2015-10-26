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
<?php if(!empty($this->filters)) { ?>
	<div style="float:right"><?php echo implode(' ', $this->filters); ?></div>
	<div style="clear:right"></div>
<?php } ?>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT; ?>&amp;ctrl=plugins&plugin_type=<?php echo $this->plugin_type;?>" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">
	<table>
		<tr>
			<td width="100%">
				<?php echo JText::_( 'FILTER' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" />
				<button class="btn" onclick="document.adminForm.limitstart.value=0;this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
				<button class="btn" onclick="document.adminForm.limitstart.value=0;document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
			</td>
			<td nowrap="nowrap">
<?php
				if(!empty($this->extrafilters)) {
					foreach($this->extrafilters as $name => $filterObj) {
						echo $filterObj->displayFilter($name, $this->pageInfo->filter);
					}
				}
?>
			</td>
		</tr>
	</table>
<?php $cols = 7; ?>
<table class="adminlist table table-striped" cellpadding="1">
	<thead>
		<tr>
			<th class="title titlenum"><?php echo JText::_('HIKA_NUM');?></th>
			<th class="title titlebox">
				<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
			</th>
			<th class="title"><?php echo JText::_('HIKA_NAME');?></th>
<?php
	if(!empty($this->listing_columns)) {
		foreach($this->listing_columns as $key => $column) {
			$cols++;
?>			<th class="title"><?php echo JText::_($column['name']);?></th>
<?php
		}
	}
?>
			<th class="title"><?php echo JText::_('HIKA_TYPE');?></th>
			<th class="title titleorder" style="width:10%;"><?php
				if($this->ordering->ordering)
					echo JHTML::_('grid.order',  $this->rows);
				echo JText::_( 'HIKA_ORDER' );
			?></th>
			<th class="title" style="width:2%;"><?php echo JText::_('HIKA_DELETE');?></th>
			<th class="title" style="width:2%;"><?php echo JText::_('HIKA_PUBLISHED');?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="<?php echo $cols; ?>">
				<?php echo $this->pagination->getListFooter(); ?>
				<?php echo $this->pagination->getResultsCounter(); ?>
			</td>
		</tr>
	</tfoot>
	<tbody>
<?php
$p_id = $this->plugin_type.'_id';
$p_name = $this->plugin_type.'_name';
$p_order = $this->plugin_type.'_ordering';
$p_published = $this->plugin_type.'_published';
$p_type = $this->plugin_type.'_type';

if(empty($this->rows))
	$this->rows = array();

$k = 0;
$i = 0;
$count = count($this->rows);
if(!empty($this->rows)) {
	foreach($this->rows as $plugin_id => $plugin){
		$published_id = $this->plugin_type.'_published-' . $plugin->$p_id;
		$id = $this->plugin_type.'_' . $plugin->$p_id;

		$currentPlugin = null;
		if(isset($this->plugins[ $plugin->$p_type ])) {
			$currentPlugin = $this->plugins[ $plugin->$p_type ];
			$plugin->$p_published = $plugin->$p_published && $currentPlugin->published;
		}
?>
		<tr class="row<?php echo $k;?>" id="<?php echo $id;?>">
			<td align="center"><?php
				echo $i+1;
			?></td>
			<td align="center"><?php
				echo JHTML::_('grid.id', $i, $plugin->$p_id );
			?></td>
			<td>
				<a href="<?php echo hikashop_completeLink('plugins&plugin_type='.$this->plugin_type.'&task=edit&name='. $plugin->$p_type .'&subtask='.$this->plugin_type.'_edit&'.$p_id.'='.$plugin->$p_id);?>"><?php
					echo $plugin->$p_name;
					if(empty($plugin->$p_name))
						echo '<em>' . JText::_('NO_NAME') . '</em>';
				?></a>
			</td>
<?php
		if(!empty($this->listing_columns)) {
			foreach($this->listing_columns as $key => $column) {
				$cols++;
?>			<td><?php
				if(isset($column['col'])) {
					$col = $column['col'];
					echo @$plugin->$col;
				}
			?></td>
<?php
		}
	}
?>
			<td><?php
				if(!empty($currentPlugin))
					echo $currentPlugin->name;
				else
					echo $plugin->$p_type;
			?></td>
			<td class="order">
<?php if($this->ordering->ordering) { ?>
				<span><?php
					echo $this->pagination->orderUpIcon(
							$i,
							$this->ordering->reverse XOR ($plugin->$p_order >= @$this->rows[$i-1]->$p_order),
							$this->ordering->orderUp,
							'Move Up',
							$this->ordering->ordering
						);
				?></span>
				<span><?php
					echo $this->pagination->orderDownIcon(
							$i,
							$count,
							$this->ordering->reverse XOR ($plugin->$p_order <= @$this->rows[$i+1]->$p_order),
							$this->ordering->orderDown,
							'Move Down',
							$this->ordering->ordering
						);
					?></span>
<?php } ?>
				<input type="text" name="order[]" size="5" <?php if(!$this->ordering->ordering) echo 'disabled="disabled"'; ?> value="<?php echo $plugin->$p_order; ?>" class="text_area" style="text-align: center" />
			</td>
			<td align="center">
				<span class=""><?php
					echo $this->toggleClass->delete($id, $plugin->$p_type.'-'.$plugin->$p_id, $this->plugin_type, true);
				?></span>
			</td>
			<td align="center">
				<span id="<?php echo $published_id;?>" class="spanloading"><?php echo $this->toggleClass->toggle($published_id, (int)$plugin->$p_published, $this->plugin_type);?></span>
			</td>
		</tr>
<?php
		$k = 1-$k;
		$i++;
	}
} else if(in_array($this->plugin_type, array('payment','shipping'))) {
?>
		<tr>
			<td class="empty_list" colspan="<?php echo $cols; ?>"><?php
				echo JText::_('NO_METHOD_CLICK_NEW');
			?></td>
		</tr>
<?php
}
?>
	</tbody>
</table>
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT;?>" />
<input type="hidden" name="task" value="listing"/>
<?php echo JHTML::_( 'form.token' ); ?>
</form>
