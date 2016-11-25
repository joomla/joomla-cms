<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Fields Contexts
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldFieldcontexts extends JFormAbstractlist
{
	public $type = 'Fieldcontexts';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getOptions()
	{
		$options        = parent::getOptions();
		$includeSection = (string) $this->element['includeSection'];
		$includeSection = ($includeSection != 'false' && $includeSection);

		// Loop through components and load contexts from helper if available.
		$components = JComponentHelper::getComponents();

		foreach ($components as $component)
		{
			if (!$component->enabled)
			{
				continue;
			}

			$eName = str_replace('com_', '', $component->option);
			$file  = JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component->option . '/helpers/' . $eName . '.php');

			if (!file_exists($file))
			{
				continue;
			}

			$prefix   = ucfirst($eName);
			$cName    = $prefix . 'Helper';
			$contexts = false;

			JLoader::register($cName, $file);

			if (class_exists($cName) && is_callable(array($cName, 'getContexts')))
			{
				$contexts = call_user_func_array(array($cName, 'getContexts'), array());
			}

			if (!$contexts || !is_array($contexts))
			{
				continue;
			}

			if ($includeSection)
			{
				$options[] = JHtml::_('select.optgroup', JText::_(strtoupper($component->option)));
				$options   = array_merge($options, $contexts);
				$options[] = JHtml::_('select.optgroup', JText::_(strtoupper($component->option)));
			}
			else
			{
				$options[$component->option] = JText::_(strtoupper($component->option));
			}
		}

		return $options;
	}
}
