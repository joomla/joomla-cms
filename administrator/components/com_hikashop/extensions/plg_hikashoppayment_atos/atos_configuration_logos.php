<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>
	<div style="float:right;margin-bottom : 10px">
		<button class="btn" id="insertButton" onclick="insertCards(); return false;">Apply</button>
	</div>

	<div style="margin:auto;">
	<table class="adminlist table table-striped" cellpadding="1"><thead><tr>
		<th class="title"></th>
		<th class="title"><?php echo JText::sprintf( 'SELECT_CARDS' ); ?></th>
		<th class="title"></th>
		</tr></thead><tr>
	<?php
	$i=0;
	foreach($this->data['cards'] as $card){
		if($i<2){
			if($this->data['finalCard'][$card]->present==1){
				if($this->data['finalCard'][$card]->check==1){
					?><td><input type="checkbox" checked="checked" name="cards[]" value="<?php echo $card; ?>"><?php echo $card; ?></td>
					<?php
					$i++;
				}
				else{
					?><td><input type="checkbox" name="cards[]" value="<?php echo $card; ?>"><?php echo $card; ?></td>
					<?php
					$i++;
				}
			}
			else{
				?><td><input type="checkbox" name="cards[]" value="<?php echo $card; ?>" disabled="disabled"><span style="color:#BDBDBD"><?php echo $card; ?></span></td>
				<?php
				$i++;
			}
		}
		else{
			if($this->data['finalCard'][$card]->present==1){
				if($this->data['finalCard'][$card]->check==1){
					?><td><input type="checkbox" checked="checked" name="cards[]" value="<?php echo $card; ?>"><?php echo $card; ?></td></tr><tr>
					<?php
					$i=0;
				}
				else{
					?><td><input type="checkbox" name="cards[]" value="<?php echo $card; ?>"><?php echo $card; ?></td></tr><tr>
					<?php
					$i=0;
				}
			}
			else{
				?><td><input type="checkbox" name="cards[]" value="<?php echo $card; ?>" disabled="disabled"><span style="color:#BDBDBD"><?php echo $card; ?></span></td></tr><tr>
				<?php
				$i=0;
			}
		}
	}
	?>

	</tr></table></div>
