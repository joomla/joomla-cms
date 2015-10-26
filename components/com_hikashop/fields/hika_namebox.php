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
class JFormFieldHika_namebox extends JFormField {
	protected $type = 'hika_namebox';

	protected function getInput() {
		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(!defined('HIKASHOP_COMPONENT') && !include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php'))
			return 'This module can not work without the Hikashop Component';

		$nameboxType = hikashop_get('type.namebox');

		$namebox_type = 'product';
		if(isset($this->element['namebox_type']))
			$namebox_type = (string)$this->element['namebox_type'];

		$namebox_mode = hikashopNameboxType::NAMEBOX_SINGLE;
		if($this->multiple)
			$namebox_mode = hikashopNameboxType::NAMEBOX_MULTIPLE;

		$text = $nameboxType->display(
			$this->name,
			$this->value,
			$namebox_mode,
			$namebox_type,
			array(
				'delete' => true,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>'
			)
		);
		return $text;
	}
}
