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

class SofortText extends SofortElement {

	public $text;

	public $escape = false;


	public function __construct($text, $escape = false, $trim = true) {
		$this->text = $trim ? trim($text) : $text;
		$this->escape = $escape;
	}


	public function render() {
		return $this->escape ? htmlspecialchars($this->text) : $this->text;
	}
}
?>
