<?php
/**
* @package Helix3 Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2017 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

//no direct accees
defined ('_JEXEC') or die ('resticted aceess');

class FieldsHelper{

	protected function __construct()
	{

		$types = JFolder::files( dirname( __FILE__ ) . '/types', '\.php$', false, true);

		foreach ($types as $type) {
			require_once $type;
		}
	}

	protected static function getInputElements( $key, $attr )
	{
		return call_user_func(array( 'SpType' . ucfirst( $attr['type'] ), 'getInput'), $key, $attr );
	}

}
