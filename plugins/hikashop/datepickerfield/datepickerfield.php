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
class plgHikashopDatepickerfield extends JPlugin
{
	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);

		$this->loadLanguage('plg_hikashop_datepickerfield', JPATH_ADMINISTRATOR );
    }

	public function onFieldsLoad(&$fields, &$options) {
		$me = new stdClass();
		$me->name = 'datepickerfield';
		$me->text = JText::_('DATE_PICKER');
		$me->options = array('required', 'default', 'columnname', 'format', 'allow', 'datepicker_options');

		$fields[] = $me;

		$opt = new stdClass();
		$opt->name = 'datepicker_options';
		$opt->text = JText::_('DATE_PICKER_OPTIONS');
		$opt->obj = 'fieldOpt_datepicker_options';

		$options[$opt->name] = $opt;
	}
}

if(defined('HIKASHOP_COMPONENT')) {
	require_once( dirname(__FILE__).DS.'datepickerfield_class.php' );
}
