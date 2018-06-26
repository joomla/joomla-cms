<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Users\Administrator\Field;

defined('JPATH_BASE') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Helper\UserGroupsHelper;
use Joomla\CMS\Form\Field\ListField;

FormHelper::loadFieldClass('list');

/**
 * User Group Parent field..
 *
 * @since  1.6
 */
class GroupparentField extends ListField
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
		$options = UserGroupsHelper::getInstance()->getAll();

		// Prevent parenting to children of this item.
		if ($id = $this->form->getValue('id'))
		{
			unset($options[$id]);
		}

		$options      = array_values($options);
		$isSuperAdmin = Factory::getUser()->authorise('core.admin');

		// Pad the option text with spaces using depth level as a multiplier.
		for ($i = 0, $n = count($options); $i < $n; $i++)
		{
			// Show groups only if user is super admin or group is not super admin
			if ($isSuperAdmin || !Access::checkGroup($options[$i]->id, 'core.admin'))
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
