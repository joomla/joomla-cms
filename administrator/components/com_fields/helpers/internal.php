<?php
/**
 * @package    Fields
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2015 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

class FieldsHelperInternal
{

	public static function addSubmenu ($context)
	{
		// Avoid nonsense situation.
		if ($context == 'com_fields')
		{
			return;
		}

		$parts = FieldsHelper::extract($context);
		if (! $parts)
		{
			return;
		}
		$component = $parts[0];
		$section = $parts[1];

		// Try to find the component helper.
		$eName = str_replace('com_', '', $component);
		$file = JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component . '/helpers/' . $eName . '.php');

		if (file_exists($file))
		{
			require_once $file;

			$prefix = ucfirst(str_replace('com_', '', $component));
			$cName = $prefix . 'Helper';

			if (class_exists($cName))
			{
				if (is_callable(array(
						$cName,
						'addSubmenu'
				)))
				{
					$lang = JFactory::getLanguage();

					// Loading language file from the administrator/language
					// directory then
					// loading language file from the
					// administrator/components/*context*/language directory
					$lang->load($component, JPATH_BASE, null, false, true) ||
							 $lang->load($component, JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component), null, false, true);

					call_user_func(array(
							$cName,
							'addSubmenu'
					), 'fields' . (isset($section) ? '.' . $section : ''));
				}
			}
		}
	}

	public static function where ()
	{
		$e = new Exception();
		$trace = '<pre>' . $e->getTraceAsString() . '</pre>';

		echo $trace;
	}
}
