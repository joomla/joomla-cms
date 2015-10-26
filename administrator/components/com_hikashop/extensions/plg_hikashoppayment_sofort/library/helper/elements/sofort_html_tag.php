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

class SofortHtmlTag extends SofortTag {

	private static $selfClosingTags = array('base', 'meta', 'link', 'hr', 'br', 'param', 'img', 'area', 'input', 'col');


	public function __construct($tagname, array $attributes = array(), $children = array()) {
		$tagname = strtolower($tagname);
		$loweredAttributes = array();

		foreach ($attributes as $key => $value) {
			$loweredAttributes[strtolower($key)] = $value;
		}

		parent::__construct($tagname, $loweredAttributes, $children);
	}


	protected function _render($output, $attributes) {
		return in_array($this->tagname, self::$selfClosingTags) ? "<{$this->tagname}{$attributes} />" : "<{$this->tagname}{$attributes}>{$output}</{$this->tagname}>";
	}
}
?>
