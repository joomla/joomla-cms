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
class TranslationController extends hikashopController{
	var $toggle = array('published'=>'id');
	var $display = array('listing','show','');
	var $modify_views = array();
	var $add = array();
	var $modify = array('toggle');
	var $delete = array();
}
