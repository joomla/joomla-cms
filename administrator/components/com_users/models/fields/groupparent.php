<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

/**
 * User Group Parent field..
 *
 * @since  1.6
 */
class JFormFieldGroupParent extends JFormFieldList
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
		for ($i = 0, $n = count($options); $i < $n; $i++)
		{
			// Show groups only if user is super admin or group is not super admin
			if ($isSuperAdmin || !JAccess::checkGroup($options[$i]->id, 'core.admin'))
			{
				$options[$i]->value = $options[$i]->id;
				$options[$i]->text = str_repeat('- ', $options[$i]->level) . $options[$i]->title;
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
