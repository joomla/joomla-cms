<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die ();

class HelixUltimateFieldsHelper
{
	protected function __construct()
	{
		$fields = JFolder::files( dirname( __FILE__ ) . '/fields', '\.php$', false, true);
		foreach ($fields as $field)
		{
			require_once $field;
		}
	}

	protected static function getInputElements($key, $attr)
	{
		return call_user_func(array('HelixUltimateField' . ucfirst($attr['field']), 'getInput'), $key, $attr);
	}
}
