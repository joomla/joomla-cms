<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div style="border-top: 1px solid rgb(204, 204, 204); border-bottom: 1px solid rgb(204, 204, 204); background: rgb(221, 225, 230) none repeat scroll 0% 0%; -moz-background-clip: border; -moz-background-origin: padding; -moz-background-inline-policy: continuous; font-weight: bold;margin-bottom:1px"><?php echo JText::_( 'EXPLORER' ); ?></div>
<?php
	$control = JRequest::getCmd('control');
	if(!empty($control)){
		$control='&control='.$control;
	}
	$tree = hikashop_get('type.categorysub');
	$type = null;
	if($this->type == 'status')
		$type = array('status');
	echo $tree->displayTree('product_listing', 0, $type, true, true, $this->defaultId, hikashop_completeLink($this->task.'&type='.$this->type.$control,$this->popup,false,true));
?>
