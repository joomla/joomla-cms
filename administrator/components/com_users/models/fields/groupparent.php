<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Form Field class for the Joomla Framework.
 *
 * @since  1.6
 */
class JFormFieldGroupParent extends JFormAbstractlist
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since   1.6
	 */
	protected $type = 'GroupParent';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects
	 *
	 * @since   1.6
	 */
	protected function getOptions()
	{
		$options = JHelperUsergroups::getInstance()->getAll();

		// Prevent parenting to children of this item.
		if ($id = $this->form->getValue('id'))
		{
			unset($options[$id]);
		}

		$options      = array_values($options);
		$isSuperAdmin = JFactory::getUser()->authorise('core.admin');

		// Pad the option text with spaces using depth level as a multiplier.
		foreach ($options as $i => &$option)
		{
			// Show groups only if user is super admin or group is not super admin
			if ($isSuperAdmin || !JAccess::checkGroup($options[$i]->id, 'core.admin'))
			{
				$option->value = $option->id;
				$option->text = str_repeat('- ', $option->level) . $option->title;
			}
			else
			{
				unset($options[$i]);
			}
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), $options);
	}
}
