<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class ShippingController extends hikashopController{
	var $type='shipping';
	function __construct($config = array()){
		parent::__construct($config);
		$this->modify_views[] = 'unpublish';
		$this->modify_views[] = 'publish';
	}

}
