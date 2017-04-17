<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>				<span id="result" >
					<?php echo @$this->element->zone_id.' '.@$this->element->zone_name_english;
					$type = JRequest::getCmd('type');
					$subtype = JRequest::getCmd('subtype');
					if($type=='discount'){
					?>
						<input type="hidden" name="data[discount][discount_zone_id]" value="<?php echo @$this->element->zone_id; ?>" />
					<?php
					}elseif($type=='tax'){
					?>
						<input type="hidden" name="data[taxation][zone_namekey]" value="<?php echo @$this->element->zone_namekey; ?>" />
					<?php
					}elseif($type=='config'){
					?>
						<input type="hidden" name="config[main_tax_zone]" value="<?php echo @$this->element->zone_id; ?>" />
					<?php
					}elseif(!empty($subtype)){
						$map = JRequest::getVar('map',$subtype);
						$column = JRequest::getVar('column','zone_namekey');
					?>
						<input type="hidden" name="<?php echo $map;?>" value="<?php echo @$this->element->$column; ?>" />
					<?php
					}else{
					?>
						<input type="hidden" name="data[<?php echo $type;?>][<?php echo $type;?>_zone_namekey]" value="<?php echo @$this->element->zone_namekey; ?>" />
					<?php
					}
					?>
				</span>
