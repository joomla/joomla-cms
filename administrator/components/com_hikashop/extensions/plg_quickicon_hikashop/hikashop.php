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
class plgQuickiconHikaShop  extends JPlugin {
	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
		$this->loadLanguage('com_hikashop.sys');
	}

	public function onGetIcons($context) {
		$hikashopHelper = rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php';
		if($context != $this->params->get('context', 'mod_quickicon') || !file_exists($hikashopHelper) || !JFactory::getUser()->authorise('core.manage', 'com_hikashop')) {
			return;
		}

		if(version_compare(JVERSION, '3.0', '>=')) {
			$img = 'cart';
		} else {
			$img = JURI::base().'../media/com_hikashop/images/icons/icon-48-hikashop.png';
		}

		return array(
			array(
				'link' => 'index.php?option=com_hikashop',
				'image' => $img,
				'text' => $this->params->get('displayedtext', JText::_('HIKASHOP')),
				'access' => array('core.manage', 'com_hikashop'),
				'id' => 'plg_quickicon_hikashop'
			)
		);
	}
}
