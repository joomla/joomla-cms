<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><span class="hikashop_product_vote">
<?php
	$js = '';
	$this->params->set('vote_type','product');
	$this->params->set('product_id',$this->row->product_id);
	$this->params->set('main_div_name',$this->params->get('main_div_name'));
	$this->params->set('listing_product', true);
	echo hikashop_getLayout('vote', 'mini', $this->params, $js);
?>
</span>
