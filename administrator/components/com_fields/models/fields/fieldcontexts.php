<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Fields Contexts
 *
 * @since  3.7.0
 */
class JFormFieldFieldcontexts extends JFormFieldList
{
	public $type = 'Fieldcontexts';

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.7.0
	 */
	protected function getInput()
	{
		return $this->getOptions() ? parent::getInput() : '';
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.7.0
	 */
	protected function getOptions()
	{
		$parts = explode('.', $this->value);
		$eName = str_replace('com_', '', $parts[0]);
		$file = JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $parts[0] . '/helpers/' . $eName . '.php');
		$contexts = array();

		if (!file_exists($file))
		{
			return array();
		}

		$prefix = ucfirst($eName);
		$cName = $prefix . 'Helper';

		JLoader::register($cName, $file);

		if (class_exists($cName) && is_callable(array($cName, 'getContexts')))
		{
			$contexts = $cName::getContexts();
		}

		if (!$contexts || !is_array($contexts) || count($contexts) == 1)
		{
			return array();
		}

		return $contexts;
	}
}
