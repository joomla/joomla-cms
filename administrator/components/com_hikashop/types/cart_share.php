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
class hikashopCart_shareType {
	protected $values = null;

	public function load() {
		$values = array(
			'nobody' => 'HIKASHOP_NOBODY',
			'public' => 'HIKASHOP_EVERYBODY',
			'registered' => 'HIKASHOP_REGISTERED_USERS',
			'email' => 'HIKA_EMAIL',
		);
		if($this->values === null) {
			$this->values = array();
			foreach($values as $k => $v) {
				$this->values[] = JHTML::_('select.option', $k, JText::_($v));
			}
		}
		return $values;
	}

	public function display($map, $value) {
		if(empty($this->values))
			$this->load();
		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', $value);
	}
}
