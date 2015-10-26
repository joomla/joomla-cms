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
class VoteController extends hikashopController{
	var $type='vote';
	var $pkey = 'vote_id';
	var $table = 'vote';
	var $orderingMap ='vote_ordering';
}
